<?php

namespace App\Models;

use App\Models\DarkUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedUsers extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    public function blockedUser()
    {
        return $this->belongsTo(DarkUsers::class, 'blocked_id');
    }
}
