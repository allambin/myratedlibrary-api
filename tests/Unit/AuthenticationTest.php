<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;
    
    public function testShouldRegister()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, \App\User::count());
        $response->assertJsonStructure([
            'email', 'updated_at', 'created_at', 'id'
        ]);
    }
    
    public function testShouldFailRegisterWithDuplicateEmail()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $this->assertEquals(200, $response->status());
        
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $this->assertEquals(400, $response->status());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'email' => [
                    'The email has already been taken.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailRegisterWithEmptyPassword()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com'
        ]);
        
        $response->assertStatus(400);
        $this->assertEquals(0, \App\User::count());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'password' => [
                    'The password field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailRegisterWithEmptyEmail()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'password' => 'password'
        ]);
        
        $response->assertStatus(400);
        $this->assertEquals(0, \App\User::count());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'email' => [
                    'The email field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailRegisterWithShortPasswordAndEmptyEmail()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'password' => 'pass'
        ]);
        
        $response->assertStatus(400);
        $this->assertEquals(0, \App\User::count());
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'email' => [
                    'The email field is required.'
                ],
                'password' => [
                    'The password must be at least 6 characters.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailSignInWithEmptyEmail()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, \App\User::count());
        
        $response = $this->post(route('api.v1.auth.login'), [
            'password' => 'foobar'
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Validation failed',
            'errors' => [
                'email' => [
                    'The email field is required.'
                ]
            ]
        ]);
    }
    
    public function testShouldFailSignInWithWrongPassword()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, \App\User::count());
        
        $response = $this->post(route('api.v1.auth.login'), [
            'email' => 'test@mail.com',
            'password' => 'wrongone'
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Login failed',
            'errors' => [
                'user' => 'Wrong email/password.'
            ]
        ]);
    }
    
    public function testShouldFailSignInWithUnknownUser()
    {
        $response = $this->post(route('api.v1.auth.login'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $response->assertExactJson([
            'code' => 400,
            'message' => 'Login failed',
            'errors' => [
                'user' => 'Wrong email/password.'
            ]
        ]);
    }
    
    public function testShouldSignIn()
    {
        $response = $this->post(route('api.v1.auth.register'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, \App\User::count());
        
        $response = $this->post(route('api.v1.auth.login'), [
            'email' => 'test@mail.com',
            'password' => 'foobar'
        ]);
        $this->assertEquals(200, $response->status());
        $response->assertJsonStructure([
            'auth_token'
        ]);
    }
}
