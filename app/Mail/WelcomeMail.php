<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->from('enisabazi415work@gmail.com')
                    ->subject('Welcome to Dark Vault!')
                    ->html('
                    <div style="max-width: 600px; margin: 0 auto;">
                        <h2>Welcome, ' . $this->user->name . '!</h2>
                        <p>Thank you for registering with Dark Vault. We are thrilled to have you with us.</p>
                        <p>If you need any assistance, please don’t hesitate to contact us at <a href="mailto:enisabazi415work@gmail.com">enisabazi415work@gmail.com</a>.</p>
                        <p>Best regards,<br>Dark Vault Team</p>
                    </div>
                    ');
    }
}
