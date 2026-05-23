<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'description',
        'cover_image',
        'book_url',
        'total_pages',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
