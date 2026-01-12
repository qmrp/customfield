<?php

namespace Qmrp\CustomField\Commands;

use Illuminate\Console\Command;
use Qmrp\CustomField\Services\CustomFieldService;
use Illuminate\Support\Facades\File;

class ImportFields extends Command
{
    protected $signature = 'customfield:import {file : The JSON file to import}';

    protected $description = 'Import custom field configuration from a JSON file';

    protected $service;

    public function __construct(CustomFieldService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $file = $this->argument('file');

        if (!File::exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $this->info("Importing custom fields from: {$file}");

        $json = File::get($file);
        $data = json_decode($json, true);

        if (!$data) {
            $this->error("Invalid JSON file");
            return Command::FAILURE;
        }

        if ($this->service->importModuleFields($data)) {
            $this->info("Custom fields imported successfully!");
            return Command::SUCCESS;
        } else {
            $this->error("Failed to import custom fields");
            return Command::FAILURE;
        }
    }
}
