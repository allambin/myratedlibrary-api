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
        
        $author = \App\Author::create([
            'name' => 'Agatha Christie'
        ]);
        
        $this->book->authors()->save($author);
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
}
