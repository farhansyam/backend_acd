<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\BpService;
use App\Models\BusinessPartner;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const APARTMENT_SURCHARGE = 20000;
    const TIME_SLOTS = [
        '09:00',
        '10:00',
        '11:00',
        '12:00',
        '13:00',
        '14:00',
        '15:00',
        '16:00',
        '17:00',
    ];

    // ─── GET layanan tersedia (bp_services aktif) ─────────────
    public function getServices(Request $request)
    {
        // Ambil city dari alamat primary user, atau query param
        $cityName = $request->query('city');

        $query = BpService::with(['serviceType', 'businessPartner'])
            ->where('is_active', 1);

        if ($cityName) {
            $query->whereHas('businessPartner', function ($q) use ($cityName) {
                $q->where('city', 'like', "%{$cityName}%");
            });
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id'           => $service->id,
                'name'         => $service->serviceType->name ?? '-',
                'description'  => $service->serviceType->description ?? '',
                'base_price'   => (float) $service->base_service,
                'discount'     => (float) $service->discount,
                'final_price'  => (float) $service->base_service - (float) $service->discount,
                'bp_id'        => $service->bp_id,
                'bp_name'      => $service->businessPartner->name ?? 'Dikari',
                'banner'       => $service->banner,
            ];
        });

        return response()->json([
            'services'   => $services,
            'time_slots' => self::TIME_SLOTS,
        ]);
    }

    // ─── POST buat order baru ─────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_phone_id'  => 'required|exists:user_phones,id',
            'address_id'     => 'required|exists:addresses,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|in:' . implode(',', self::TIME_SLOTS),
            'notes'          => 'nullable|string|max:500',
            'items'          => 'required|array|min:1',
            'items.*.bp_service_id' => 'required|exists:bp_services,id',
            'items.*.quantity'      => 'required|integer|min:1|max:20',
        ]);

        // Pastikan alamat & phone milik user ini
        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if(
            !\App\Models\UserPhone::where('id', $validated['user_phone_id'])
                ->where('user_id', $request->user()->id)->exists(),
            403,
            'Nomor kontak tidak valid.'
        );

        // Cari BP berdasarkan kota alamat
        $bp = BusinessPartner::where('city', 'like', "%{$address->city_name}%")->first();

        if (!$bp) {
            return response()->json([
                'message' => 'Maaf, layanan belum tersedia di kota ' . $address->city_name . '. Kami akan segera hadir di kota Anda!',
                'available' => false,
            ], 422);
        }

        return DB::transaction(function () use ($request, $validated, $address, $bp) {
            // Hitung biaya apartemen
            $apartmentSurcharge = $address->property_type === 'apartemen'
                ? self::APARTMENT_SURCHARGE
                : 0;

            // Hitung subtotal dari items
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $bpService = BpService::with('serviceType')->findOrFail($item['bp_service_id']);
                $unitPrice = (float) $bpService->base_service;
                $discount  = (float) $bpService->discount;
                $qty       = $item['quantity'];
                $itemSubtotal = ($unitPrice - $discount) * $qty;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'bp_service_id' => $bpService->id,
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'discount'      => $discount,
                    'subtotal'      => $itemSubtotal,
                ];
            }

            $totalAmount = $subtotal + $apartmentSurcharge;

            // Buat order
            $order = Order::create([
                'user_id'             => $request->user()->id,
                'user_phone_id'       => $validated['user_phone_id'],
                'address_id'          => $validated['address_id'],
                'bp_id'               => $bp->id,
                'scheduled_date'      => $validated['scheduled_date'],
                'scheduled_time'      => $validated['scheduled_time'],
                'apartment_surcharge' => $apartmentSurcharge,
                'subtotal'            => $subtotal,
                'total_amount'        => $totalAmount,
                'status'              => 'pending',
                'notes'               => $validated['notes'] ?? null,
            ]);

            // Buat order items
            $order->items()->createMany($itemsData);

            // Load relasi untuk response
            $order->load(['items.bpService.serviceType', 'address', 'phone', 'businessPartner']);

            return response()->json([
                'message' => 'Order berhasil dibuat!',
                'order'   => $this->formatOrder($order),
            ], 201);
        });
    }

    // ─── GET list order user ──────────────────────────────────
    public function index(Request $request)
    {
        $orders = Order::with(['items.bpService.serviceType', 'address', 'phone', 'businessPartner'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        return response()->json(['orders' => $orders]);
    }

    // ─── GET detail order ─────────────────────────────────────
    public function show(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        $order->load([
            'items.bpService.serviceType',
            'address',
            'phone',
            'businessPartner',
            'report',
            'rating',
            'technician.user',
            'complaint',
        ]);

        return response()->json(['order' => $this->formatOrder($order)]);
    }

    // ─── DELETE / cancel order ────────────────────────────────
    public function cancel(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        abort_if(
            !($order->status === 'pending' && $order->payment_status === 'unpaid'),
            422,
            'Order tidak dapat dibatalkan. Hubungi CS untuk bantuan.'
        );

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Order berhasil dibatalkan.']);
    }

    // ─── Helper format order ──────────────────────────────────
    private function formatOrder(Order $order): array
    {
        return [
            'id'                  => $order->id,
            'status'              => $order->status,
            'scheduled_date'      => $order->scheduled_date?->format('Y-m-d'),
            'scheduled_time'      => $order->scheduled_time,
            'apartment_surcharge' => (float) $order->apartment_surcharge,
            'subtotal'            => (float) $order->subtotal,
            'total_amount'        => (float) $order->total_amount,
            'notes'               => $order->notes,
            'bp_name'             => $order->businessPartner?->name ?? '-',
            'phone'               => [
                'label'        => $order->phone?->label,
                'phone_number' => $order->phone?->phone_number,
            ],
            'address'             => [
                'label'         => $order->address?->label,
                'full_address'  => $order->address?->formatted_address,
                'property_type' => $order->address?->property_type,
            ],
            'items'               => $order->items->map(fn($item) => [
                'id'         => $item->id,
                'name'       => $item->bpService?->serviceType?->name ?? '-',
                'quantity'   => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount'   => (float) $item->discount,
                'subtotal'   => (float) $item->subtotal,
            ]),
            'created_at'          => $order->created_at?->format('Y-m-d H:i'),
            'payment_status'     => $order->payment_status,
            'tripay_payment_url' => $order->tripay_payment_url,
            'tripay_reference' => $order->tripay_reference,

            'report' => $order->report ? [
                'photo_before'       => url('storage/' . $order->report->photo_before),
                'photo_after'        => url('storage/' . $order->report->photo_after),
                'notes'              => $order->report->notes,
                'filter_cleaned'     => $order->report->filter_cleaned,
                'freon_checked'      => $order->report->freon_checked,
                'drain_cleaned'      => $order->report->drain_cleaned,
                'electrical_checked' => $order->report->electrical_checked,
            ] : null,
            'discount_amount'     => (float) $order->discount_amount,
            'rating' => $order->rating ? [
                'rating' => $order->rating->rating,
                'review' => $order->rating->review,
            ] : null,
            'technician_name' => $order->technician?->user?->name ?? null,
            'rating'          => $order->rating ? [
                'rating' => $order->rating->rating,
                'review' => $order->rating->review,
            ] : null,
            'complaint' => $order->complaint ? [
                'id'           => $order->complaint->id,
                'title'        => $order->complaint->title,
                'status'       => $order->complaint->status,
                'status_label' => $order->complaint->status_label,
                'bp_comment'   => $order->complaint->bp_comment,
                'created_at'   => $order->complaint->created_at->format('Y-m-d H:i'),
            ] : null,
        ];
    }
}
