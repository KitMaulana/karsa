<?php

namespace App\Services\Notify;

use App\Models\Alert;
use App\Models\AuthorityContact;
use App\Notifications\RiskAlertNotification;

/**
 * Kirim peringatan ke warga pemantau wilayah (push + in-app) dan ke kontak
 * instansi yang level minimalnya terpenuhi (email). CLAUDE.md §13.
 */
class AlertDispatcher
{
    public function __construct(private readonly AuthorityNotifier $notifier) {}

    public function dispatch(Alert $alert): void
    {
        $district = $alert->district;

        $watchers = $district->watchers()->get();
        if ($watchers->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($watchers, new RiskAlertNotification($alert));
        }

        $contacts = AuthorityContact::where('district_id', $district->id)
            ->orWhere('regency_id', $district->regency_id)
            ->get()
            ->filter(fn (AuthorityContact $c) => $alert->to_level->rank() >= $c->min_level->rank());

        foreach ($contacts as $contact) {
            $this->notifier->notifyAlert($contact, $district->name, $alert->message);
        }
    }
}
