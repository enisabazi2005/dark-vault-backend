<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageReactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'message_id',
        'reacted_by',
        'reaction_type',
    ];
}
