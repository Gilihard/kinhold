<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->wants('email', 'task_due_soon')) {
            $channels[] = 'mail';
        }

        if ($notifiable->wants('push', 'task_due_soon') && ! $notifiable->isPushSuppressed()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url');
        $dueText = $this->task->due_date
            ? 'Срок: '.(clone $this->task->due_date)->locale('ru')->translatedFormat('j F Y')
            : 'Срок — сегодня';

        return (new MailMessage)
            ->subject("Напоминание: {$this->task->title} — срок сегодня")
            ->greeting("Привет, {$notifiable->name}!")
            ->line('Срок этой задачи — сегодня:')
            ->line("**{$this->task->title}**")
            ->when($this->task->description, function (MailMessage $message) {
                $message->line($this->task->description);
            })
            ->line($dueText)
            ->action('Открыть задачу', "{$appUrl}/tasks");
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $points = $this->task->getEffectivePoints();
        $body = $points > 0
            ? 'На кону '.$points.' '.self::pluralRu($points, 'балл', 'балла', 'баллов')
            : 'Напоминание из Kinhold';

        return (new WebPushMessage)
            ->title("Срок сегодня: {$this->task->title}")
            ->body($body)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge-96.png')
            ->tag('task-due-'.$this->task->id)
            ->data([
                'type' => 'task_due_soon',
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
