<?php

namespace Qmrp\CustomField\Commands;

use Illuminate\Console\Command;
use Qmrp\CustomField\Services\CustomFieldService;
use Illuminate\Support\Facades\File;

class ExportFields extends Command
{
    protected $signature = 'customfield:export {module : The module name} {--output= : Output file path}';

    protected $description = 'Export custom field configuration for a module';

    protected $service;

    public function __construct(CustomFieldService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $module = $this->argument('module');
        $output = $this->option('output');

        $this->info("Exporting custom fields for module: {$module}");

        $data = $this->service->exportModuleFields($module);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($output) {
            File::put($output, $json);
            $this->info("Exported to: {$output}");
        } else {
            $this->line($json);
        }

        return Command::SUCCESS;
    }
}
