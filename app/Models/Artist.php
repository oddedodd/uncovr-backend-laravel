<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'label_id',
    ];

    /**
     * Relasjoner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    public function releases(): HasMany
    {
        return $this->hasMany(Release::class);
    }

    /**
     * Boot: generer unik slug og sett label_id automatisk for label-brukere.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Artist $artist) {
            // Slug (om tom)
            if (empty($artist->slug) && !empty($artist->name)) {
                $artist->slug = static::generateUniqueSlug($artist->name);
            }

            // Hvis innlogget bruker har rollen "label" og label_id ikke er satt,
            // koble automatisk til labelen som eies av brukeren.
            if (empty($artist->label_id) && auth()->check() && auth()->user()->hasRole('label')) {
                $label = Label::query()
                    ->where('owner_user_id', auth()->id())
                    ->first();

                if ($label) {
                    $artist->label_id = $label->id;
                }
            }
        });

        static::updating(function (Artist $artist) {
            if (empty($artist->slug) && !empty($artist->name)) {
                $artist->slug = static::generateUniqueSlug($artist->name, $artist->id);
            }
        });
    }

    /**
     * Generer unik slug.
     */
    protected static function generateUniqueSlug(string $name, $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}