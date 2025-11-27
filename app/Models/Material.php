<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    /** @use HasFactory<\Database\Factories\MaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'product_code',
        'price',
        'is_free',
        'characteristics',
        'advantages',
        'packaging_info',
        'brand',
        'producing_country',
    ];

    protected $casts = [
        'characteristics' => 'array',
        'advantages' => 'array',
        'packaging_info' => 'array',
        'price' => 'decimal:2',
        'is_free' => 'boolean',
    ];

    public function images(): HasMany{
        return $this->hasMany(MaterialImage::class)->orderBy('sort');
    }
}
