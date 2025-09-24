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
}