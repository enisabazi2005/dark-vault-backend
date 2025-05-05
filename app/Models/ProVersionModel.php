<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DarkUsers;

class ProVersionModel extends Model
{
    use HasFactory;

    protected $table = 'pro_versions';
    
    protected $fillable = [
        'dark_users_id',
        'activated_at',
        'expires_at',
        'is_active',
    ];

    public function darkUser()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }

}
