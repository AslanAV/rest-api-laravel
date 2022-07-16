<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testRegister(): void
    {
        $body = [
            'name' => 'username',
            'email' => 'example@email.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('register'), $body);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => $body['email'],
        ]);
    }

    public function testRegisterFail(): void
    {
        $email = 'example@email.com';
        User::factory()->create(['email' => $email]);

        $body = [
            'name' => 'username',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('register'), $body);

        $response->assertStatus(422);
    }

    public function testLogin(): void
    {
        $email = 'example@email.com';
        $password = 'password';

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $body = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson(route('login'), $body);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'email',
            'token',
        ]);
    }

    public function testLoginFail(): void
    {
        $email = 'example@email.com';
        $password = 'password';

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $body = [
            'email' => $email,
            'password' => 'failed-password',
        ];

        $response = $this->postJson(route('login'), $body);

        $response->assertStatus(401);
    }

    public function testUseBearerToken(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('API_TOKEN')->plainTextToken;

        $route = route('me');

        $response = $this->json('GET', $route, [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }
}
