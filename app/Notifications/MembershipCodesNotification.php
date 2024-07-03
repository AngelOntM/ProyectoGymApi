<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipCodesNotification extends Notification
{
    use Queueable;

    protected $membershipCodes;

    /**
     * Create a new notification instance.
     *
     * @param array $membershipCodes Array of MembershipCode objects
     */
    public function __construct(array $membershipCodes)
    {
        $this->membershipCodes = $membershipCodes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('Códigos de Membresía Generados')
            ->line('Se han generado nuevos códigos de membresía para tu cuenta.');

        foreach ($this->membershipCodes as $membershipCode) {
            $mailMessage->line('Código de Membresía: ' . $membershipCode);
        }

        $mailMessage->line('Utiliza estos códigos para acceder a tu membresía.')
                    ->line('Gracias por tu compra.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'membership_codes' => $this->membershipCodes,
        ];
    }
}
