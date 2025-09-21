<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'release_id',
        'title',
        'slug',
        'page_type',
        'position',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }
}