<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OtpVerification;
use Carbon\Carbon;

class RemoveExpiredVerifications extends Command
{
    protected $signature = 'verification:clear-expired';
    protected $description = 'Removes OTP verifications that are older than 30 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $expiredDate = now()->subMinutes(1); 
    
        $deleted = OtpVerification::where('is_verified', true)
            ->where('verified_at', '<', $expiredDate)
            ->delete();
    
        $this->info("$deleted expired OTP verifications have been deleted.");
    }
}
