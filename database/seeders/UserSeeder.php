<?php
declare(strict_types=1);
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory(10)->create();  // Create 10 random users
    }
}

