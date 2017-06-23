<?php

namespace App\Listeners;

use App\Events\UserCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Library;
use App\LibraryBooks;

class LibraryDeletingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Library $library)
    {
        LibraryBooks::where([
            'library_id' => $library->id,
        ])->delete();
    }
}
