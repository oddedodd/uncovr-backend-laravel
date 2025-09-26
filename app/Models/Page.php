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
    ];

    protected $casts = [
        'blocks' => 'array', // JSON <-> array
    ];
    
    public function release()
    {
        return $this->belongsTo(Release::class);
    }

    public function getBlocksWithResolvedBackgroundAttribute(): array
    {
        $pageBg = $this->background_color;
        $blocks = $this->blocks ?? [];

        return collect($blocks)->map(function ($block) use ($pageBg) {
            // Filament Builder lagrer typisk struktur: ['type' => 'text', 'data' => [...]]
            $data = $block['data'] ?? [];

            $block['resolvedBackground'] = $data['background_color'] ?? $pageBg;

            return $block;
        })->values()->all();
    }
}