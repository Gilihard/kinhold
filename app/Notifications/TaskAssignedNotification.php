<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $assignedBy,
    ) {}

    /**
     * Channels chosen per-user from the unified notification_preferences shape.
     * Push is suppressed during quiet hours / global mute (see User::isPushSuppressed).
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->wants('email', 'task_assigned')) {
            $channels[] = 'mail';
        }

        if ($notifiable->wants('push', 'task_assigned') && ! $notifiable->isPushSuppressed()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url');
        $dueText = $this->task->due_date
            ? 'Срок: '.(clone $this->task->due_date)->locale('ru')->translatedFormat('j F Y')
            : 'Без срока';

        $points = $this->task->getEffectivePoints();
        $pointsText = $points > 0
            ? ' (+'.$points.' '.self::pluralRu($points, 'балл', 'балла', 'баллов').')'
            : '';

        return (new MailMessage)
            ->subject("Назначена новая задача: {$this->task->title}")
            ->greeting("Привет, {$notifiable->name}!")
            ->line("**{$this->assignedBy->name}** назначил(а) вам новую задачу{$pointsText}:")
            ->line("**{$this->task->title}**")
            ->when($this->task->description, function (MailMessage $message) {
                $message->line($this->task->description);
            })
            ->line($dueText)
            ->action('Открыть задачу', "{$appUrl}/tasks")
            ->line('У вас всё получится!');
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $points = $this->task->getEffectivePoints();
        $body = $this->assignedBy->name.' назначил(а) вам задачу'
            .($points > 0 ? ' (+'.$points.' '.self::pluralRu($points, 'балл', 'балла', 'баллов').')' : '');

        return (new WebPushMessage)
            ->title($this->task->title)
            ->body($body)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge-96.png')
            ->tag('task-'.$this->task->id)
            ->data([
                'type' => 'task_assigned',
                'url' => '/tasks?focus='.$this->task->id,
                'task_id' => $this->task->id,
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
