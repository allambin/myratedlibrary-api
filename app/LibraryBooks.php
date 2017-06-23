<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LibraryBooks extends Model
{
    protected $fillable = [
        'library_id', 'book_id'
    ];
}
