<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Label extends Model
{
    protected $fillable = ['name', 'slug', 'owner_user_id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    /**
     * Praktiske read-only felt (tilgjengelig som $label->owner_name / owner_email).
     * NB: Disse endrer ikke relasjonen; de er kun "gettere".
     */
    public function getOwnerNameAttribute(): ?string
    {
        return $this->owner?->name;
    }

    public function getOwnerEmailAttribute(): ?string
    {
        return $this->owner?->email;
    }

    /**
     * Boot-metode for å generere unike slugs automatisk.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($label) {
            if (empty($label->slug) && ! empty($label->name)) {
                $label->slug = static::generateUniqueSlug($label->name);
            }
        });

        static::updating(function ($label) {
            if (empty($label->slug) && ! empty($label->name)) {
                $label->slug = static::generateUniqueSlug($label->name, $label->id);
            }
        });
    }

    /**
     * Generer unik slug.
     */
    protected static function generateUniqueSlug(string $name, $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;

        $i = 1;
        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}