<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomJob extends Model
{
    protected $fillable = [
        'room_id',
        'title',
        'sum',
        'more_info',
    ];

    protected $casts = [
        'more_info' => 'array', // автоматически сериализует/десериализует
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
