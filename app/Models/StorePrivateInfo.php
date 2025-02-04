<?php

namespace App\Models;

use App\Models\DarkUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePrivateInfo extends Model
{
    use HasFactory;

    protected $table = 'private_info';


    protected $fillable = [
        'dark_users_id',
        'name',
        'info_1',
        'info_2',
        'info_3',
    ];

    public function user()
    {
        return $this->belongsTo(DarkUsers::class, 'dark_users_id');
    }

}
