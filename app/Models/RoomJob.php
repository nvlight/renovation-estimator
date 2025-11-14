<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomJob extends Model
{
    protected $fillable = [
        'room_id',
        'title',
        'sum',
        'more_info',
    ];
}
