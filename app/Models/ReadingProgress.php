<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
use App\Models\User;

class ReadingProgress extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'bookmark',
        'highlights',
        'last_read_at',
    ];

    protected $casts = [
        'highlights' => 'array',
        'last_read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
