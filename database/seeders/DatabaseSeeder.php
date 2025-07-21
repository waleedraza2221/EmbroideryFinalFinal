<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $this->call(AdminUserSeeder::class);

        // Create a test customer user
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Uncomment below to create more test users if needed
        // User::factory(10)->create();
    }
}
