<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdModerate extends Notification
{
    use Queueable;

    public mixed $ad;
    public string $url;
    public string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($ad)
    {
        $this->ad = $ad;
        $this->url = $this->ad->url();
        $this->message = trans('Your') . ' “' . $this->ad->title() . '” ' . trans('ad is under moderation.');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line($this->message)
            ->action(trans('Show'), $this->url)
            ->line(trans('Thank you for using our application!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'type' => 'warning'
        ];
    }
}
