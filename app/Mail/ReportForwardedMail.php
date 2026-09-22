<?php

namespace App\Mail;

use App\Models\AuthorityContact;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportForwardedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AuthorityContact $contact,
        public Report $report,
    ) {}

    public function build(): self
    {
        return $this->subject('Laporan karhutla diteruskan dari KARSA')
            ->view('emails.report-forwarded');
    }
}
