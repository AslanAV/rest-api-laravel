<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testRegister(): void
    {
        $uri = '/api/register';
        $body = [
            'email' => 'example@email.com',
            'password' => 'password',
        ];

        $response = $this->postJson($uri, $body);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => $body['email'],
        ]);
    }

    public function testRegisterFail(): void
    {
        $email = 'example@email.com';
        User::factory()->create(['email' => $email]);

        $uri = '/api/register';
        $body = [
            'email' => 'example@email.com',
            'password' => 'password',
        ];

        $response = $this->postJson($uri, $body);
        $response->assertStatus(422);
    }
}
