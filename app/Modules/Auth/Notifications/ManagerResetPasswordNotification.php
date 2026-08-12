<?php

namespace App\Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManagerResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('auth.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject(__('auth.reset_password_subject'))
            ->line(__('auth.reset_password_email_line'))
            ->action(__('auth.reset_password_button'), $resetUrl)
            ->line(__('auth.reset_password_expire_line', ['count' => config('auth.passwords.managers.expire', 60)]))
            ->line(__('auth.reset_password_ignore_line'));
    }
}
