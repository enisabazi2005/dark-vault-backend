<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'reciever_id',
        'message',
        'dark_users_id',
        'order',
        'message_sent_at',
    ];
}
