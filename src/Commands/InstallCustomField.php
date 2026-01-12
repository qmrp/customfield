<?php

namespace Qmrp\CustomField\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCustomField extends Command
{
    protected $signature = 'customfield:install';

    protected $description = 'Install the custom field package';

    public function handle()
    {
        $this->info('Installing Custom Field Package...');

        $this->publishMigrations();
        $this->publishViews();

        $this->info('Custom Field Package installed successfully!');
        $this->warn('Please run: php artisan migrate');
    }

    protected function publishMigrations()
    {
        $this->info('Publishing migrations...');

        $source = __DIR__ . '/../../database/migrations';
        $destination = database_path('migrations');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $files = File::files($source);
        foreach ($files as $file) {
            $filename = date('Y_m_d_His') . '_' . $file->getFilename();
            File::copy($file->getPathname(), $destination . '/' . $filename);
            $this->line("  - {$filename}");
        }
    }

    protected function publishViews()
    {
        $this->info('Publishing views...');

        $source = __DIR__ . '/../../resources/views';
        $destination = resource_path('views/vendor/customfield');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        File::copyDirectory($source, $destination);
        $this->line("  - Views published to {$destination}");
    }
}
