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
        'published_at',
    ];

    protected $casts = [
        'blocks' => 'array',
        'meta'   => 'array',
        'published_at' => 'datetime',
    ];

    // (valgfritt) defaultverdier
    protected $attributes = [
        'status' => 'draft',
        'page_type' => 'generic',
    ];

    // Enkle "konstanter" som kan brukes andre steder i koden
    public const STATUSES = ['draft', 'published'];

    public function release()
    {
        return $this->belongsTo(Release::class);
    }

    // Standard sortering på position
    protected $table = 'pages';

    protected static function booted(): void
    {
        // Sett position automatisk hvis tom - per release
        static::creating(function (self $page) {
            if (empty($page->position)) {
                $page->position = (int) static::where('release_id', $page->release_id)->max('position') + 1;
            }
        });

        // (valgfritt) automatikk for published_at basert på status
        static::saving(function (self $model) {
            if ($model->status === 'published' && empty($model->published_at)) {
                $model->published_at = now();
            }

            if ($model->status !== 'published') {
                $model->published_at = null;
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // Kun publiserte sider
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
        // Evt. også tid:
        // ->whereNotNull('published_at')->where('published_at', '<=', now());
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