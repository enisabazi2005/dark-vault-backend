<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomBackground extends Model
{
    use HasFactory;

    protected $table = 'custom_background';
    protected $fillable = [
        'dark_users_id',
        'color_1',
        'color_2',
    ];

    public function user()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }
}
