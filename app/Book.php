<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title', 'comment', 'user_id'
    ];
    
    protected $guarded = [
        'id'
    ];
}
