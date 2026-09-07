<?php

namespace App\Notifications\Billing;

use App\Models\Family;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired by the `customer.subscription.deleted` webhook handler.
 */
class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Family $family) {}

    public function via(object $notifiable): array
    {
        if (! $notifiable->wantsEmail('billing')) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = (string) config('app.url');

        return (new MailMessage)
            ->subject('Ваша подписка Kinhold закончилась')
            ->view('emails.billing.subscription-cancelled', [
                'subject' => 'Ваша подписка Kinhold закончилась',
                'userName' => $notifiable->name,
                'familyName' => $this->family->name,
                'appUrl' => $appUrl,
                'portalUrl' => $appUrl,
            ]);
    }
}
