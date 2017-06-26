<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name'
    ];
    
    protected $visible = [
        'name', 'rating'
    ];
    
    /**
     * Relationship with Book
     * @return type
     */
    public function books()
    {
        return $this->belongsToMany('App\Book', 'author_books');
    }
}
