<?php

namespace App\Services\Notify;

use App\Mail\AlertToAuthorityMail;
use App\Mail\ReportForwardedMail;
use App\Models\AuthorityContact;
use App\Models\Report;
use Illuminate\Support\Facades\Mail;

class EmailAuthorityNotifier implements AuthorityNotifier
{
    public function notifyAlert(AuthorityContact $contact, string $districtName, string $message): void
    {
        if (blank($contact->email)) {
            return;
        }

        Mail::to($contact->email)->queue(new AlertToAuthorityMail($contact, $districtName, $message));
    }

    public function notifyVerifiedReport(AuthorityContact $contact, Report $report): void
    {
        if (blank($contact->email)) {
            return;
        }

        Mail::to($contact->email)->queue(new ReportForwardedMail($contact, $report));
    }

    /**
     * Tautan wa.me untuk tombol "Kirim via WhatsApp" di panel admin (CLAUDE.md §13).
     */
    public static function whatsappLink(AuthorityContact $contact, string $message): ?string
    {
        if (blank($contact->whatsapp)) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $contact->whatsapp);

        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }
}
