<?php

namespace App\Console\Commands;

use App\Services\ProductImportService;
use Illuminate\Console\Command;

class ImportJsonCommand extends Command
{
    protected $signature = 'keycompare:import
                            {file : Path to the JSON file}
                            {--source=cli : Source identifier}';

    protected $description = 'Import products from a JSON file';

    public function handle(ProductImportService $importer): int
    {
        $path = $this->argument('file');
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['products'])) {
            $this->error("Invalid JSON structure. Expected {\"products\": [...]}");
            return self::FAILURE;
        }

        $this->info("Importing " . count($data['products']) . " products from {$path}...");

        $log = $importer->import($data, $this->option('source'));

        $this->info("Status: {$log->status}");
        $this->info("Products: {$log->products_count} (created: {$log->products_created}, updated: {$log->products_updated})");
        $this->info("Duration: {$log->duration_ms}ms");

        if (!empty($log->errors)) {
            $this->warn("Errors: " . count($log->errors));
            foreach (array_slice($log->errors, 0, 5) as $err) {
                $this->line("  - " . json_encode($err));
            }
            if (count($log->errors) > 5) {
                $this->line("  ... and " . (count($log->errors) - 5) . " more");
            }
        }

        return $log->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
