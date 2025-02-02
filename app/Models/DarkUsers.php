<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DarkUsers extends Model
{
    use HasFactory, HasApiTokens , Notifiable;

    protected $table = 'dark_users';

    protected $primaryKey = 'id';
    

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

    public function getAuthIdentifierName()
    {
        return 'email'; // Or your custom column name for user authentication
    }

    public function getAuthIdentifier()
    {
        return $this->email; // Or your custom column
    }

    public function getAuthPassword()
    {
        return $this->password; // Default Laravel password field
    }

}
