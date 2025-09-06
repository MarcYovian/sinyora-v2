<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');

        // Path tempat file sitemap.xml akan disimpan
        $sitemapPath = public_path('sitemap.xml');

        Sitemap::create()
            // 1. Tambahkan halaman statis
            ->add(Url::create(route('home.index'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create(route('articles.index'))->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create(route('events.index'))->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            ->add(Url::create(route('borrowing.assets.index'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))

            // 2. Tambahkan halaman dinamis dari database (contoh: artikel)
            ->add(Article::published()->get())

            // Simpan file sitemap
            ->writeToFile($sitemapPath);

        $this->info('Sitemap generated successfully!');
        return 0;
    }
}
