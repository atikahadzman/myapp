<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
