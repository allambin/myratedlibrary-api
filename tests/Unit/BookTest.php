<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BookTest extends TestCase
{
    use DatabaseMigrations;
    
    private $user;
    private $token;
    
    public function setUp()
    {
        parent::setUp();
        \App\User::unguard();
        $this->user = \App\User::create([
            'id' => 3,
            'email' => 'test@email.com',
            'password' => 'password'
        ]);
        $this->token = sha1($this->user->email . time());
        
        \App\AuthToken::create([
            'token' => $this->token,
            'user_id' => $this->user->id
        ]);
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testShouldFailMissingAuthToken()
    {
        $response = $this->post(route('api.v1.books.store'), [
        ]);
        
        $this->assertEquals(401, $response->status());
        $response->assertExactJson([
            'code' => 401,
            'message' => 'Validation failed.',
            'errors' => [
                'auth_token' => [
                    'The auth token field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldCreateBook()
    {
        \App\Book::reguard();
        $response = $this->post(route('api.v1.books.store'), [
            'auth_token' => $this->token,
            'title' => 'Five Little Pigs',
            'authors' => 'Agatha Christie',
            'comment' => 'Nice one'
        ]);
        
        $this->assertEquals(200, $response->status());
        $response->assertJson([
            'title' => 'Five Little Pigs',
            'authors' => [
                ['name' => 'Agatha Christie']
            ],
            'comment' => 'Nice one'
        ]);
        $this->assertEquals(1, \App\Book::count());
    }
    
    public function testShouldCreateBookWithMultipleAuthors()
    {
        \App\Book::reguard();
        $response = $this->post(route('api.v1.books.store'), [
            'auth_token' => $this->token,
            'title' => 'Five Little Pigs',
            'authors' => ['Agatha Christie', 'Miss Marple'],
            'comment' => 'Nice one'
        ]);
        
        $this->assertEquals(200, $response->status());
        $response->assertJson([
            'title' => 'Five Little Pigs',
            'authors' => [
                ['name' => 'Agatha Christie'],
                ['name' => 'Miss Marple'],
            ],
            'comment' => 'Nice one'
        ]);
        $this->assertEquals(1, \App\Book::count());
    }
    
    public function testShouldFailCreateBookWithEmptyTitle()
    {        
        \App\Book::reguard();
        $response = $this->post(route('api.v1.books.store'), [
            'auth_token' => $this->token,
            'authors' => ['Agatha Christie'],
            'comment' => 'Nice one'
        ]);
        
        $this->assertEquals(400, $response->status());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed.',
            'errors' => [
                'title' => [
                    'The title field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailCreateBookWithEmptyAuthor()
    {        
        \App\Book::reguard();
        $response = $this->post(route('api.v1.books.store'), [
            'auth_token' => $this->token,
            'title' => 'Hello World',
            'comment' => 'Nice one'
        ]);
        
        $this->assertEquals(400, $response->status());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed.',
            'errors' => [
                'authors' => [
                    'The authors field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldUpdateBook()
    {
        $book = \App\Book::create([
            'id' => 30,
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one',
            'user_id' => $this->user->id
        ]);
        $book->authors()->save(\App\Author::create([
            'name' => 'Agatha Christie'
        ]));
        
        \App\Book::reguard();
        $response = $this->put(route('api.v1.books.update', ['id' => $book->id]), [
            'auth_token' => $this->token,
            'title' => 'Five Little Pigs - updated',
            'authors' => 'Miss Marple',
            'comment' => 'Nice one - updated'
        ]);
        
        $response->assertJsonFragment([
            'id' => 30,
            'title' => 'Five Little Pigs - updated',
            'authors' => [
                ['name' => 'Miss Marple']
            ],
            'comment' => 'Nice one - updated'
        ]);
        $this->assertEquals(2, \App\Author::count());
    }
    
    public function testShouldFailUpdateOtherUserBook()
    {
        \App\User::unguard();
        $user2 = \App\User::create([
            'id' => 4,
            'email' => 'test2@email.com',
            'password' => 'password'
        ]);
        
        $book = \App\Book::create([
            'id' => 30,
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one',
            'user_id' => $user2->id
        ]);
        
        \App\Book::reguard();
        $response = $this->put(route('api.v1.books.update', ['id' => $book->id]), [
            'auth_token' => $this->token,
            'title' => 'Five Little Pigs - updated',
            'comment' => 'Nice one - updated'
        ]);
        
        $response->assertExactJson([
            'code' => 401,
            'message' => 'You are not authorized to perform this action.',
            'errors' => [
                'book' => 'Unauthorized action.'
            ]
        ]);
    }
    
    public function testShouldFailUpdateWithUnknownBook()
    {
        \App\Book::reguard();
        $response = $this->put(route('api.v1.books.update', ['id' => 99]), [
            'auth_token' => $this->token,
            'title' => 'Five Little Pigs - updated',
            'comment' => 'Nice one - updated'
        ]);
        
        $response->assertExactJson([
            'code' => 404,
            'message' => 'The resource was not found.',
            'errors' => [
                'book' => 'Not found.'
            ]
        ]);
    }
    
    public function testShouldDeleteBook()
    {
        $book = \App\Book::create([
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one',
            'user_id' => $this->user->id
        ]);
        $book->authors()->save(\App\Author::create([
            'name' => 'Agatha Christie'
        ]));
        $book->libraries()->save(\App\Library::create([
            'name' => 'Hercule Poirot',
            'user_id' => $this->user->id
        ]));
        $this->assertEquals(1, \App\Book::count());
        $this->assertEquals(1, \App\Author::count());
        $this->assertEquals(1, \App\Library::count());
        $this->assertEquals(1, \App\LibraryBooks::count());
        $this->assertEquals(1, \App\AuthorBooks::count());
        
        $response = $this->delete(route('api.v1.books.destroy', ['id' => $book->id]), [
            'auth_token' => $this->token,
        ]);
        
        $this->assertEquals(0, \App\Book::count());
        $this->assertEquals(1, \App\Author::count());
        $this->assertEquals(1, \App\Library::count());
        $this->assertEquals(0, \App\LibraryBooks::count());
        $this->assertEquals(0, \App\AuthorBooks::count());
    }
    
    public function testShouldFailDeleteOtherUserBook()
    {
        \App\User::unguard();
        $user2 = \App\User::create([
            'id' => 4,
            'email' => 'test2@email.com',
            'password' => 'password'
        ]);
        
        $book = \App\Book::create([
            'id' => 30,
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one',
            'user_id' => $user2->id
        ]);
        
        $response = $this->delete(route('api.v1.books.destroy', ['id' => $book->id]), [
            'auth_token' => $this->token,
        ]);
        
        $response->assertExactJson([
            'code' => 401,
            'message' => 'You are not authorized to perform this action.',
            'errors' => [
                'book' => 'Unauthorized action.'
            ]
        ]);
    }
    
    public function testShouldFailDeleteWithUnknownBook()
    {
        \App\Book::reguard();
        $response = $this->delete(route('api.v1.books.destroy', ['id' => 99]), [
            'auth_token' => $this->token,
        ]);
        
        $response->assertExactJson([
            'code' => 404,
            'message' => 'The resource was not found.',
            'errors' => [
                'book' => 'Not found.'
            ]
        ]);
    }
}
