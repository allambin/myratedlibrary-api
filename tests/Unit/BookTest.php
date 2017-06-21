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
}
