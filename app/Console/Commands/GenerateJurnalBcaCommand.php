<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class GenerateJurnalBcaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'python:generate-jurnal-bcaevi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan skrip Python generate_jurnal_bcaevi.py secara aman melalui wrapper.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Memulai eksekusi skrip Python generate_jurnal_bcaevi.py...');
        Log::info('Console command python:generate-jurnal-bcaevi started.');
        
        $scriptPath = base_path('scripts/python/generate_jurnal_bcaevi.py');
        
        if (!file_exists($scriptPath)) {
            $this->error("Skrip tidak ditemukan di path: {$scriptPath}");
            Log::error("Console command python:generate-jurnal-bcaevi failed: Script not found at {$scriptPath}");
            return Command::FAILURE;
        }

        // Inisialisasi Process dengan path executable python
        // Note: Asumsi environment Windows/Laragon menggunakan alias 'python'
        $process = new Process(['python', $scriptPath]);
        
        // Konfigurasi timeout: 300 detik (5 menit) karena pemrosesan excel bisa memakan waktu
        $process->setTimeout(300);

        try {
            $process->mustRun(function ($type, $buffer) {
                // Menampilkan output dari skrip python secara real-time ke console
                if (Process::ERR === $type) {
                    $this->error($buffer);
                } else {
                    $this->line($buffer);
                }
            });

            $this->info('Eksekusi skrip Python berhasil diselesaikan.');
            Log::info('Console command python:generate-jurnal-bcaevi completed successfully.');

            return Command::SUCCESS;

        } catch (ProcessFailedException $exception) {
            $this->error('Gagal mengeksekusi skrip Python:');
            $this->error($exception->getMessage());
            
            Log::error('Console command python:generate-jurnal-bcaevi failed to execute.', [
                'exception' => $exception->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
