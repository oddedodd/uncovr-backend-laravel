<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedRelease extends Model
{
    protected $fillable = [
        'release_id',
        'display_order',
    ];

    protected $casts = [
        'featured_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->featured_at)) {
                $model->featured_at = now();
            }
        });
    }

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
}
