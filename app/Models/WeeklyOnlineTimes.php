<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyOnlineTimes extends Model
{
    use HasFactory;

    protected $table = 'weekly_online_times';

    protected $fillable = [
        'dark_users_id',
        'day',
        'minutes_online'
    ];
}
