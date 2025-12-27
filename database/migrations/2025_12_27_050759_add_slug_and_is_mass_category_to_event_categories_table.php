<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add columns without unique constraint
        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->boolean('is_mass_category')->default(false)->after('is_active');
        });

        // Step 2: Populate existing records with slugs
        $categories = DB::table('event_categories')->get();
        foreach ($categories as $category) {
            $slug = Str::slug($category->name);

            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (DB::table('event_categories')->where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            DB::table('event_categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);
        }

        // Step 3: Make slug required and unique
        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'is_mass_category']);
        });
    }
};
