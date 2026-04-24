<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BpService;
use App\Models\BusinessPartner;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionSession;
use App\Services\SubscriptionPaymentService;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionPaymentService $paymentService,
        private BalanceService $balanceService,
    ) {}

    // ─── 1. List paket aktif ──────────────────────────────────────
    public function packages(): JsonResponse
    {
        $packages = SubscriptionPackage::where('is_active', true)
            ->orderBy('interval_months', 'desc')
            ->get();

        return response()->json(['packages' => $packages]);
    }

    // ─── 2. List service cuci_reguler (untuk dipilih customer) ───
    // GET /subscriptions/services?address_id=x
    public function services(Request $request): JsonResponse
    {
        $request->validate(['address_id' => 'required|exists:addresses,id']);

        $address = \App\Models\Address::findOrFail($request->address_id);
        $bp      = $this->resolveBpFromAddress($address);

        if (!$bp) {
            return response()->json(['message' => 'Layanan tidak tersedia di area kamu.'], 422);
        }

        $services = BpService::with('serviceType')
            ->where('bp_id', $bp->id)
            ->where('is_active', 1)
            ->whereHas('serviceType', fn($q) => $q->where('category', 'cuci_reguler'))
            ->get()
            ->map(fn($s) => [
                'bp_service_id' => $s->id,
                'name'          => $s->serviceType->name,
                'base_price'    => $s->base_service,
                'banner_url'    => $s->banner ? asset('storage/' . $s->banner) : null,
            ]);

        return response()->json([
            'bp_id'    => $bp->id,
            'bp_name'  => $bp->name,
            'services' => $services,
        ]);
    }

    // ─── 3. Preview harga ─────────────────────────────────────────
    // POST /subscriptions/preview
    // Body: { package_type, address_id, items: [{bp_service_id, quantity}] }
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'package_type'           => 'required|in:hemat,rutin,intensif',
            'address_id'             => 'required|exists:addresses,id',
            'items'                  => 'required|array|min:1',
            'items.*.bp_service_id'  => 'required|exists:bp_services,id',
            'items.*.quantity'       => 'required|integer|min:1',
        ]);

        $address = \App\Models\Address::findOrFail($request->address_id);
        $bp      = $this->resolveBpFromAddress($address);
        abort_if(!$bp, 422, 'Layanan tidak tersedia di area kamu.');

        $package = SubscriptionPackage::where('type', $request->package_type)->firstOrFail();

        $itemBreakdown = [];
        $subtotalPerSession = 0;

        foreach ($request->items as $item) {
            $bpService = BpService::with('serviceType')->findOrFail($item['bp_service_id']);

            // Pastikan service milik BP yang sesuai alamat & kategori cuci_reguler
            abort_if($bpService->bp_id != $bp->id, 422, 'Service tidak valid.');
            abort_if($bpService->serviceType->category !== 'cuci_reguler', 422, 'Hanya service cuci reguler yang bisa langganan.');

            $unitPrice        = $bpService->base_service;
            $qty              = $item['quantity'];
            $perSessionItem   = $unitPrice * $qty;
            $subtotalPerSession += $perSessionItem;

            $itemBreakdown[] = [
                'bp_service_id'       => $bpService->id,
                'name'                => $bpService->serviceType->name,
                'quantity'            => $qty,
                'unit_price'          => $unitPrice,
                'subtotal_per_session' => $perSessionItem,
            ];
        }

        // Subtotal = per sesi × total sesi (harga normal)
        $subtotal      = $subtotalPerSession * $package->total_sessions;
        // Total = subtotal × multiplier (harga langganan)
        $totalAmount   = round($subtotal * $package->price_multiplier);
        $discountAmount = $subtotal - $totalAmount;

        return response()->json([
            'package'         => $package,
            'items'           => $itemBreakdown,
            'subtotal_per_session' => $subtotalPerSession,
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount'    => $totalAmount,
            'total_sessions'  => $package->total_sessions,
        ]);
    }

    // ─── 4. Buat langganan ────────────────────────────────────────
    // POST /subscriptions
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'package_type'           => 'required|in:hemat,rutin,intensif',
            'address_id'             => 'required|exists:addresses,id',
            'user_phone_id'          => 'required|exists:user_phones,id',
            'items'                  => 'required|array|min:1',
            'items.*.bp_service_id'  => 'required|exists:bp_services,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'payment_method'         => 'required|string',
        ]);

        // Validasi kepemilikan alamat & telepon
        abort_if(
            !$request->user()->addresses()->where('id', $request->address_id)->exists(),
            403,
            'Alamat tidak valid.'
        );
        abort_if(
            !$request->user()->phones()->where('id', $request->user_phone_id)->exists(),
            403,
            'Nomor telepon tidak valid.'
        );

        $address = \App\Models\Address::findOrFail($request->address_id);
        $bp      = $this->resolveBpFromAddress($address);
        abort_if(!$bp, 422, 'Layanan tidak tersedia di area kamu.');

        $package = SubscriptionPackage::where('type', $request->package_type)->firstOrFail();

        // Hitung harga
        $subtotalPerSession = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $bpService = BpService::with('serviceType')->findOrFail($item['bp_service_id']);
            abort_if($bpService->bp_id != $bp->id, 422, 'Service tidak valid.');
            abort_if($bpService->serviceType->category !== 'cuci_reguler', 422, 'Hanya service cuci reguler.');

            $unitPrice          = $bpService->base_service;
            $qty                = $item['quantity'];
            $perSessionItem     = $unitPrice * $qty;
            $subtotalPerSession += $perSessionItem;

            $itemsData[] = [
                'bp_service_id'        => $bpService->id,
                'quantity'             => $qty,
                'unit_price'           => $unitPrice,
                'subtotal_per_session' => $perSessionItem,
                'subtotal_total'       => $perSessionItem * $package->total_sessions,
            ];
        }

        $subtotal       = $subtotalPerSession * $package->total_sessions;
        $totalAmount    = round($subtotal * $package->price_multiplier);
        $discountAmount = $subtotal - $totalAmount;

        $subscription = DB::transaction(function () use (
            $request,
            $package,
            $itemsData,
            $subtotal,
            $totalAmount,
            $discountAmount,
            $bp
        ) {
            $sub = Subscription::create([
                'user_id'                  => $request->user()->id,
                'address_id'               => $request->address_id,
                'user_phone_id'            => $request->user_phone_id,
                'bp_id'                    => $bp->id,
                'subscription_package_id'  => $package->id,
                'subtotal'                 => $subtotal,
                'discount_amount'          => $discountAmount,
                'total_amount'             => $totalAmount,
                'payment_method'           => $request->payment_method,
                'payment_status'           => 'unpaid',
                'status'                   => 'pending',
            ]);

            foreach ($itemsData as $item) {
                $sub->items()->create($item);
            }

            return $sub;
        });

        // Buat transaksi Tripay (atau DikariPay)
        try {
            $paymentResult = $this->paymentService->createTransaction($subscription, $request->payment_method);

            $subscription->update([
                'tripay_reference'   => $paymentResult['reference'] ?? null,
                'tripay_payment_url' => $paymentResult['payment_url'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Jangan hapus subscription, biar bisa retry payment
            \Log::error('Subscription payment error: ' . $e->getMessage());
        }

        return response()->json([
            'message'      => 'Langganan berhasil dibuat.',
            'subscription' => $subscription->load(['package', 'items.bpService.serviceType']),
            'payment_url'  => $subscription->tripay_payment_url,
        ], 201);
    }

    // ─── 5. List langganan customer ───────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with(['package', 'sessions'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn($sub) => [
                'id'               => $sub->id,
                'package_name'     => $sub->package->name,
                'package_type'     => $sub->package->type,
                'total_amount'     => $sub->total_amount,
                'payment_status'   => $sub->payment_status,
                'status'           => $sub->status,
                'starts_at'        => $sub->starts_at?->format('Y-m-d'),
                'expires_at'       => $sub->expires_at?->format('Y-m-d'),
                'total_sessions'   => $sub->package->total_sessions,
                'completed_sessions' => $sub->sessions->where('status', 'completed')->count(),
            ]);

        return response()->json(['subscriptions' => $subscriptions]);
    }

    // ─── 6. Detail langganan ──────────────────────────────────────
    public function show(Request $request, Subscription $subscription): JsonResponse
    {
        abort_if($subscription->user_id !== $request->user()->id, 403);

        $subscription->load([
            'package',
            'items.bpService.serviceType',
            'address',
            'userPhone',
            'sessions.technician.user',
            'sessions.report',
        ]);

        return response()->json(['subscription' => $subscription]);
    }

    // ─── 7. Set jadwal semua sesi ─────────────────────────────────
    // POST /subscriptions/{subscription}/schedule
    // Body: { schedules: [{session_number, scheduled_date, scheduled_time}] }
    public function setSchedule(Request $request, Subscription $subscription): JsonResponse
    {
        abort_if($subscription->user_id !== $request->user()->id, 403);
        abort_if($subscription->payment_status !== 'paid', 422, 'Langganan belum dibayar.');
        abort_if($subscription->sessions()->exists(), 422, 'Jadwal sudah diset sebelumnya.');

        $totalSessions = $subscription->package->total_sessions;
        $intervalMonths = $subscription->package->interval_months;

        $request->validate([
            'schedules'                     => "required|array|size:{$totalSessions}",
            'schedules.*.session_number'    => 'required|integer|min:1',
            'schedules.*.scheduled_date'    => 'required|date|after_or_equal:today',
            'schedules.*.scheduled_time'    => 'required|string',
        ]);

        // Validasi range tanggal tiap sesi
        $schedules = collect($request->schedules)->sortBy('session_number')->values();
        $firstDate = \Carbon\Carbon::parse($schedules[0]['scheduled_date']);

        foreach ($schedules as $i => $schedule) {
            $sessionNum = $i + 1;
            if ($sessionNum === 1) continue;

            // Batas bawah: jadwal sesi pertama + (n-1) * interval
            $minDate = $firstDate->copy()->addMonths(($sessionNum - 1) * $intervalMonths);
            // Toleransi ±7 hari dari batas bawah
            $minAllowed = $minDate->copy()->subDays(7);
            $maxAllowed = $minDate->copy()->addDays(7);

            $sessionDate = \Carbon\Carbon::parse($schedule['scheduled_date']);
            abort_if(
                $sessionDate->lt($minAllowed) || $sessionDate->gt($maxAllowed),
                422,
                "Jadwal sesi ke-{$sessionNum} harus sekitar " . $minDate->format('d M Y') . " (±7 hari)."
            );
        }

        DB::transaction(function () use ($subscription, $schedules, $firstDate, $intervalMonths) {
            foreach ($schedules as $schedule) {
                SubscriptionSession::create([
                    'subscription_id' => $subscription->id,
                    'session_number'  => $schedule['session_number'],
                    'scheduled_date'  => $schedule['scheduled_date'],
                    'scheduled_time'  => $schedule['scheduled_time'],
                    'status'          => 'scheduled',
                ]);
            }

            $subscription->update([
                'starts_at'  => $firstDate->toDateString(),
                'expires_at' => $firstDate->copy()->addYear()->toDateString(),
                'status'     => 'active',
            ]);
        });

        return response()->json([
            'message'  => 'Jadwal berhasil disimpan.',
            'sessions' => $subscription->fresh()->sessions,
        ]);
    }

    // ─── 8. Konfirmasi customer per sesi ─────────────────────────
    // POST /subscriptions/{subscription}/sessions/{session}/confirm
    public function confirmSession(Request $request, Subscription $subscription, SubscriptionSession $session): JsonResponse
    {
        abort_if($subscription->user_id !== $request->user()->id, 403);
        abort_if($session->subscription_id !== $subscription->id, 403);
        abort_if($session->status !== 'waiting_confirmation', 422, 'Sesi tidak dalam status menunggu konfirmasi.');

        DB::transaction(function () use ($subscription, $session) {
            $session->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            // Cair balance mitra untuk sesi ini
            $this->balanceService->releaseSubscriptionSessionEarning($session);

            // Cek apakah semua sesi selesai
            $allCompleted = $subscription->sessions()
                ->where('status', '!=', 'completed')
                ->doesntExist();

            if ($allCompleted) {
                $subscription->update(['status' => 'completed']);
            }
        });

        return response()->json(['message' => 'Sesi dikonfirmasi. Terima kasih!']);
    }

    // ─── Helper: resolve BP dari alamat customer ──────────────────
    private function resolveBpFromAddress(\App\Models\Address $address): ?BusinessPartner
    {
        // Match by city_name (case-insensitive) — konsisten dengan pola order biasa
        return BusinessPartner::whereRaw('LOWER(city) = ?', [strtolower($address->city_name)])
            ->first();
    }
}
