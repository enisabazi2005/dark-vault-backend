<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $owner;
    public $semiAdmin;
    public $suspect;
    public $messageText;
    public $fromEmail;
    public $reason;


    public function __construct($owner, $semiAdmin, $suspect, $messageText, $fromEmail, $reason)
    {
        $this->owner = $owner;
        $this->semiAdmin = $semiAdmin;
        $this->suspect = $suspect;
        $this->messageText = $messageText;
        $this->fromEmail = $fromEmail;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->from($this->fromEmail)
            ->subject('Group Message Report Alert')
            ->html("
    <div style='max-width: 600px; margin: 0 auto;'>
        <h2>Dear {$this->owner->name},</h2>
        <p>I, <strong>{$this->semiAdmin->name} {$this->semiAdmin->lastname}</strong>, a semi-admin of your group, am reporting a message sent by:</p>
        <ul>
            <li><strong>Name:</strong> {$this->suspect->name} {$this->suspect->lastname}</li>
            <li><strong>Email:</strong> {$this->suspect->email}</li>
            <li><strong>Age:</strong> {$this->suspect->age}</li>
        </ul>
        <p><strong>Reason for report:</strong> {$this->reason}</p>
        <p><strong>Message Content:</strong> \"{$this->messageText}\"</p>
        <p>Please review this message and take appropriate action (delete message or remove user from the group).</p>
        <p>Best regards,<br>Dark Vault System</p>
    </div>
");
    }
}
