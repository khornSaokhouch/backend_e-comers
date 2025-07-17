<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewSellerRequest extends Notification
{
    use Queueable;

    protected $seller;

    public function __construct($seller)
    {
        $this->seller = $seller;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];  // Send email + save in DB
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Seller Request')
                    ->line('A new seller request has been submitted.')
                    ->action('View Seller', url('/admin/sellers/' . $this->seller->id))
                    ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'seller_id' => $this->seller->id,
            'user_id' => $this->seller->user_id,
            'name' => $this->seller->name,
            'company_name' => $this->seller->company_name,
            'email' => $this->seller->email,
            'message' => 'A new seller request has been submitted.',
        ];
    }
}
