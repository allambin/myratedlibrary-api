<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Library;

class LibraryTest extends TestCase
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
        $response = $this->post(route('api.v1.libraries.store'), [
        ]);
        
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
    
    public function testShouldCreateLibrary()
    {
        Library::reguard();
        $response = $this->post(route('api.v1.libraries.store'), [
            'auth_token' => $this->token,
            'name' => 'Fantasy',
        ]);
        
        $response->assertJsonFragment([
            'name' => 'Fantasy',
        ]);
        $this->assertEquals(1, Library::count());
    }
    
    public function testShouldFailCreateLibraryWithEmptyName()
    {        
        \App\Book::reguard();
        $response = $this->post(route('api.v1.libraries.store'), [
            'auth_token' => $this->token,
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'name' => [
                    'The name field is required.'
                ]
            ]
        ]);
    }
}
