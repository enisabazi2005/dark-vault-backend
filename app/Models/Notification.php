<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'dark_user_id',
        'message',
        'is_read',
        'message_id',
    ];

    public function message()
    {
    return $this->belongsTo(Message::class);
    }
}
    