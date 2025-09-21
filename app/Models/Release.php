<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Release extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'type',
        'release_date',
        'slug',
        'meta',
        'status',
        'published_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }
}