<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    use HasFactory;

    protected $table = 'group_message';

    protected $fillable = [
        'group_id', 'sent_by', 'message', 'sent_at'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(GroupUser::class , 'group_id');
    }

    public function sender()
    {
        return $this->belongsTo(DarkUsers::class, 'sent_by');
    }
}
