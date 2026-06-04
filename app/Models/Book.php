<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\User;

class Book extends Model implements HasMedia
{
    use InteractsWithMedia;

    CONST STATUS_ENABLE = 1;
    CONST STATUS_DISABLE = 2;

    protected $fillable = [
        'title',
        'author',
        'description',
        'total_pages',
        'added_by',
        'status',
    ];

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover_image') ?: null;
    }

    public function getBookUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('book_url') ?: null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by', 'id');
    }
}
