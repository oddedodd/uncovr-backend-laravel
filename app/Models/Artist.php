<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'links',
    ];

    // Relasjoner
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function releases()
    {
        return $this->hasMany(Release::class);
    }
}