<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The shape every Smartsense email shares.
 *
 * Queued, so a slow mail server is never something a person waits for, and
 * rendered through the branded markdown layout, which also produces the
 * plain-text alternative. Subclasses only declare a subject, a view and its data.
 */
abstract class BrandedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject($notifiable))
            ->markdown($this->view(), $this->data($notifiable) + ['notifiable' => $notifiable]);
    }

    abstract protected function subject(object $notifiable): string;

    abstract protected function view(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function data(object $notifiable): array;
}
