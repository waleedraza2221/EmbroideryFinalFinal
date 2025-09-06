<?php

namespace App\Mail;

use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirm extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscription $subscription){}

    public function build()
    {
        return $this->subject('Confirm your subscription')
            ->view('emails.newsletter.confirm');
    }
}