<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Book;
use App\Models\User;

class Rates extends Model
{
    protected $fillable = [
        'rating',
        'review',
        'added_by',
        'book_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by', 'id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
