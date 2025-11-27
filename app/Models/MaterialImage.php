<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialImage extends Model
{
    protected $fillable = [
        'name',
        'material_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->sort) || $model->sort == 0) {
                $maxOrder = static::where('material_id', $model->material_id)
                    ->max('sort') ?? 0;
                $model->sort = $maxOrder + 1;
            }
        });
    }

    public function material(): belongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
