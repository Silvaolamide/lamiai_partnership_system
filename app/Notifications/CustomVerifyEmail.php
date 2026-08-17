<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your email address — AIPM')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Welcome to AIPM — AI Powered Marketing.')
            ->line('To complete your account setup and secure your account, please verify your email address by clicking the button below.')
            ->action('Verify My Email Address', $this->verificationUrl($notifiable))
            ->line('This verification link will expire for security reasons.')
            ->line('If you did not create an account with AIPM, you can safely ignore this email.')
            ->salutation("Regards,\nAIPM — AI Powered Marketing");
    }
}
