<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BookRatingTest extends TestCase
{
    use DatabaseMigrations;
    
    private $user;
    private $token;
    private $book;
    private $author;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->user = \App\User::create([
            'email' => 'test@email.com',
            'password' => 'password'
        ]);
        
        $this->token = sha1($this->user->email . time());
        \App\AuthToken::create([
            'token' => $this->token,
            'user_id' => $this->user->id
        ]);
        
        $this->book = \App\Book::create([
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one',
            'user_id' => $this->user->id
        ]);
        
        $this->author = \App\Author::create([
            'name' => 'Agatha Christie'
        ]);
        
        $this->book->authors()->save($this->author);
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testShouldRateBook()
    {
        $response = $this->put(route('api.v1.books.rate', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
            'rating' => 4.5,
        ]);
        
        $response->assertJson([
            'title' => 'Five Little Pigs',
            'authors' => [
                ['name' => 'Agatha Christie']
            ],
            'comment' => 'Nice one',
            'rating' => 4.5
        ]);
    }
    
    public function testShouldFailRateBookWithMissingRating()
    {
        $response = $this->put(route('api.v1.books.rate', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed.',
            'errors' => [
                'rating' => [
                    'The rating field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailRateBookWithRatingGreaterThan5()
    {
        $response = $this->put(route('api.v1.books.rate', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
            'rating' => 10
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed.',
            'errors' => [
                'rating' => [
                    'The rating must be between 0 and 5.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailRateBookWithRatingNotNumeric()
    {
        $response = $this->put(route('api.v1.books.rate', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
            'rating' => 'abc'
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed.',
            'errors' => [
                'rating' => [
                    'The rating must be a number.'
                ]
            ]
        ]);
    }
    
    public function testShouldRateAuthorWhileRatingBooks()
    {        
        $book1 = \App\Book::create([
            'title' => 'Murder on the Orient Express',
            'comment' => 'Nice one',
            'user_id' => $this->user->id,
        ]);
        $book2 = \App\Book::create([
            'title' => 'Lord Edwagre dies...',
            'comment' => 'Nice one',
            'user_id' => $this->user->id,
        ]);
        
        $book1->authors()->save($this->author);
        $book2->authors()->save($this->author);
        
        $this->put(route('api.v1.books.rate', ['id' => $book1->id]), [
            'auth_token' => $this->token,
            'rating' => 3.5,
        ]);
        
        $this->put(route('api.v1.books.rate', ['id' => $book2->id]), [
            'auth_token' => $this->token,
            'rating' => 2,
        ]);
        
        $response = $this->put(route('api.v1.books.rate', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
            'rating' => 4.5,
        ]);
        
        $response->assertJson([
            'title' => 'Five Little Pigs',
            'authors' => [
                ['name' => 'Agatha Christie', 'rating' => 3.33333333333333]
            ],
            'comment' => 'Nice one',
            'rating' => 4.5
        ]);
    }
    
    public function testShouldUpdateAuthorRatingWhenDeletingBook()
    {        
        $book1 = \App\Book::create([
            'title' => 'Murder on the Orient Express',
            'comment' => 'Nice one',
            'user_id' => $this->user->id,
        ]);
        $book2 = \App\Book::create([
            'title' => 'Lord Edwagre dies...',
            'comment' => 'Nice one',
            'user_id' => $this->user->id,
        ]);
        
        $book1->authors()->save($this->author);
        $book2->authors()->save($this->author);
        
        $this->put(route('api.v1.books.rate', ['id' => $book1->id]), [
            'auth_token' => $this->token,
            'rating' => 3.5,
        ]);
        
        $this->put(route('api.v1.books.rate', ['id' => $book2->id]), [
            'auth_token' => $this->token,
            'rating' => 2,
        ]);
        
        $response = $this->put(route('api.v1.books.rate', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
            'rating' => 4.5,
        ]);
        
        $response->assertJson([
            'title' => 'Five Little Pigs',
            'authors' => [
                ['name' => 'Agatha Christie', 'rating' => 3.33333333333333]
            ],
            'comment' => 'Nice one',
            'rating' => 4.5
        ]);
        
        $this->delete(route('api.v1.books.destroy', ['id' => $this->book->id]), [
            'auth_token' => $this->token,
        ]);
        
        $response = $this->put(route('api.v1.books.rate', ['id' => $book2->id]), [
            'auth_token' => $this->token,
            'rating' => 2,
        ]);
        
        $response->assertJson([
            'title' => 'Lord Edwagre dies...',
            'authors' => [
                ['name' => 'Agatha Christie', 'rating' => 2.75]
            ],
            'comment' => 'Nice one',
            'rating' => 2
        ]);
    }
}
