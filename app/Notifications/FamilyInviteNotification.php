<?php

namespace App\Notifications;

use App\Models\Family;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FamilyInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Family $family,
        public string $inviteCode,
        public string $inviterName,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url');
        $joinUrl = "{$appUrl}/register?invite_code={$this->inviteCode}";

        return (new MailMessage)
            ->subject("Вас пригласили в семью {$this->family->name} в Kinhold")
            ->greeting('Здравствуйте!')
            ->line("{$this->inviterName} приглашает вас присоединиться к семье **{$this->family->name}** в Kinhold.")
            ->line('Kinhold — это семейный центр для управления календарём, задачами, важными документами и не только: всё в одном месте.')
            ->line("**Ваш код приглашения:** `{$this->inviteCode}`")
            ->action('Присоединиться к семье', $joinUrl)
            ->line('Код приглашения также можно ввести вручную при регистрации.')
            ->salutation('Добро пожаловать в семью!');
    }
}
