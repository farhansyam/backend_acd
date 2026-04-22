<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\Technician;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    // POST submit rating
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'rating'               => 'required|integer|min:1|max:5',
            'review'               => 'nullable|string|max:500',
            // Rating teknisi pasang (opsional, khusus relokasi beda lokasi 2 teknisi)
            'second_rating'        => 'nullable|integer|min:1|max:5',
            'second_review'        => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        abort_if($order->user_id !== $user->id, 403, 'Bukan order kamu.');
        abort_if(!in_array($order->status, ['warranty', 'completed', 'complained']), 422, 'Order belum selesai.');
        abort_if($order->rating()->exists(), 422, 'Rating sudah diberikan.');
        abort_if(!$order->technician_id, 422, 'Order belum punya teknisi.');

        // Rating teknisi bongkar (atau teknisi tunggal)
        // Rating teknisi bongkar (atau teknisi tunggal)
        $rating = OrderRating::create([
            'order_id'      => $order->id,
            'user_id'       => $user->id,
            'technician_id' => $order->technician_id,
            'rating'        => $request->rating,
            'review'        => $request->review,
            // Langsung simpan rating teknisi pasang di row yang sama
            'second_technician_id' => ($order->split_technician && $order->second_technician_id && $request->filled('second_rating'))
                ? $order->second_technician_id : null,
            'second_rating' => $request->second_rating ?? null,
            'second_review' => $request->second_review ?? null,
        ]);

        // Update avg rating teknisi bongkar
        $this->updateAvgRating($order->technician_id);

        // Update avg rating teknisi pasang
        if ($order->split_technician && $order->second_technician_id && $request->filled('second_rating')) {
            $this->updateAvgRating($order->second_technician_id);
        }

        return response()->json([
            'message' => 'Rating berhasil dikirim. Terima kasih!',
            'rating'  => [
                'id'     => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
            ],
        ]);
    }

    private function updateAvgRating(int $technicianId): void
    {
        $technician = Technician::find($technicianId);
        if ($technician) {
            $avg = OrderRating::where('technician_id', $technicianId)->avg('rating');
            $technician->update(['avg_rating' => round($avg, 1)]);
        }
    }

    // GET rating sebuah order
    public function show(Order $order)
    {
        $rating = $order->rating;
        if (!$rating) {
            return response()->json(['rating' => null]);
        }

        return response()->json([
            'rating' => [
                'id'         => $rating->id,
                'rating'     => $rating->rating,
                'review'     => $rating->review,
                'created_at' => $rating->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function public()
    {
        $ratings = \App\Models\OrderRating::with(['order.user', 'technician.user'])
            ->where('rating', '>=', 4)
            ->whereNotNull('review')
            ->where('review', '!=', '')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'rating'          => $r->rating,
                'review'          => $r->review,
                'customer_name'   => $r->order?->user?->name ?? 'Customer',
                'customer_avatar' => $r->order?->user?->avatar,
                'technician_name' => $r->technician?->user?->name ?? 'Teknisi',
                'created_at'      => $r->created_at->format('d M Y'),
            ]);

        return response()->json(['reviews' => $ratings]);
    }
}
