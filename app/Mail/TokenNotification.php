<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TokenNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public string $messageContent;
    public string $customSubject; // Renamed to avoid conflict with parent Mailable::$subject

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, string $messageContent, string $subject = 'Electricity Token - WinIt Prize Distribution')
    {
        $this->transaction = $transaction;
        $this->messageContent = $messageContent;
        $this->customSubject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.anti-spam-token-notification', // Using the anti-spam template
            with: [
                'transaction' => $this->transaction,
                'recipient' => $this->transaction->recipient,
                'messageContent' => $this->messageContent,
                'token' => $this->transaction->token,
                'units' => $this->transaction->units,
                'amount' => $this->transaction->amount,
                'meter_number' => $this->transaction->recipient->meter_number,
                'disco' => $this->transaction->recipient->disco,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
