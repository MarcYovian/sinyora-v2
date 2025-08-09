<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Implement the command logic
        $name = $this->argument('name');

        $serviceDir = app_path('Services');
        $servicePath = $serviceDir . '/' . $name . '.php';

        // Cek apakah file service sudah ada
        if (File::exists($servicePath)) {
            $this->error('Service already exists!');
            return 1; // Hentikan eksekusi
        }

        // Pastikan direktori app/Services ada, buat jika belum
        if (!File::isDirectory($serviceDir)) {
            File::makeDirectory($serviceDir, 0755, true, true);
        }

        // Ambil template (stub) dan ganti placeholder
        $stub = File::get(base_path('stubs/service.stub'));
        $content = str_replace('{{ServiceName}}', $name, $stub);
        File::put($servicePath, $content);

        $this->info('Service created successfully.');
        $this->line('Service class created at: ' . $servicePath);

        return 0; // Sukses
    }
}
