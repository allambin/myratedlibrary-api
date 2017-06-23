<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LibraryBooksTest extends TestCase
{
    use DatabaseMigrations;
    
    protected $user;
    protected $token;
    protected $book;
    protected $library;
    
    public function setUp()
    {
        parent::setUp();
        \App\User::unguard();
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
            'user_id' => $this->user->id
        ]);
        
        $this->library = \App\Library::create([
            'name' => 'Hercule Poirot',
            'user_id' => $this->user->id
        ]);
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testShouldAddBookToLibrary()
    {
        $response = $this->put(route('api.v1.libraries.add-book', [
            'library_id' => $this->library->id,
        ]), [
            'auth_token' => $this->token,
            'book_id' => $this->book->id
        ]);
        
        $response->assertStatus(204);
    }
    
    public function testShouldFailToAddBookToLibratyWithEmptyBookId()
    {
        $response = $this->put(route('api.v1.libraries.add-book', [
            'library_id' => $this->library->id,
        ]), [
            'auth_token' => $this->token,
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed.',
            'errors' => [
                'book_id' => [
                    'The book id field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailToAddBookToLibratyWithNotFoundLibrary()
    {
        $response = $this->put(route('api.v1.libraries.add-book', [
            'library_id' => 99,
        ]), [
            'auth_token' => $this->token,
            'book_id' => $this->book->id
        ]);
        
        $response->assertExactJson([
            'code' => 404,
            'message' => 'The resource was not found.',
            'errors' => [
                'library' => 'Not found.'
            ]
        ]);
    }
    
    public function testShouldFailToAddBookToLibratyWithNotFoundBook()
    {
        $response = $this->put(route('api.v1.libraries.add-book', [
            'library_id' => $this->library->id,
        ]), [
            'auth_token' => $this->token,
            'book_id' => 99
        ]);
        
        $response->assertExactJson([
            'code' => 404,
            'message' => 'The resource was not found.',
            'errors' => [
                'book' => 'Not found.'
            ]
        ]);
    }
    
    public function testShouldFailToAddBookToOtherUserLibrary()
    {
        $user = \App\User::create([
            'email' => 'test2@email.com',
            'password' => 'password'
        ]);
        
        $library = \App\Library::create([
            'name' => 'Other user library',
            'user_id' => $user->id
        ]);
        
        $response = $this->put(route('api.v1.libraries.add-book', [
            'library_id' => $library->id,
        ]), [
            'auth_token' => $this->token,
            'book_id' => $this->book->id
        ]);
        
        $response->assertExactJson([
            'code' => 401,
            'message' => 'You are not authorized to perform this action.',
            'errors' => [
                'library' => 'Unauthorized action.'
            ]
        ]);
    }
    
    public function testShouldFailToAddBookToOtherUserBook()
    {
        $user = \App\User::create([
            'email' => 'test2@email.com',
            'password' => 'password'
        ]);
        
        $book = \App\Book::create([
            'title' => 'Other user book',
            'user_id' => $user->id
        ]);
        
        $response = $this->put(route('api.v1.libraries.add-book', [
            'library_id' => $this->library->id,
        ]), [
            'auth_token' => $this->token,
            'book_id' => $book->id
        ]);
        
        $response->assertExactJson([
            'code' => 401,
            'message' => 'You are not authorized to perform this action.',
            'errors' => [
                'book' => 'Unauthorized action.'
            ]
        ]);
    }
}
