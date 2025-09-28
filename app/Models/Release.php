<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Release extends Model
{
    // ✅ Tillat mass-assignment av nye felter
    protected $fillable = [
        'artist_id',
        'title',
        'slug',
        'cover_image',
        'content',
        'spotify_url',
        'type',          // ← NY
        'status',        // ← NY
        'release_date',  // ← NY
        'published_at',
    ];

    // (valgfritt) defaultverdier
    protected $attributes = [
        'type'   => 'single',
        'status' => 'draft',
    ];

    protected $casts = [
        'release_date' => 'date',
        'published_at' => 'datetime',
        'meta' => 'array',
        // 'type' og 'status' er strings – ingen spesial-cast nødvendig
    ];

    // Enkle “konstanter” som kan brukes andre steder i koden
    public const TYPES = ['single', 'ep', 'album'];
    public const STATUSES = ['draft', 'published'];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class)->orderBy('position');
    }

    // (valgfritt) litt sikkerhet: normaliser og begrens verdier
    public function setTypeAttribute($value): void
    {
        $value = strtolower((string) $value);
        $this->attributes['type'] = in_array($value, self::TYPES, true) ? $value : 'single';
    }

    public function setStatusAttribute($value): void
    {
        $value = strtolower((string) $value);
        $this->attributes['status'] = in_array($value, self::STATUSES, true) ? $value : 'draft';
    }

    // (valgfritt) automatikk for published_at basert på status
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->status === 'published' && empty($model->published_at)) {
                $model->published_at = now();
            }

            if ($model->status !== 'published') {
                $model->published_at = null;
            }
        });
    }

    // (valgfritt) hjelpescope
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}