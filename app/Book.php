<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Listeners\BookDeletedListener;
use App\Listeners\BookUpdatedListener;

class Book extends Model
{
    protected $fillable = [
        'title', 'comment', 'user_id'
    ];
    
    protected $guarded = [
        'id'
    ];
    
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];
    
    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $events = [
        'deleted' => BookDeletedListener::class,
        'updated' => BookUpdatedListener::class
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
            $authors[] = [
                'name' => $author->name,
                'rating' => $author->rating,
            ];
        }
        $this->authors = $authors;
        return $this;
    }
}
