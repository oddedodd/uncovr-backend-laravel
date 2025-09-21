<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Release extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'slug',
        'type',
        'status',
        'release_date',
        'published_at',
        'meta',
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

    public function pages()
    {
        return $this->hasMany(Page::class)->orderBy('position');
    }
}