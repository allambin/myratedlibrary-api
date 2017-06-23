<?php

namespace App\Listeners;

use App\Events\UserCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Book;
use App\LibraryBooks;
use App\AuthorBooks;

class BookDeletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Book $book)
    {
        LibraryBooks::where([
            'book_id' => $book->id,
        ])->delete();
        AuthorBooks::where([
            'book_id' => $book->id,
        ])->delete();
    }
}
