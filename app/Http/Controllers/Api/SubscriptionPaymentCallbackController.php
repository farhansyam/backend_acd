<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentCallbackController extends Controller
{
    public function __construct(private SubscriptionPaymentService $paymentService) {}

    public function handle(Request $request): \Illuminate\Http\JsonResponse
    {
        // Verifikasi signature Tripay (sama seperti PaymentController@callback)
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json              = $request->getContent();
        $signature         = hash_hmac('sha256', $json, config('tripay.private_key'));

        if (!hash_equals($signature, (string) $callbackSignature)) {
            Log::warning('Subscription callback: invalid signature');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $payload = $request->all();
        Log::info('Subscription Tripay callback', $payload);

        try {
            $this->paymentService->handleCallback($payload);
        } catch (\Exception $e) {
            Log::error('Subscription callback error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing callback'], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}
