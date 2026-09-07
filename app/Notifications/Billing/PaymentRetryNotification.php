<?php

namespace App\Notifications\Billing;

use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Models\Family;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired by `kinhold:enforce-grace-period` on day 3 of the grace period.
 */
class PaymentRetryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Family $family,
        public int $daysRemaining = 4,
    ) {}

    public function via(object $notifiable): array
    {
        if (! $notifiable->wantsEmail('billing')) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $portalUrl = StripeWebhookController::billingPortalReturnUrl();

        return (new MailMessage)
            ->subject('Напоминание об оплате Kinhold')
            ->view('emails.billing.payment-retry', [
                'subject' => 'Напоминание об оплате Kinhold',
                'userName' => $notifiable->name,
                'familyName' => $this->family->name,
                'daysRemaining' => $this->daysRemaining,
                'portalUrl' => $portalUrl,
            ]);
    }
}
