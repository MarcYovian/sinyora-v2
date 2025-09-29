<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseSeeder extends Seeder
{
    // use RefreshDatabase;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // password
        ]);

        $this->call([
            MenuSeeder::class,
            RolePermissionSeeder::class,
            AssetBorrowingSeeder::class,
            EventCategorySeeder::class,
            LocationSeeder::class,
            OrganizationSeeder::class,
            // EventSeeder::class,
            ArticleCategorySeeder::class,
            TagSeeder::class,
        ]);
        $user->assignRole('admin');
    }
}
