<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DarkUsers;
use App\Models\GroupUser;

class GroupAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'accepted',
    ];


    public function group()
    {
        return $this->belongsTo(GroupUser::class, 'group_id');
    }

    /**
     * Get the user who answered the invitation.
     */
    public function user()
    {
        return $this->belongsTo(DarkUsers::class , 'user_id');
    }
}
