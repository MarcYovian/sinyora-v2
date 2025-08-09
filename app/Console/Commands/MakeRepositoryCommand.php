<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository interface and its eloquent implementation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $interfaceName = $name . 'Interface';
        $eloquentName = 'Eloquent' . $name;

        // Tentukan path direktori dan file secara terpisah
        $interfaceDir = app_path('Repositories/Contracts');
        $eloquentDir = app_path('Repositories/Eloquent');

        // Path untuk interface
        $interfacePath = $interfaceDir . '/' . $interfaceName . '.php';
        // Path untuk implementasi
        $eloquentPath = $eloquentDir . '/' . $eloquentName . '.php';

        // Cek jika file sudah ada
        if (File::exists($interfacePath) || File::exists($eloquentPath)) {
            $this->error('Repository already exists!');
            return 1;
        }

        if (!File::isDirectory($interfaceDir)) {
            File::makeDirectory($interfaceDir, 0755, true, true);
        }
        if (!File::isDirectory($eloquentDir)) {
            File::makeDirectory($eloquentDir, 0755, true, true);
        }

        // Ambil template (stub) dan ganti placeholder
        $interfaceStub = File::get(base_path('stubs/repository.interface.stub'));
        $interfaceContent = str_replace('{{InterfaceName}}', $interfaceName, $interfaceStub);
        File::put($interfacePath, $interfaceContent);

        $eloquentStub = File::get(base_path('stubs/repository.eloquent.stub'));
        $eloquentContent = str_replace(
            ['{{EloquentName}}', '{{InterfaceName}}', '{{Name}}'],
            [$eloquentName, $interfaceName, $name],
            $eloquentStub
        );
        File::put($eloquentPath, $eloquentContent);

        $this->info("Repository {$name} created successfully.");
        $this->line("\t<info>Interface:</info> {$interfacePath}");
        $this->line("\t<info>Eloquent:</info> {$eloquentPath}");

        return 0;
    }

    protected function ensureDirectoryExists($path)
    {
        if (!File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0755, true, true);
        }
    }
}
