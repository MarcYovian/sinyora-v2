<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Create default "Uncategorized" category if it doesn't exist
        $defaultCategory = DB::table('article_categories')
            ->where('slug', 'uncategorized')
            ->first();

        if (!$defaultCategory) {
            $defaultCategoryId = DB::table('article_categories')->insertGetId([
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
                'published_articles_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $defaultCategoryId = $defaultCategory->id;
        }

        // Step 2: Update all articles with NULL category_id to default category
        DB::table('articles')
            ->whereNull('category_id')
            ->update(['category_id' => $defaultCategoryId]);

        // Step 3: Drop the old foreign key constraint
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        // Step 4: Make category_id NOT NULL
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });

        // Step 5: Re-add foreign key with RESTRICT on delete (not SET NULL)
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('article_categories')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse step 5: Drop new foreign key
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        // Reverse step 4: Make category_id nullable again
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });

        // Reverse step 5: Re-add original foreign key with SET NULL
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('article_categories')
                ->onDelete('set null');
        });
    }
};
