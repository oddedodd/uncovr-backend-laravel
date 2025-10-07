<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'release_id',
        'title',
        'slug',
        'background_color',
        'blocks',
        'position',      // 👈 pass på at position er fillable
        'status',
        'page_type',
        'meta',
    ];

    protected $casts = [
        'blocks' => 'array',
        'meta'   => 'array',
    ];

    public function release()
    {
        return $this->belongsTo(Release::class);
    }

    // Standard sortering på position
    protected $table = 'pages';

    protected static function booted(): void
    {
        // Sett position automatisk hvis tom
        static::creating(function (self $page) {
            if (empty($page->position)) {
                $page->position = (int) static::where('release_id', $page->release_id)->max('position') + 1;
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function getBlocksWithResolvedBackgroundAttribute(): array
    {
        $pageBg = $this->background_color;
        $blocks = $this->blocks ?? [];

        return collect($blocks)->map(function ($block) use ($pageBg) {
            $data = $block['data'] ?? [];
            $block['resolvedBackground'] = $data['background_color'] ?? $pageBg;
            return $block;
        })->values()->all();
    }
}