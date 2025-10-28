<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TempPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $temporaryPassword;
    public string $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $temporaryPassword, string $userName)
    {
        $this->temporaryPassword = $temporaryPassword;
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        return (new MailMessage)
                        ->subject('Your Account Has Been Created - WinIt Prize Distribution')
                        ->view('emails.anti-spam-user-creation', [ // Using the anti-spam template
                            'user' => $notifiable,
                            'temporaryPassword' => $this->temporaryPassword,
                            'userName' => $this->userName,
                            'loginUrl' => url('/login'),
                            'organizationName' => config('app.name', 'WinIt Prize Distribution'),
                        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'temporary_password' => $this->temporaryPassword,
            'user_name' => $this->userName,
            'email' => $notifiable->email,
        ];
    }
}
