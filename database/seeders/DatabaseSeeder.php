<?php

namespace Database\Seeders;

use App\Models\Fund;
use App\Models\FundManager;
use App\Models\Company;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create Store Managers
        FundManager::factory()
            ->count(5)
            ->has(
                Fund::factory()
                    ->count(3)
                    ->hasFundAliases(2)
                    ->hasAttached(
                        Company::factory()->count(2)
                    )
            )
            ->create();
    }
}
