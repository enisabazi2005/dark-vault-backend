<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DarkUsers;

class GroupUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'dark_user_id', 'title', 'code', 'users_in_group', 'users_invited', 'users_answered', 'admin_id', 'semi_admin_id', 'group_link'
    ];

    protected $casts = [
        'users_in_group' => 'array',
        'users_invited' => 'array',
        'users_answered' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(DarkUsers::class, 'admin_id');
    }

    public function creator()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_user_id');
    }
}
