<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $digest,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable->wantsEmail('email_weekly_digest')) {
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
        $familyName = $this->digest['family_name'] ?? 'Ваша семья';
        $weekRange = $this->digest['week_range'] ?? 'Эта неделя';

        $message = (new MailMessage)
            ->subject("Еженедельный дайджест для {$familyName}")
            ->greeting("Привет, {$notifiable->name}!")
            ->line("Вот еженедельная сводка для **{$familyName}** ({$weekRange}):");

        // Tasks summary
        $tasksCompleted = $this->digest['tasks_completed'] ?? 0;
        $tasksPending = $this->digest['tasks_pending'] ?? 0;
        $tasksOverdue = $this->digest['tasks_overdue'] ?? 0;

        $message->line('**Задачи**');
        $message->line("- За эту неделю выполнено задач: {$tasksCompleted}");
        $message->line("- Ожидают выполнения: {$tasksPending}");

        if ($tasksOverdue > 0) {
            $message->line("- Просрочено: {$tasksOverdue}");
        }

        // Points summary
        $pointsEarned = $this->digest['points_earned'] ?? 0;
        $pointsBank = $this->digest['points_bank'] ?? 0;

        if ($pointsEarned > 0 || $pointsBank > 0) {
            $message->line('**Баллы**');
            $message->line('- Заработано за эту неделю: '.$pointsEarned.' '.self::pluralRu($pointsEarned, 'балл', 'балла', 'баллов'));
            $message->line('- Всего на счету: '.$pointsBank.' '.self::pluralRu($pointsBank, 'балл', 'балла', 'баллов'));
        }

        // Upcoming tasks
        $upcomingTasks = $this->digest['upcoming_tasks'] ?? [];
        if (count($upcomingTasks) > 0) {
            $message->line('**На следующей неделе**');
            foreach (array_slice($upcomingTasks, 0, 5) as $task) {
                $dueDate = $task['due_date'] ?? '';
                $message->line('- '.$task['title'].($dueDate ? ' (срок: '.$dueDate.')' : ''));
            }
        }

        // Badges earned
        $badgesEarned = $this->digest['badges_earned'] ?? [];
        if (count($badgesEarned) > 0) {
            $message->line('**Полученные значки**');
            foreach ($badgesEarned as $badge) {
                $message->line("- {$badge['name']}: {$badge['description']}");
            }
        }

        $message
            ->action('Открыть Kinhold', $appUrl)
            ->line('Хорошей недели!');

        return $message;
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
