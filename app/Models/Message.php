<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'sender_id',
        'reciever_id',
        'message',
        'dark_users_id',
        'order',
        'message_sent_at',
        'seen_at',
    ];

    public function reactions()
    {
        return $this->hasMany(MessageReactions::class, 'message_id');
    }

}
