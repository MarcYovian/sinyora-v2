<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create([
            'name' => 'Kapel St Yohanes Rasul Lantai 1',
            'description' => 'Kapel St Yohanes Rasul Lantai 1',
            'image' => 'https://placehold.co/600x400?text=Hello+World',
            'is_active' => 1,
        ]);
        Location::create([
            'name' => 'Kapel St Yohanes Rasul Lantai 2',
            'description' => 'Kapel St Yohanes Rasul Lantai 2',
            'image' => 'https://placehold.co/600x400',
            'is_active' => 1,
        ]);
        Location::create([
            'name' => 'Halaman Kapel St Yohanes Rasul',
            'description' => 'Halaman Kapel St Yohanes Rasul',
            'image' => 'https://placehold.co/600x400',
            'is_active' => 1,
        ]);
    }
}
