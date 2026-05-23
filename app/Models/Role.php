<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    CONST TYPE_ADMIN = 1;
    CONST TYPE_READER = 2;

    protected $fillable = [
        'title',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
