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

    /**
     * Relationship with Author
     * @return type
     */
    public function authors()
    {
        return $this->belongsToMany('App\Author', 'author_books');
    }
    
    /**
     * Format the object for Json response
     * @return $this
     */
    public function formatJson()
    {
        $authors = [];
        foreach($this->authors as $author) {
            $authors[] = ['name' => $author->name];
        }
        $this->authors = $authors;
        return $this;
    }
}
