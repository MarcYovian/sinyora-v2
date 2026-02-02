<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'event',
            'Inspirasi',
            'Religi',
            'Tips',
            'Tutorial',
            'Opini',
        ];

        foreach ($tags as $tag) {
            Tag::factory()->create([
                'name' => $tag,
                'slug' => \Illuminate\Support\Str::slug($tag),
            ]);
        }

        Tag::factory(5)->create();
    }
}
