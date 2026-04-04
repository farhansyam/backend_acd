<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    public function __construct(
        private \Kreait\Firebase\Contract\Messaging $messaging
    ) {}

    /**
     * Kirim notifikasi ke satu device
     */
    public function sendToDevice(
        string $fcmToken,
        string $title,
        string $body,
        array $data = []
    ): void {
        try {
            $message = CloudMessage::fromArray([
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => array_map('strval', $data),
            ]);

            $this->messaging->send($message);

            Log::info('FCM sent', [
                'title' => $title,
                'token' => substr($fcmToken, 0, 20) . '...',
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM error', [
                'message' => $e->getMessage(),
                'title'   => $title,
            ]);
        }
    }

    /**
     * Notifikasi ke customer — order dikonfirmasi BP
     */
    public function notifyOrderConfirmed(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '✅ Pesanan Dikonfirmasi',
            "Pesanan #$orderId kamu sudah dikonfirmasi oleh mitra kami. Teknisi akan segera datang.",
            ['type' => 'order_confirmed', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke customer — teknisi dalam perjalanan
     */
    public function notifyTechnicianOnTheWay(string $fcmToken, int $orderId, string $technicianName): void
    {
        $this->sendToDevice(
            $fcmToken,
            '🛵 Teknisi Sedang Menuju Lokasi',
            "$technicianName sedang dalam perjalanan ke lokasi kamu untuk pesanan #$orderId.",
            ['type' => 'technician_on_the_way', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke customer — teknisi selesai, minta konfirmasi
     */
    public function notifyWaitingConfirmation(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '🎉 Pekerjaan Selesai!',
            "Teknisi sudah menyelesaikan pesanan #$orderId. Konfirmasi sekarang atau otomatis terkonfirmasi dalam 24 jam.",
            ['type' => 'waiting_confirmation', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke customer — order selesai & terkonfirmasi
     */
    public function notifyOrderCompleted(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '✨ Pesanan Selesai',
            "Pesanan #$orderId telah selesai. Terima kasih sudah menggunakan layanan Dikari!",
            ['type' => 'order_completed', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke teknisi — ada order baru di-assign
     */
    public function notifyTechnicianAssigned(string $fcmToken, int $orderId, string $address): void
    {
        $this->sendToDevice(
            $fcmToken,
            '📋 Order Baru!',
            "Kamu mendapat order baru #$orderId di $address.",
            ['type' => 'order_assigned', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke teknisi — saldo dirilis
     */
    public function notifyBalanceReleased(string $fcmToken, float $amount, int $orderId): void
    {
        $formatted = 'Rp ' . number_format($amount, 0, ',', '.');
        $this->sendToDevice(
            $fcmToken,
            '💰 Saldo Diterima',
            "$formatted dari order #$orderId sudah masuk ke saldo kamu.",
            ['type' => 'balance_released', 'order_id' => (string) $orderId, 'amount' => (string) $amount]
        );
    }
}
