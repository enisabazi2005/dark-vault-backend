<?php

namespace App\Models;

use App\Models\DarkUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreNotes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
        'dark_users_id',
    ];

    public function user()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }
    
}
