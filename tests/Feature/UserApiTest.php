<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test user login.
     *
     * @return void
     */
    public function testUserLogin()
    {
        // Create a user
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Send a login request
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    /**
     * Test user logout.
     *
     * @return void
     */
    public function testUserLogout()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate user
        $token = $user->createToken('TestToken')->plainTextToken;

        // Send a logout request with authentication token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        // Assert response
        $response->assertStatus(204);
    }

    /**
     * Test fetching user details.
     *
     * @return void
     */
    public function testFetchUserDetails()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate user
        Sanctum::actingAs($user);

        // Send a GET request to fetch user details
        $response = $this->getJson('/api/user');

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Add other fields you want to assert
            ]);
    }
}
