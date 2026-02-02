<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Denormalisasi: Menambahkan kolom counter untuk menghindari
     * correlated subquery yang mahal pada query popular categories.
     */
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->unsignedInteger('published_articles_count')->default(0)->after('slug');
        });

        // Populate existing counts
        $categories = ArticleCategory::all();
        foreach ($categories as $category) {
            $count = Article::where('category_id', $category->id)
                ->published()
                ->count();
            $category->update(['published_articles_count' => $count]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropColumn('published_articles_count');
        });
    }
};
