<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('import:wilayah')]
#[Description('Import master wilayah data from JSON file')]
class ImportWilayahCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/wilayah.json');

        if (!file_exists($path)) {
            $this->error("File not found at $path");
            return;
        }

        $this->info("Reading JSON data...");
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->error("JSON Decode Error: " . json_last_error_msg());
            return;
        }

        $this->info("Importing " . count($data) . " records...");

        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        $chunks = array_chunk($data, 500);

        foreach ($chunks as $chunk) {
            $insertData = array_map(function ($row) {
                return [
                    'idsubsls' => (string) ($row['idsubsls'] ?? ''),
                    'nmsls' => $row['nmsls'] ?? null,
                    'nama_ketua' => $row['nama_ketua'] ?? null,
                    'nmkec' => $row['nmkec'] ?? '',
                    'kdkec' => (string) ($row['kdkec'] ?? ''),
                    'nmdesa' => $row['nmdesa'] ?? '',
                    'kddesa' => (string) ($row['kddesa'] ?? ''),
                    'kdsls' => (string) ($row['kdsls'] ?? ''),
                    'kdsubsls' => (string) ($row['kdsubsls'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $chunk);

            \App\Models\MasterWilayah::upsert(
                $insertData,
                ['idsubsls'],
                ['nmsls', 'nama_ketua', 'nmkec', 'kdkec', 'nmdesa', 'kddesa', 'kdsls', 'kdsubsls', 'updated_at']
            );

            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine();
        $this->info("Import completed successfully!");
    }
}
