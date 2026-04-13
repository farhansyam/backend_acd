<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\BusinessPartner;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Technician;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\ComplaintReceivedMail;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    // ─── GET list komplain customer ───────────────────────────
    public function index(Request $request)
    {
        $complaints = Complaint::with(['order', 'technician.user', 'reworkTechnician.user'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => $this->formatComplaint($c));

        return response()->json(['complaints' => $complaints]);
    }

    // ─── POST ajukan komplain ─────────────────────────────────
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'photo'       => 'nullable|image|max:5120',
        ]);

        $user = $request->user();

        \Log::info('Complaint store', [
            'order_id'       => $order->id,
            'order_status'   => $order->status,
            'order_user_id'  => $order->user_id,
            'user_id'        => $user->id,
            'warranty_exp'   => $order->warranty_expires_at,
            'has_complaint'  => $order->complaint()->exists(),
        ]);

        abort_if($order->user_id !== $user->id, 403, 'Bukan order kamu.');
        abort_if($order->status !== 'warranty', 422, 'Order tidak dalam masa garansi.');
        abort_if($order->complaint()->exists(), 422, 'Komplain sudah pernah diajukan.');
        abort_if(!$order->warranty_expires_at || now()->gt($order->warranty_expires_at), 422, 'Masa garansi sudah berakhir.');

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store("complaints/{$order->id}", 'public');
        }

        DB::transaction(function () use ($order, $user, $request, $photoPath) {
            $complaint = Complaint::create([
                'order_id'           => $order->id,
                'user_id'            => $user->id,
                'bp_id'              => $order->bp_id,
                'technician_id'      => $order->technician_id,
                'title'              => $request->title,
                'description'        => $request->description,
                'photo'              => $photoPath,
                'status'             => 'open',
                'warranty_expires_at' => $order->warranty_expires_at,
            ]);
            $bp = BusinessPartner::with('user')->find($order->bp_id);
            if ($bp?->user?->email) {
                Mail::to($bp->user->email)->queue(new ComplaintReceivedMail($complaint->fresh()->load(['user', 'technician.user'])));
            }
            $order->update(['status' => 'complained']);
        });


        // Notif ke BP
        $bp = BusinessPartner::find($order->bp_id);
        if ($bp?->user?->fcm_token) {
            $this->notificationService->notifyComplaintReceived(
                $bp->user->fcm_token,
                $order->id
            );
        }


        return response()->json(['message' => 'Komplain berhasil diajukan.'], 201);
    }

    // ─── GET detail komplain ──────────────────────────────────
    public function show(Complaint $complaint)
    {
        $complaint->load(['order', 'technician.user', 'reworkTechnician.user']);
        return response()->json(['complaint' => $this->formatComplaint($complaint)]);
    }

    // ─── Helper format ────────────────────────────────────────
    private function formatComplaint(Complaint $c): array
    {
        return [
            'id'                   => $c->id,
            'order_id'             => $c->order_id,
            'title'                => $c->title,
            'description'          => $c->description,
            'photo'                => $c->photo ? url('storage/' . $c->photo) : null,
            'bp_comment'           => $c->bp_comment,
            'status'               => $c->status,
            'status_label'         => $c->status_label,
            'technician_name'      => $c->technician?->user?->name ?? '-',
            'rework_technician'    => $c->reworkTechnician ? [
                'name'  => $c->reworkTechnician->user?->name ?? '-',
                'grade' => $c->reworkTechnician->grade,
            ] : null,
            'warranty_expires_at'  => $c->warranty_expires_at?->toIso8601String(),
            'resolved_at'          => $c->resolved_at?->toIso8601String(),
            'created_at'           => $c->created_at->format('Y-m-d H:i'),
        ];
    }
}
