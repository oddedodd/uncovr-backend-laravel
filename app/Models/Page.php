<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'blocks' => 'array',          // JSON <-> array
        'title'  => 'string',
        'slug'   => 'string',
        'background_color' => 'string',
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    /**
     * Hvis du senere vil sortere sider, kan du bytte til en 'position' kolonne
     * og oppdatere scope'et under. For nå lar vi den bare sortere på id.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Hjelpe-attributt: hver blokk får en 'resolvedBackground' der blokkas egen
     * farge (om satt) vinner, ellers faller den tilbake til sidens background_color.
     */
    public function getBlocksWithResolvedBackgroundAttribute(): array
    {
        $pageBg = $this->background_color;
        $blocks = $this->blocks ?? [];

        return collect($blocks)->map(function ($block) use ($pageBg) {
            // Builder-form: ['type' => '...', 'data' => [...]]
            $data = $block['data'] ?? [];
            $block['resolvedBackground'] = $data['background_color'] ?? $pageBg;
            return $block;
        })->values()->all();
    }
}