<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetCode;
    public $verifyUrl;
    public $trackingUrl;
    public $email;

    public function __construct($resetCode, $email)
    {
        $this->resetCode = $resetCode;
        $this->email = $email;
        
        // URL for verification
        $this->verifyUrl = url("/api/password-reset/verify/{$resetCode}/{$email}");
        
        // Separate tracking URL (invisible pixel)
        $this->trackingUrl = url("/api/password-reset/track/{$resetCode}/{$email}");
    }
    
    public function build()
    {
        return $this->from('enisabazi415work@gmail.com')  
                    ->subject('Your Password Reset Code')
                    ->html('
                    <div style="max-width: 600px; margin: 0 auto;">
                        <p>Your password reset code is:</p>
                        
                        <div style="font-size: 24px; font-weight: bold; color: #2d3748; background-color: #f7fafc; padding: 15px; border-radius: 8px; margin: 10px 0; text-align: center;">
                            '.$this->resetCode.'
                        </div>
                        
                        <div style="text-align: center; margin: 20px 0;">
                            <a href="'.$this->verifyUrl.'" style="color: #4299e1;">
                                Click here to verify automatically
                            </a>
                        </div>
                        
                        <p style="text-align: center; color: #718096;">
                            Or enter the code manually in the app
                        </p>
                        
                        <!-- Invisible tracking pixel -->
                        <img src="'.$this->trackingUrl.'" width="1" height="1" style="display:none">
                    </div>
                    ');
    }
}