<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeDTOCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new data transfer object class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $dtoDir = app_path('DataTransferObjects');
        $dtoPath = $dtoDir . '/' . $name . '.php';

        if (File::exists($dtoPath)) {
            $this->error('Data Transfer Object already exists!');
            return 1; // Hentikan eksekusi
        }

        if (!File::isDirectory($dtoDir)) {
            File::makeDirectory($dtoDir, 0755, true, true);
        }

        $stub = File::get(base_path('stubs/dto.stub'));
        $content = str_replace('{{DTOName}}', $name, $stub);
        File::put($dtoPath, $content);

        $this->info('Data Transfer Object created successfully!');
        $this->line('Data Transfer Object class created at: ' . $dtoPath);

        return 0;
    }
}
