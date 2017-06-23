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

    /**
     * Relationship with Library
     * @return type
     */
    public function libraries()
    {
        return $this->belongsToMany('App\Library', 'library_books');
    }
}
