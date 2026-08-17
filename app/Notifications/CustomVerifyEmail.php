<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your email address — AI Powered Marketing')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Welcome to AI Powered Marketing.')
            ->line('To complete your account setup and secure your account, please verify your email address by clicking the button below.')
            ->action('Verify My Email Address', $this->verificationUrl($notifiable))
            ->line('This verification link will expire for security reasons.')
            ->line('If you did not create an account with AI Powered Marketing, you can safely ignore this email.')
            ->salutation("Regards,\nThe AI Powered Marketing Team");
    }
}
