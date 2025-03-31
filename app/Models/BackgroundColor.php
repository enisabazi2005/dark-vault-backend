<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DarkUsers;

class BackgroundColor extends Model
{
    use HasFactory;

    protected $fillable = ['dark_users_id', 'option', 'changed_at'];

    public function user()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }
}
