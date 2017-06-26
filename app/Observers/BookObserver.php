<?php

namespace App\Observers;

use DB;
use App\Book;
use App\LibraryBooks;
use App\AuthorBooks;

class BookObserver
{
    public function deleted(Book $book)
    {
        LibraryBooks::where([
            'book_id' => $book->id,
        ])->delete();
        AuthorBooks::where([
            'book_id' => $book->id,
        ])->delete();
        
        $this->handleAuthorRating($book);
    }
    
    public function updated(Book $book)
    {
        $columns = $book->getDirty();
        if(in_array('rating', array_keys($columns))){
            $this->handleAuthorRating($book);
        }
    }
    
    protected function handleAuthorRating(Book $book)
    {
        foreach($book->authors as $author) {
            DB::update("UPDATE authors SET rating = ("
                    . "SELECT AVG(rating) FROM books b "
                    . "INNER JOIN author_books ab ON ab.book_id = b.id "
                    . "WHERE ab.author_id = ? AND b.user_id = ?"
                    . ") WHERE id = ?", [$author->id, $book->user_id, $author->id]);
        }
    }
}
