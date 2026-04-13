<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WarrantyActiveMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $warrantyExpires;

    public function __construct(public Order $order)
    {
        $this->warrantyExpires = $order->warranty_expires_at?->format('d M Y H:i') ?? '-';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🛡️ Masa Garansi Aktif - Pesanan #$this->order->id",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.warranty_active');
    }
}
