<?php

namespace App\Services\Notify;

use App\Models\AuthorityContact;
use App\Models\Report;

interface AuthorityNotifier
{
    public function notifyAlert(AuthorityContact $contact, string $districtName, string $message): void;

    public function notifyVerifiedReport(AuthorityContact $contact, Report $report): void;
}
