<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FirebaseActionLinkNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $url,
        private readonly string $subject,
        private readonly string $message,
        private readonly string $button,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->line($this->message)
            ->action($this->button, $this->url);
    }
}
