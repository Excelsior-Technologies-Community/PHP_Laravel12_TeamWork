<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInviteNotification extends Notification
{
    use Queueable;

    public $invite;

    public function __construct($invite)
    {
        $this->invite = $invite;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $acceptUrl = url('/invite/accept/' . $this->invite->accept_token);
        $denyUrl   = url('/invite/deny/' . $this->invite->deny_token);

        return (new MailMessage)
            ->subject('You are invited to join a Team')
            ->greeting('Hello!')
            ->line('You have been invited to join the team: ' . $this->invite->team->name)
            ->action('Accept Invitation', $acceptUrl) // Only ONE action button
            ->line('If you do not want to join, click here to deny:')
            ->line($denyUrl) // Show deny as plain link
            ->line('Thank you!');
    }
}
