<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    protected $fillable = [
        'name', 'user_id'
    ];
    
    protected $guarded = [
        'id'
    ];
    
    protected $casts = [
        'id' => 'integer',
        'is_public' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Relationship with Book
     * @return type
     */
    public function books()
    {
        return $this->belongsToMany('App\Book', 'library_books');
    }
}
