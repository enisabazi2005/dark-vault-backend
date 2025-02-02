<?php

namespace App\Models;

use App\Models\DarkUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePassword extends Model
{
    use HasFactory;

    protected $fillable = ['dark_users_id', 'password'];


    public function user()
    {
        return $this->belongsTo(DarkUser::class, 'dark_users_id');
    }

}
