<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_a_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_an_admin_user_can_view_the_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_login_rejects_a_non_admin_even_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'shopper@example.test',
            'password' => bcrypt('supersecret1'),
            'is_admin' => false,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'shopper@example.test',
            'password' => 'supersecret1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
