<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'links',
        'user_id',
    ];

    /**
     * Relasjon: en artist har én bruker (konto).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot-metode for å sikre unike slugs.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($artist) {
            if (empty($artist->slug) && !empty($artist->name)) {
                $artist->slug = static::generateUniqueSlug($artist->name);
            }
        });

        static::updating(function ($artist) {
            if (empty($artist->slug) && !empty($artist->name)) {
                $artist->slug = static::generateUniqueSlug($artist->name, $artist->id);
            }
        });
    }

    /**
     * Generer unike slugs basert på navn.
     */
    protected static function generateUniqueSlug(string $name, $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;

        $i = 1;
        while (static::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}