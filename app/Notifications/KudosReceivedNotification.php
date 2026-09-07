<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class KudosReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $from,
        public string $reason,
        public ?PointTransaction $transaction = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->wants('email', 'kudos_received')) {
            $channels[] = 'mail';
        }

        if ($notifiable->wants('push', 'kudos_received') && ! $notifiable->isPushSuppressed()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url');

        return (new MailMessage)
            ->subject("{$this->from->name} отправил(а) вам похвалу!")
            ->greeting("Отличная работа, {$notifiable->name}!")
            ->line("**{$this->from->name}** отправил(а) вам похвалу:")
            ->line("> {$this->reason}")
            ->action('Посмотреть баллы', "{$appUrl}/points");
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title("{$this->from->name} отправил(а) вам похвалу")
            ->body($this->reason)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge-96.png')
            ->tag('kudos-'.($this->transaction?->id ?? uniqid()))
            ->data([
                'type' => 'kudos_received',
                'url' => '/points',
                'from_id' => $this->from->id,
            ])
            ->options(['TTL' => 60 * 60 * 24]);
    }
}
