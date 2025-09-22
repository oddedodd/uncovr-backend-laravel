<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    // Allow Filament to mass-assign these:
    protected $fillable = [
        'release_id',
        'title',
        'slug',
        'cover_image',
        'content',
    ];

    // (optional) if you prefer the “allow everything” approach:
    // protected $guarded = [];
    
    public function release()
    {
        return $this->belongsTo(Release::class);
    }
}