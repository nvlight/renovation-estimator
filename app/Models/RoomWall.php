<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomWall extends Model
{
    /** @use HasFactory<\Database\Factories\RoomWallFactory> */
    use HasFactory;

    public const PER_PAGE = 7;

    protected $fillable = [
        'room_id',
        'length',
        'angle',
        'order',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
