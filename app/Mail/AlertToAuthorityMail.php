<?php

namespace App\Mail;

use App\Models\AuthorityContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertToAuthorityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AuthorityContact $contact,
        public string $districtName,
        public string $alertMessage,
    ) {}

    public function build(): self
    {
        return $this->subject("Peringatan KARSA: {$this->districtName}")
            ->view('emails.alert-to-authority');
    }
}
