<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receives_a_token_and_a_cart(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'password' => 'supersecret1',
        ]);

        $response->assertCreated()->assertJsonStructure(['token', 'user' => ['id', 'email']]);

        $user = User::where('email', 'ada@example.test')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->cart);
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ada@example.test']);

        $response = $this->postJson('/api/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'password' => 'supersecret1',
        ]);

        $response->assertStatus(422);
    }

    public function test_a_user_can_log_in_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'ada@example.test',
            'password' => bcrypt('supersecret1'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ada@example.test',
            'password' => 'supersecret1',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rejects_a_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'ada@example.test',
            'password' => bcrypt('supersecret1'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ada@example.test',
            'password' => 'totally-wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_requests_to_protected_routes_are_rejected(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
        $this->getJson('/api/cart')->assertStatus(401);
        $this->getJson('/api/orders')->assertStatus(401);
    }
}
