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

    public function notifyComplaintReceived(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '⚠️ Komplain Baru',
            "Order #{$orderId} mendapat komplain dari customer.",
            ['type' => 'complaint_received', 'order_id' => (string) $orderId]
        );
    }
    public function notifyWarrantyActive(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '🛡️ Masa Garansi Aktif',
            "Pesanan #$orderId masuk masa garansi 7 hari. Ajukan komplain jika ada masalah.",
            ['type' => 'warranty_active', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke customer — garansi akan expired besok
     */
    public function notifyWarrantyExpiringSoon(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '⏰ Garansi Hampir Habis',
            "Masa garansi pesanan #$orderId akan berakhir besok. Segera ajukan komplain jika ada masalah.",
            ['type' => 'warranty_expiring_soon', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke teknisi — garansi order expired
     */
    public function notifyWarrantyExpired(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '✅ Garansi Selesai',
            "Masa garansi order #$orderId sudah habis. Pesanan dinyatakan selesai.",
            ['type' => 'warranty_expired', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke teknisi — withdraw disetujui admin
     */
    public function notifyWithdrawApproved(string $fcmToken, float $amount): void
    {
        $formatted = 'Rp ' . number_format($amount, 0, ',', '.');
        $this->sendToDevice(
            $fcmToken,
            '💸 Penarikan Disetujui',
            "Penarikan saldo $formatted kamu telah disetujui dan sedang diproses.",
            ['type' => 'withdraw_approved', 'amount' => (string) $amount]
        );
    }

    public function notifyWithdrawRejected(string $fcmToken, float $amount, string $reason): void
    {
        $formatted = 'Rp ' . number_format($amount, 0, ',', '.');
        $this->sendToDevice(
            $fcmToken,
            '❌ Penarikan Ditolak',
            "Penarikan saldo $formatted ditolak. Alasan: $reason",
            ['type' => 'withdraw_rejected', 'amount' => (string) $amount]
        );
    }

    // app/Services/NotificationService.php — tambah 2 method baru

    /**
     * Notifikasi ke customer — hasil survey siap dilihat
     */
    public function notifySurveyResult(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '🔍 Hasil Survey AC Anda',
            "Teknisi telah menyelesaikan survei pesanan #$orderId. Cek hasil dan pilih tindakan selanjutnya.",
            ['type' => 'survey_result', 'order_id' => (string) $orderId]
        );
    }

    /**
     * Notifikasi ke teknisi — customer lanjut ke fase 2
     */
    public function notifyPhase2Confirmed(string $fcmToken, int $orderId): void
    {
        $this->sendToDevice(
            $fcmToken,
            '✅ Customer Lanjut ke Fase 2',
            "Customer menyetujui untuk melanjutkan. Silakan kerjakan pesanan #$orderId.",
            ['type' => 'phase2_confirmed', 'order_id' => (string) $orderId]
        );
    }
}
