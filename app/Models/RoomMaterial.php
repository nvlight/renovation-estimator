<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomMaterial extends Model
{
    /** @use HasFactory<\Database\Factories\RoomMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'material_id',
        'amount',
        'sum',
        'notice',
    ];

    public function room(): BelongsTo{
        return $this->belongsTo(Room::class);
    }

    public function material(): BelongsTo{
        return $this->belongsTo(Material::class);
    }
}
