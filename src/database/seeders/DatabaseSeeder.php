<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            AuthorsTableSeeder::class,
            BooksTableSeeder::class,
        ]);

        $user = User::first();
        $token = $user->createToken('api-token')->plainTextToken;
        $this->command->info("Test User token: {$token}");
    }
}
