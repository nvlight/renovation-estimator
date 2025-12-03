<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    public const PER_PAGE = 10;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'height',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function roomJobs(): HasMany
    {
        return $this->hasMany(RoomJob::class);
    }

    public function room_walls(): HasMany
    {
        return $this->hasMany(RoomWall::class, 'room_id');
    }

    public function room_materials(): HasMany{
        return $this->hasMany(RoomMaterial::class, 'room_id');
    }
}
