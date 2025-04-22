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
        'is_seen',
    ];

    public function reactions()
    {
        return $this->hasMany(MessageReactions::class, 'message_id');
    }
    public function senderUser()
    {
        return $this->belongsTo(DarkUsers::class, 'sender_id', 'request_id');
    }

}
