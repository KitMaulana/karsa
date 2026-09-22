<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Notifikasi peringatan kenaikan risiko ke warga pemantau wilayah -- push +
 * in-app (CLAUDE.md §13). Deep link ke /aksi?kecamatan=... sesuai spesifikasi.
 */
class RiskAlertNotification extends Notification
{
    public function __construct(private readonly Alert $alert) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'district_id' => $this->alert->district_id,
            'message' => $this->alert->message,
            'to_level' => $this->alert->to_level->value,
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $district = $this->alert->district;

        return (new WebPushMessage)
            ->title('Peringatan KARSA: '.$district->name)
            ->icon('/icons/icon-192.png')
            ->body($this->alert->message)
            ->data(['url' => '/aksi?kecamatan='.$district->slug])
            ->options(['TTL' => 3600]);
    }
}
