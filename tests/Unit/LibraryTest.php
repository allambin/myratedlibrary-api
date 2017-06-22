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
    
    public function testShouldUpdateLibrary()
    {
        $library = \App\Library::create([
            'id' => 30,
            'name' => 'Fantasy',
            'user_id' => $this->user->id
        ]);
        
        \App\Library::reguard();
        $response = $this->put(route('api.v1.libraries.update', ['id' => $library->id]), [
            'auth_token' => $this->token,
            'name' => 'Fantasy - updated',
        ]);
        
        $response->assertJsonFragment([
            'id' => 30,
            'name' => 'Fantasy - updated',
        ]);
    }
    
    public function testShouldFailUpdatOtherUsereLibrary()
    {
        \App\User::unguard();
        $user2 = \App\User::create([
            'id' => 4,
            'email' => 'test2@email.com',
            'password' => 'password'
        ]);
        
        $library = \App\Library::create([
            'id' => 30,
            'name' => 'Fantasy',
            'user_id' => $user2->id
        ]);
        
        \App\Library::reguard();
        $response = $this->put(route('api.v1.libraries.update', ['id' => $library->id]), [
            'auth_token' => $this->token,
            'name' => 'Fantasy - updated',
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'You are not authorized to perform this action.',
            'errors' => [
                'library' => 'Unauthorized action.'
            ]
        ]);
    }
    
    public function testShouldFailUpdatWithUnknownLibrary()
    {
        \App\Library::reguard();
        $response = $this->put(route('api.v1.libraries.update', ['id' => 99]), [
            'auth_token' => $this->token,
            'name' => 'Fantasy - updated',
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'The resource was not found.',
            'errors' => [
                'library' => 'Not found.'
            ]
        ]);
    }
    
    public function testShouldSetLibraryPublic()
    {
        $library = \App\Library::create([
            'id' => 30,
            'name' => 'Fantasy',
            'user_id' => $this->user->id
        ]);
        
        \App\Library::reguard();
        $response = $this->patch(route('api.v1.libraries.patch', ['id' => $library->id]), [
            'auth_token' => $this->token,
            'is_public' => 1
        ]);
        
        $response->assertJson([
            'original' => [
                'id' => 30,
                'name' => 'Fantasy',
                'is_public' => 0,
                'user_id' => 3
            ],
            'patched' => [
                'id' => 30,
                'name' => 'Fantasy',
                'is_public' => 1,
                'user_id' => 3
            ]
        ]);
    }
    
    public function testShouldFailToPatchLibraryWithWrongField()
    {
        $library = \App\Library::create([
            'id' => 30,
            'name' => 'Fantasy',
            'user_id' => $this->user->id
        ]);
        
        \App\Library::reguard();
        $response = $this->patch(route('api.v1.libraries.patch', ['id' => $library->id]), [
            'auth_token' => $this->token,
            'test' => 1
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'The resource cannot be patched this way.',
            'errors' => [
                'library' => 'This field cannot be modified.'
            ]
        ]);
    }
}
