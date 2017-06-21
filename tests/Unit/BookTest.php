<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BookTest extends TestCase
{
    use DatabaseMigrations;
    
    public function testShouldFailMissingAuthToken()
    {
        $response = $this->post(route('api.v1.books.store'), [
        ]);
        
        $this->assertEquals(401, $response->status());
        $response->assertExactJson([
            'code' => 401,
            'message' => 'Validation failed',
            'errors' => [
                'auth_token' => [
                    'The auth token field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldCreateBook()
    {
        \App\User::unguard();
        $user = \App\User::create([
            'id' => 3,
            'email' => 'test@email.com',
            'password' => 'password'
        ]);
        $token = sha1($user->email . time());
        
        \App\AuthToken::create([
            'token' => $token,
            'user_id' => $user->id
        ]);
        
        \App\Book::reguard();
        $response = $this->post(route('api.v1.books.store'), [
            'auth_token' => $token,
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one'
        ]);
        
        $this->assertEquals(200, $response->status());
        $response->assertJsonFragment([
            'title' => 'Five Little Pigs',
            'comment' => 'Nice one'
        ]);
        $this->assertEquals(1, \App\Book::count());
    }
    
    public function testShouldFailCreateBookWithEmptyTitle()
    {
        \App\User::unguard();
        $user = \App\User::create([
            'id' => 3,
            'email' => 'test@email.com',
            'password' => 'password'
        ]);
        $token = sha1($user->email . time());
        
        \App\AuthToken::create([
            'token' => $token,
            'user_id' => $user->id
        ]);
        
        \App\Book::reguard();
        $response = $this->post(route('api.v1.books.store'), [
            'auth_token' => $token,
            'comment' => 'Nice one'
        ]);
        
        $this->assertEquals(400, $response->status());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'title' => [
                    'The title field is required.'
                ]
            ]
        ]);
    }
}
