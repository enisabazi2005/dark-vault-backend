<?php

namespace App\Models;

use App\Models\DarkUsers;
use Filament\Panel\Concerns\HasFont;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FriendRequests extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'dark_user_id',
        'request_friend_id',
        'is_accepted',
        'friend',
        'rejection',
        'pending',
    ];

    protected $casts = [
        'friend' => 'array',
        'rejection' => 'array',
        'pending' => 'array',
    ];

    
    public function user()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }

    public function requestFriend()
    {
        return $this->belongsTo(DarkUsers::class, 'request_friend_id', 'request_id');
    }
    
}
