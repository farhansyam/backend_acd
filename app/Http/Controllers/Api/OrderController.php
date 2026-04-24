<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\BpService;
use App\Models\BusinessPartner;
use App\Models\Order;
use App\Models\SurveyReport;
use App\Models\OrderItem;
use App\Services\NotificationService;
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

    public function __construct(private NotificationService $notif) {}

    // ─── GET layanan tersedia ─────────────────────────────────
    public function getServices(Request $request)
    {
        $cityName = $request->query('city');
        $category = $request->query('category');

        $query = BpService::with(['serviceType', 'businessPartner'])
            ->where('is_active', 1);

        if ($cityName) {
            $query->whereHas('businessPartner', function ($q) use ($cityName) {
                $q->where('city', 'like', "%{$cityName}%");
            });
        }

        if ($category) {
            $query->whereHas('serviceType', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id'          => $service->id,
                'name'        => $service->serviceType->name ?? '-',
                'description' => $service->serviceType->description ?? '',
                'category'    => $service->serviceType->category ?? 'cuci_reguler',
                'base_price'  => (float) $service->base_service,
                'discount'    => (float) $service->discount,
                'final_price' => (float) $service->base_service - (float) $service->discount,
                'bp_id'       => $service->bp_id,
                'bp_name'     => $service->businessPartner->name ?? 'Dikari',
                'banner'      => $service->banner,
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
        $isRelocation   = $request->input('order_type') === 'relokasi';
        $isDiffLocation = $request->input('relocation_type') === 'different_location';

        $rules = [
            'user_phone_id'         => 'required|exists:user_phones,id',
            'address_id'            => 'required|exists:addresses,id',
            'scheduled_date'        => 'required|date|after_or_equal:today',
            'scheduled_time'        => 'required|in:' . implode(',', self::TIME_SLOTS),
            'notes'                 => 'nullable|string|max:500',
            'items'                 => 'required|array|min:1',
            'items.*.bp_service_id' => 'required|exists:bp_services,id',
            'items.*.quantity'      => 'required|integer|min:1|max:20',
            'order_type'            => 'nullable|string',
            'relocation_type'       => 'nullable|in:same_location,different_location',
            'origin_address_id'     => $isRelocation && $isDiffLocation
                ? 'required|exists:addresses,id'
                : 'nullable|exists:addresses,id',
        ];

        $validated = $request->validate($rules);

        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if(
            !\App\Models\UserPhone::where('id', $validated['user_phone_id'])
                ->where('user_id', $request->user()->id)->exists(),
            403,
            'Nomor kontak tidak valid.'
        );

        if ($isRelocation && $isDiffLocation) {
            Address::where('id', $validated['origin_address_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        $bp = BusinessPartner::where('city', 'like', "%{$address->city_name}%")->first();
        if (!$bp) {
            return response()->json([
                'message'   => 'Maaf, layanan belum tersedia di kota ' . $address->city_name . '.',
                'available' => false,
            ], 422);
        }

        return DB::transaction(function () use ($request, $validated, $address, $bp, $isRelocation, $isDiffLocation) {
            $apartmentSurcharge = $address->property_type === 'apartemen'
                ? self::APARTMENT_SURCHARGE : 0;

            $subtotal  = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $bpService    = BpService::with('serviceType')->findOrFail($item['bp_service_id']);
                $unitPrice    = (float) $bpService->base_service;
                $discount     = (float) $bpService->discount;
                $qty          = $item['quantity'];
                $itemSubtotal = ($unitPrice - $discount) * $qty;
                $subtotal    += $itemSubtotal;

                $itemsData[] = [
                    'bp_service_id' => $bpService->id,
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'discount'      => $discount,
                    'subtotal'      => $itemSubtotal,
                ];
            }

            $totalAmount = $subtotal + $apartmentSurcharge;

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
                'status'              => $isRelocation && $isDiffLocation
                    ? 'pending_transport_fee'
                    : 'pending',
                'notes'               => $validated['notes'] ?? null,
                'order_type'          => $validated['order_type'] ?? null,
                'relocation_type'     => $validated['relocation_type'] ?? null,
                'origin_address_id'   => ($isRelocation && $isDiffLocation)
                    ? $validated['origin_address_id'] : null,
                'transport_fee'       => 0,
            ]);

            $order->items()->createMany($itemsData);
            $order->load(['items.bpService.serviceType', 'address', 'phone', 'businessPartner', 'originAddress']);

            return response()->json([
                'message' => $isRelocation && $isDiffLocation
                    ? 'Order relokasi berhasil dibuat! Menunggu konfirmasi biaya transportasi dari mitra.'
                    : 'Order berhasil dibuat!',
                'order'   => $this->formatOrder($order),
            ], 201);
        });
    }

    // ─── PATCH konfirmasi biaya transport (customer) ──────────
    public function confirmTransportFee(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        abort_if(
            $order->status !== 'pending_transport_fee_set',
            422,
            'Belum ada biaya transportasi yang perlu dikonfirmasi.'
        );

        $request->validate(['confirm' => 'required|boolean']);

        if ($request->confirm) {
            $order->update(['status' => 'pending']);
            return response()->json(['message' => 'Biaya transportasi dikonfirmasi. Lanjutkan ke pembayaran.']);
        }

        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Order dibatalkan.']);
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
            'originAddress',
            'report',
            'rating',
            'technician.user',
            'complaint',
            'secondTechnician.user',
        ]);

        return response()->json(['order' => $this->formatOrder($order)]);
    }

    // ─── PATCH cancel order ───────────────────────────────────
    public function cancel(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        abort_if(
            !in_array($order->status, ['pending', 'pending_transport_fee', 'pending_transport_fee_set'])
                || $order->payment_status !== 'unpaid',
            422,
            'Order tidak dapat dibatalkan. Hubungi CS untuk bantuan.'
        );

        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Order berhasil dibatalkan.']);
    }

    // ─── POST buat order perbaikan ────────────────────────────
    public function createPerbaikanOrder(Request $request)
    {
        $request->validate([
            'bp_service_id'    => 'required|exists:bp_services,id',
            'address_id'       => 'required|exists:addresses,id',
            'user_phone_id'    => 'required|exists:user_phones,id',
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'scheduled_time'   => 'required|in:' . implode(',', self::TIME_SLOTS),
            'notes'            => 'nullable|string|max:500',
            'keluhan'          => 'nullable|array',
            'keluhan.*'        => 'string',
            'keluhan_lainnya'  => 'nullable|string|max:500',
        ]);

        /** @var \App\Models\BpService $bpService */
        $bpService = BpService::with('serviceType')->findOrFail($request->bp_service_id);

        if ($bpService->serviceType->category !== 'service_perbaikan_survey') {
            return response()->json(['message' => 'Service tidak valid untuk order perbaikan.'], 422);
        }

        $address = Address::where('id', $request->address_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if(
            !\App\Models\UserPhone::where('id', $request->user_phone_id)
                ->where('user_id', $request->user()->id)->exists(),
            403,
            'Nomor kontak tidak valid.'
        );

        /** @var \App\Models\Order $order */
        $order = Order::create([
            'user_id'          => $request->user()->id,
            'bp_id'            => $bpService->bp_id,
            'address_id'       => $request->address_id,
            'user_phone_id'    => $request->user_phone_id,
            'scheduled_date'   => $request->scheduled_date,
            'scheduled_time'   => $request->scheduled_time,
            'notes'            => $request->notes,
            'keluhan'          => $request->keluhan ?? [],
            'keluhan_lainnya'  => $request->keluhan_lainnya,
            'subtotal'         => $bpService->base_service,
            'total_amount'     => $bpService->base_service,
            'order_type'       => 'perbaikan',
            'is_perbaikan'     => true,
            'perbaikan_phase'  => 'survey',
            'status'           => 'pending',
            'payment_status'   => 'unpaid',
            'transport_fee'    => 0,
            'apartment_surcharge' => $address->property_type === 'apartemen'
                ? self::APARTMENT_SURCHARGE : 0,
        ]);

        OrderItem::create([
            'order_id'      => $order->id,
            'bp_service_id' => $bpService->id,
            'quantity'      => 1,
            'unit_price'    => (float) $bpService->base_service,
            'discount'      => (float) $bpService->discount,
            'subtotal'      => (float) $bpService->base_service - (float) $bpService->discount,
        ]);

        $order->load(['items.bpService.serviceType', 'address', 'phone', 'businessPartner']);

        return response()->json([
            'message' => 'Order perbaikan berhasil dibuat.',
            'order'   => $this->formatOrder($order),
        ], 201);
    }

    // ─── GET detail survey report (customer) ─────────────────
    public function surveyReport(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        /** @var \App\Models\SurveyReport $report */
        $report = SurveyReport::where('order_id', $order->id)->firstOrFail();

        return response()->json([
            'order'  => $this->formatOrder($order->load([
                'items.bpService.serviceType',
                'address',
                'phone',
                'businessPartner',
                'technician.user',
            ])),
            'report' => [
                'id'                => $report->id,           // ← tambah
                'order_id'          => $report->order_id,     // ← tambah
                'kondisi_unit'      => $report->kondisi_unit,
                'bagian_bermasalah' => $report->bagian_bermasalah,
                'catatan'           => $report->catatan,
                'rekomendasi'       => $report->rekomendasi,
                'photo_before'      => $report->photo_before
                    ? url('storage/' . $report->photo_before) : null,
                'photo_after'       => $report->photo_after
                    ? url('storage/' . $report->photo_after) : null,
                'customer_response' => $report->customer_response,
                'responded_at'      => $report->responded_at?->format('Y-m-d H:i'),
            ],
        ]);
    }

    // ─── POST customer respond survey (lanjut/tidak) ──────────
    public function respondSurvey(Request $request, Order $order)
    {
        $request->validate([
            'response'      => 'required|in:lanjut,tidak',
            'bp_service_id' => 'required_if:response,lanjut|exists:bp_services,id',
        ]);

        abort_if($order->user_id !== $request->user()->id, 403);
        abort_if(
            $order->status !== 'waiting_customer_response',
            422,
            'Order tidak dalam status menunggu konfirmasi.'
        );

        /** @var \App\Models\SurveyReport $report */
        $report = SurveyReport::where('order_id', $order->id)->firstOrFail();

        $report->update([
            'customer_response' => $request->response,
            'responded_at'      => now(),
        ]);

        // ─── Tidak lanjut ─────────────────────────────────────
        if ($request->response === 'tidak') {
            $order->update(['status' => 'completed']);
            $this->releaseSurveyBalance($order);
            return response()->json(['message' => 'Order survei selesai. Terima kasih.']);
        }

        // ─── Lanjut — buat order fase 2 ───────────────────────
        /** @var \App\Models\BpService $bpService */
        $bpService = BpService::with('serviceType')->findOrFail($request->bp_service_id);

        $validCategory = $report->rekomendasi === 'cuci_unit'
            ? 'cuci_reguler'
            : 'service_perbaikan_service';

        if ($bpService->serviceType->category !== $validCategory) {
            return response()->json([
                'message' => 'Service yang dipilih tidak sesuai rekomendasi teknisi.',
            ], 422);
        }

        /** @var \App\Models\Order $phase2Order */
        $phase2Order = Order::create([
            'user_id'             => $order->user_id,
            'bp_id'               => $order->bp_id,
            'technician_id'       => $order->technician_id,
            'address_id'          => $order->address_id,
            'user_phone_id'       => $order->user_phone_id,
            'scheduled_date'      => $order->scheduled_date,
            'scheduled_time'      => $order->scheduled_time,
            'notes'               => $order->notes,
            'subtotal'            => (float) $bpService->base_service,
            'total_amount'        => (float) $bpService->base_service,
            'apartment_surcharge' => 0,
            'transport_fee'       => 0,
            'order_type'          => 'perbaikan',
            'is_perbaikan'        => true,
            'perbaikan_phase'     => 'phase2',
            'survey_order_id'     => $order->id,
            'status'              => 'confirmed',
            'payment_status'      => 'unpaid',
        ]);

        OrderItem::create([
            'order_id'      => $phase2Order->id,
            'bp_service_id' => $bpService->id,
            'quantity'      => 1,
            'unit_price'    => (float) $bpService->base_service,
            'discount'      => (float) $bpService->discount,
            'subtotal'      => (float) $bpService->base_service - (float) $bpService->discount,
        ]);

        $order->update([
            'status'          => 'completed',
            'phase2_order_id' => $phase2Order->id,
        ]);

        // Notif teknisi
        $technicianUser = $order->technician?->user;
        if ($technicianUser?->fcm_token) {
            $this->notif->notifyPhase2Confirmed(
                $technicianUser->fcm_token,
                (int) $phase2Order->id
            );
        }

        $phase2Order->load(['items.bpService.serviceType', 'address', 'phone', 'businessPartner']);

        return response()->json([
            'message'      => 'Berhasil. Order fase 2 telah dibuat.',
            'phase2_order' => $this->formatOrder($phase2Order),
        ]);
    }

    // ─── Helper: release balance survey ──────────────────────
    private function releaseSurveyBalance(Order $order): void
    {
        // Delegasi ke SurveyBalanceService
        app(\App\Services\SurveyBalanceService::class)->release($order);
    }

    // ─── Helper format order ──────────────────────────────────
    private function formatOrder(Order $order): array
    {
        return [
            'id'                     => $order->id,
            'status'                 => $order->status,
            'order_type'             => $order->order_type,
            'is_perbaikan'           => (bool) $order->is_perbaikan,
            'perbaikan_phase'        => $order->perbaikan_phase,
            'phase2_order_id'        => $order->phase2_order_id,
            'survey_order_id'        => $order->survey_order_id,
            'relocation_type'        => $order->relocation_type,
            'transport_fee'          => (float) $order->transport_fee,
            'split_technician'       => (bool) $order->split_technician,
            'scheduled_date'         => $order->scheduled_date?->format('Y-m-d'),
            'scheduled_time'         => $order->scheduled_time,
            'apartment_surcharge'    => (float) $order->apartment_surcharge,
            'subtotal'               => (float) $order->subtotal,
            'total_amount'           => (float) $order->total_amount,
            'notes'                  => $order->notes,
            'keluhan'         => $order->keluhan ?? [],
            'keluhan_lainnya' => $order->keluhan_lainnya,
            'bp_name'                => $order->businessPartner?->name ?? '-',
            'phone'                  => [
                'label'        => $order->phone?->label,
                'phone_number' => $order->phone?->phone_number,
            ],
            'address'                => [
                'label'         => $order->address?->label,
                'full_address'  => $order->address?->formatted_address,
                'property_type' => $order->address?->property_type,
                'latitude'      => $order->address?->latitude,
                'longitude'     => $order->address?->longitude,
            ],
            'origin_address'         => $order->originAddress ? [
                'label'        => $order->originAddress->label,
                'full_address' => $order->originAddress->formatted_address,
            ] : null,
            'items'                  => $order->items->map(fn($item) => [
                'id'         => $item->id,
                'name'       => $item->bpService?->serviceType?->name ?? '-',
                'category'   => $item->bpService?->serviceType?->category ?? 'cuci_reguler',
                'quantity'   => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount'   => (float) $item->discount,
                'subtotal'   => (float) $item->subtotal,
            ]),
            'created_at'             => $order->created_at?->format('Y-m-d H:i'),
            'payment_status'         => $order->payment_status,
            'tripay_payment_url'     => $order->tripay_payment_url,
            'tripay_reference'       => $order->tripay_reference,
            'discount_amount'        => (float) $order->discount_amount,
            'technician_name'        => $order->technician?->user?->name ?? null,
            'second_technician_name' => $order->secondTechnician?->user?->name ?? null,
            'report'                 => $order->report ? [
                'photo_before'        => url('storage/' . $order->report->photo_before),
                'photo_after'         => url('storage/' . $order->report->photo_after),
                'notes'               => $order->report->notes,
                'filter_cleaned'      => $order->report->filter_cleaned,
                'freon_checked'       => $order->report->freon_checked,
                'drain_cleaned'       => $order->report->drain_cleaned,
                'electrical_checked'  => $order->report->electrical_checked,
                'unit_installed'      => $order->report->unit_installed,
                'piping_neat'         => $order->report->piping_neat,
                'cooling_test'        => $order->report->cooling_test,
                'remote_working'      => $order->report->remote_working,
                'ac_dismantled'       => $order->report->ac_dismantled,
                'unit_safe_transport' => $order->report->unit_safe_transport,
            ] : null,
            'rating'                 => $order->rating ? [
                'rating' => $order->rating->rating,
                'review' => $order->rating->review,
            ] : null,
            'complaint'              => $order->complaint ? [
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
