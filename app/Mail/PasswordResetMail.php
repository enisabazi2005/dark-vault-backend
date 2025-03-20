<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetCode;

    public function __construct($resetCode)
    {
        $this->resetCode = $resetCode;
    }

    public function build()
    {
        return $this->from('enisabazi415work@gmail.com')  
                    ->subject('Your Password Reset Code')
                    ->html('Your password reset code is: 
                    <div style="font-size: 24px; font-weight: bold; color: #2d3748; background-color: #f7fafc; padding: 15px; border-radius: 8px; margin: 10px 0; text-align: center; cursor: pointer;" onclick="navigator.clipboard.writeText(\'' . $this->resetCode . '\'); alert(\'Code copied to clipboard!\');">
                    ' . $this->resetCode . '</div>
                    <div style="text-align: center; color: #718096; font-size: 14px; margin-bottom: 15px;">Click the code above to copy it to clipboard</div>
                    <div style="text-align: center; color: #e53e3e; font-size: 14px; font-style: italic; border-top: 1px solid #e2e8f0; padding-top: 15px;">⚠️ If you did not request this password reset, please ignore this email.</div>');
    }
    
}
