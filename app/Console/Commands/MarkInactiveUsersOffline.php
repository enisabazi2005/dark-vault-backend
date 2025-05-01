<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DarkUsers;

class MarkInactiveUsersOffline extends Command
{
    protected $signature = 'app:mark-inactive-users-offline';

    protected $description = 'Command description';

    public function handle()
    {
        DarkUsers::where('last_active_at', '<' , now()->subMinutes(5))
            ->where('online', true)
            ->update([
                'online' => false,
                'offline' => true,
            ]);
    }
}
