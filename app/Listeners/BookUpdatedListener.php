<?php

namespace App\Listeners;

use App\Events\UserCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Book;
use DB;

class BookUpdatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Book $book)
    {
        $columns = $book->getDirty();
        if(in_array('rating', array_keys($columns))){
            foreach($book->authors as $author) {
                DB::update("UPDATE authors SET rating = ("
                        . "SELECT AVG(rating) FROM books b "
                        . "INNER JOIN author_books ab ON ab.book_id = b.id "
                        . "WHERE ab.author_id = ? AND b.user_id = ?"
                        . ") WHERE id = ?", [$author->id, $book->user_id, $author->id]);
            }
        }
    }
}
