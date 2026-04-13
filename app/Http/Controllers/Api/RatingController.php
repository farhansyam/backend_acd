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
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        abort_if($order->user_id !== $user->id, 403, 'Bukan order kamu.');
        abort_if($order->status !== 'completed', 422, 'Order belum selesai.');
        abort_if($order->rating()->exists(), 422, 'Rating sudah diberikan.');
        abort_if(!$order->technician_id, 422, 'Order belum punya teknisi.');

        $rating = OrderRating::create([
            'order_id'      => $order->id,
            'user_id'       => $user->id,
            'technician_id' => $order->technician_id,
            'rating'        => $request->rating,
            'review'        => $request->review,
        ]);

        // Update rata-rata rating teknisi
        $technician = Technician::find($order->technician_id);
        if ($technician) {
            $avgRating = OrderRating::where('technician_id', $technician->id)
                ->avg('rating');
            $technician->update(['avg_rating' => round($avgRating, 1)]);
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
}
