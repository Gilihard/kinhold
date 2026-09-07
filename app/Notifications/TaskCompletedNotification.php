<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Task $task,
        public User $completedBy,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable->wantsEmail('email_task_completed')) {
            return [];
        }

        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url');
        $points = $this->task->getEffectivePoints();
        $pointsText = $points > 0
            ? ' (+'.$points.' '.self::pluralRu($points, 'балл', 'балла', 'баллов').')'
            : '';

        return (new MailMessage)
            ->subject("Задача выполнена: {$this->task->title}")
            ->greeting("Привет, {$notifiable->name}!")
            ->line("**{$this->completedBy->name}** выполнил(а) задачу{$pointsText}:")
            ->line("**{$this->task->title}**")
            ->when($this->task->description, function (MailMessage $message) {
                $message->line($this->task->description);
            })
            ->action('Открыть задачи', "{$appUrl}/tasks")
            ->line('Так держать!');
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
