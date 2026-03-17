<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Services\FileEncryptionService;

class EncryptExistingFiles extends Command
{
    protected $signature = 'hipaa:encrypt-existing-files
                            {--dry-run : Show what would be encrypted without actually doing it}
                            {--model=* : Only process specific models (e.g. --model=Manufacturer --model=IVR)}';

    protected $description = 'Encrypt all existing unencrypted files for HIPAA compliance (safe to re-run)';

    /**
     * Models and their file fields to encrypt.
     * Maps model class => [field => disk]
     */
    protected array $modelFileMap = [
        \App\Models\Manufacturer::class => [
            'ivr_file'        => 'private',
            'order_file'      => 'private',
            'onboarding_file' => 'private',
            'logo'            => 'public',
        ],
        \App\Models\IVR::class => [
            'ivr_file' => 'private',
        ],
        \App\Models\Orders::class => [
            'order_file' => 'private',
        ],
        \App\Models\Clinic::class => [
            'logo' => 'public',
        ],
        \App\Models\Brand::class => [
            'logo' => 'public',
        ],
        \App\Models\UsageLog::class => [
            'filepath' => 'private',
        ],
    ];

    public function handle(): int
    {
        $isDryRun   = $this->option('dry-run');
        $onlyModels = $this->option('model');

        $fileService = app(FileEncryptionService::class);

        $totalFound     = 0;
        $totalEncrypted = 0;
        $totalSkipped   = 0;
        $totalFailed    = 0;

        if ($isDryRun) {
            $this->warn('DRY RUN — no files will be modified.');
        }

        foreach ($this->modelFileMap as $modelClass => $fields) {
            $shortName = class_basename($modelClass);

            // Filter by --model if specified
            if (!empty($onlyModels) && !in_array($shortName, $onlyModels)) {
                continue;
            }

            $this->info("\n[{$shortName}]");

            // Use withTrashed() if model supports soft deletes
            $query = method_exists($modelClass, 'withTrashed')
                ? $modelClass::withTrashed()
                : $modelClass::query();

            $records = $query->get();

            foreach ($records as $record) {
                $pk = $record->getKey();

                foreach ($fields as $field => $disk) {
                    // Get raw DB value (bypasses trait decryption)
                    $rawPath = $record->getRawOriginal($field) ?? $record->getAttributes()[$field] ?? null;

                    if (empty($rawPath)) {
                        continue;
                    }

                    // If the DB column itself is AES-encrypted (EncryptsData trait),
                    // decrypt it first to get the actual file path.
                    if (!str_ends_with((string) $rawPath, '.enc')) {
                        try {
                            $decoded = base64_decode($rawPath, true);
                            if ($decoded !== false) {
                                $data = json_decode($decoded, true);
                                if (is_array($data) && isset($data['iv'], $data['value'], $data['mac'])) {
                                    $rawPath = Crypt::decryptString($rawPath);
                                }
                            }
                        } catch (\Exception $e) {
                            // Not a Laravel-encrypted value, use as-is
                        }
                    }

                    $totalFound++;

                    // Already encrypted — skip
                    if ($fileService->isEncrypted($rawPath)) {
                        $this->line("  <fg=gray>SKIP  [{$pk}] {$field}: already encrypted ({$rawPath})</>");
                        $totalSkipped++;
                        continue;
                    }

                    // Check the file actually exists on disk
                    if (!Storage::disk($disk)->exists($rawPath)) {
                        $this->line("  <fg=yellow>MISS  [{$pk}] {$field}: file not found on '{$disk}' disk ({$rawPath})</>");
                        $totalSkipped++;
                        continue;
                    }

                    $this->line("  <fg=cyan>FOUND [{$pk}] {$field}: {$rawPath}</>");

                    if ($isDryRun) {
                        $totalEncrypted++;
                        continue;
                    }

                    try {
                        // Build encrypted storage path
                        $directory = strtolower($shortName);
                        $timestamp = now()->format('Ymd_His');
                        $random    = bin2hex(random_bytes(8));
                        $encPath   = "encrypted/{$directory}/{$field}/{$pk}_{$field}_{$timestamp}_{$random}";

                        // Get full filesystem path
                        $fullPath = Storage::disk($disk)->path($rawPath);

                        // Encrypt and store in 'local' disk
                        $result = $fileService->encryptAndStore($fullPath, $encPath, 'local');

                        // Securely delete the original
                        Storage::disk($disk)->delete($rawPath);

                        // Update the DB record directly (bypass model events to avoid re-triggering)
                        \Illuminate\Support\Facades\DB::table($record->getTable())
                            ->where($record->getKeyName(), $pk)
                            ->update([$field => $result['path']]);

                        $this->line("  <fg=green>DONE  [{$pk}] {$field}: → {$result['path']}</>");
                        $totalEncrypted++;

                    } catch (\Exception $e) {
                        $this->error("  FAIL  [{$pk}] {$field}: {$e->getMessage()}");
                        $totalFailed++;
                    }
                }
            }
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Files found',     $totalFound],
                ['Encrypted',       $totalEncrypted],
                ['Skipped',         $totalSkipped],
                ['Failed',          $totalFailed],
            ]
        );

        if ($isDryRun) {
            $this->warn('Dry run complete. Run without --dry-run to apply changes.');
        } else {
            $this->info('Done. All existing files have been encrypted.');
        }

        return $totalFailed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
