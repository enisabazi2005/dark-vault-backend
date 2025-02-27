<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dark_users_id',
        'muted',
        'muted_id',
    ];

    public function darkUser()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }

    public function mutedUser()
    {
        return $this->belongsTo(DarkUsers::class, 'muted_id');
    }
}
