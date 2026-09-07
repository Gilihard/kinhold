<?php

namespace App\Notifications;

use App\Models\FamilyEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CalendarEventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FamilyEvent $event,
        public Carbon $occurrenceAt,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->wants('email', 'calendar_event_reminder')) {
            $channels[] = 'mail';
        }

        if ($notifiable->wants('push', 'calendar_event_reminder') && ! $notifiable->isPushSuppressed()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url');
        $timeLine = 'Начало: '.(clone $this->occurrenceAt)->locale('ru')->translatedFormat('j F, H:i');

        $message = (new MailMessage)
            ->subject("Скоро начнётся: {$this->event->title}")
            ->greeting("Привет, {$notifiable->name}!")
            ->line('Напоминание о предстоящем событии:')
            ->line("**{$this->event->title}**")
            ->line($timeLine);

        if ($this->event->location) {
            $message->line("Место: {$this->event->location}");
        }

        if ($this->event->recurrence_label) {
            $message->line($this->event->recurrence_label);
        }

        return $message->action('Открыть календарь', "{$appUrl}/calendar");
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $minutesBefore = (int) ($this->event->reminder_minutes_before ?? 0);
        $body = 'Через '.$minutesBefore.' '.self::pluralRu($minutesBefore, 'минуту', 'минуты', 'минут');
        if ($this->event->location) {
            $body .= " · {$this->event->location}";
        }

        return (new WebPushMessage)
            ->title($this->event->title)
            ->body($body)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge-96.png')
            ->tag('event-'.$this->event->id.'-'.$this->occurrenceAt->toDateString())
            ->data([
                'type' => 'calendar_event_reminder',
                'url' => '/calendar?event='.$this->event->id,
                'event_id' => $this->event->id,
                'occurrence_date' => $this->occurrenceAt->toDateString(),
            ])
            ->options(['TTL' => 60 * 60 * 24]);
    }

    private static function pluralRu(int $n, string $one, string $few, string $many): string
    {
        $abs = abs($n) % 100;
        $last = $abs % 10;
        if ($last === 1 && $abs !== 11) {
            return $one;
        }
        if ($last >= 2 && $last <= 4 && ($abs < 12 || $abs > 14)) {
            return $few;
        }

        return $many;
    }
}
