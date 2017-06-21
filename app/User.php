<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Listeners\UserCreatingListener;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password_hash',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password_hash', 'remember_token',
    ];
    
    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $events = [
        'creating' => UserCreatingListener::class
    ];
    
    /**
     * Check if this user can edit this book
     * @param \App\Book $book
     * @return boolean
     */
    public function canEditBook(Book $book)
    {
        return $book->user_id == $this->id;
    }
}
