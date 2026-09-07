<?php

namespace App\Notifications;

use App\Models\Family;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Family $family,
        public bool $isNewFamily = false,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Always send welcome emails (no preference check)
        if (! $notifiable->email) {
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

        $message = (new MailMessage)
            ->subject('Добро пожаловать в Kinhold!')
            ->greeting("Добро пожаловать, {$notifiable->name}!")
            ->line("Вы успешно присоединились к семье **{$this->family->name}** в Kinhold.");

        if ($this->isNewFamily) {
            $message->line('Как создатель семьи, вы имеете полный доступ к управлению семейным центром. Вот что вам доступно:');
        } else {
            $message->line('Вот что вам доступно:');
        }

        $message
            ->line('- **Календарь**: просматривайте все события семьи в одном месте')
            ->line('- **Задачи**: создавайте и назначайте задачи, зарабатывайте баллы за их выполнение')
            ->line('- **Сейф**: надёжно храните важные семейные документы и информацию')
            ->line('- **Kinhold AI**: задавайте вопросы о данных вашей семьи')
            ->action('Начать', $appUrl);

        if ($this->isNewFamily) {
            $message->line('Пригласите членов семьи в разделе «Настройки», чтобы собрать всех вместе!');
        }

        return $message;
    }
}
