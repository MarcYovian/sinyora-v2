<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetBorrowingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\AssetCategory::factory(3)->create();
        \App\Models\Asset::factory(10)->create();
        \App\Models\Borrowing::factory(10)->create()->each(function ($borrowing) {
            $assetIds = \App\Models\Asset::inRandomOrder()->take(rand(1, 4))->pluck('id');
            foreach ($assetIds as $assetId) {
                $borrowing->assets()->attach($assetId, [
                    'quantity' => rand(1, 10),
                ]);
            }
        });
    }
}
