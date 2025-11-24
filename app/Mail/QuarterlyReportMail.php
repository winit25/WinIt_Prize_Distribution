<?php

namespace App\Mail;

use App\Models\Recipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuarterlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $summary;

    public function __construct(Recipient $recipient, array $summary)
    {
        $this->recipient = $recipient;
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->subject('WinIt Prize Distribution - Quarterly Token Summary Report')
                    ->view('emails.quarterly-report')
                    ->with([
                        'recipient' => $this->recipient,
                        'summary' => $this->summary,
                    ]);
    }

    public function envelope()
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'WinIt Prize Distribution - Quarterly Token Summary Report'
        );
    }
}
