<?php

namespace App\Jobs;

use App\Models\PasswordReset;
use App\Models\DarkUsers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class UpdateResetCode implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function handle()
    {
        $user = DarkUsers::where('email', $this->email)->first();
        
        if ($user) {
            $endTime = Carbon::now()->addSeconds(30);  // Set the time for the job to run for 30 seconds
            
            while (Carbon::now()->lt($endTime)) {
                $code = rand(100000, 999999);
                
                PasswordReset::updateOrCreate(
                    ['dark_users_id' => $user->id, 'email' => $this->email],
                    ['code' => $code, 'created_at' => now()]
                );

                Mail::to($user->email)->send(new PasswordResetMail($code));

                sleep(5);
            }
        }
    }
}
