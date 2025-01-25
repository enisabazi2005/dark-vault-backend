<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class DarkUsers extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'dark_users';

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'gender',
        'birthdate',
        'age',
        'picture'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    protected $attributes = [
        'birthdate' => '1950-01-01', 
    ];

}
