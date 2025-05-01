<?php

namespace App\Models;

use App\Models\FriendRequests;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class DarkUsers extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

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
        'picture',
        'request_id',
        'online',
        'offline',
        'away',
        'do_not_disturb',
        'last_active_at',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->request_id = strtoupper(Str::random(8)); // Generates a random 8-character string
        });
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequests::class, 'dark_user_id', 'id');
    }

    protected static function booted()
{
    static::updated(function ($user) {
        \Log::info('User status updated', [
            'id' => $user->id,
            'online' => $user->online,
            'offline' => $user->offline,
            'away' => $user->away,
            'dnd' => $user->do_not_disturb,
        ]);
    });
}

    
}
