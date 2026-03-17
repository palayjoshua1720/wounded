<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyAuditIntegrity extends Command
{
    protected $signature = 'audit:verify';
    protected $description = 'Verify integrity of audit log hash chain';

    private bool $isVerifying = false;

    public function handle()
    {
        $this->info('Starting audit chain verification...');
        $this->isVerifying = true;

        $start = now();
        $previousHash = null;
        $verifiedCount = 0;
        $broken = false;
        $errorMessage = '';

        try {
            DB::table('woundmed_audit_logs')
                ->orderBy('audit_log_id')
                ->chunk(500, function ($logs) use (&$previousHash, &$verifiedCount, &$broken, &$errorMessage) {

                    if ($broken) {
                        return false;
                    }

                    foreach ($logs as $log) {
                        # 1. Chain linkage check
                        if ($log->audit_log_id === 1) {
                            if ($log->prev_hash !== null && $log->prev_hash !== '') {
                                $broken = true;
                                $errorMessage = "First record (ID 1) should have NULL or empty prev_hash. Found: " . ($log->prev_hash ?? 'NULL');
                                $this->error($errorMessage);
                                return false;
                            }
                        } else {
                            if ($log->prev_hash !== $previousHash) {
                                $broken = true;
                                $errorMessage = "Chain broken at ID {$log->audit_log_id}\n" .
                                    "Expected prev_hash: " . ($previousHash ?? 'null') . "\n" .
                                    "Actual prev_hash:   " . ($log->prev_hash ?? 'null');
                                $this->error($errorMessage);
                                return false;
                            }
                        }

                        if ($verifiedCount > 0 && $verifiedCount % 5000 === 0) {
                            $this->info("Still verifying... {$verifiedCount} records processed so far");
                        }

                        # 2. Recompute row hash
                        $data = [
                            'user_id'              => $log->user_id,
                            'attempted_identifier' => $log->attempted_identifier,
                            'ip_address'           => $log->ip_address,
                            'action_type'          => $log->action_type,
                            'action_message'       => $log->action_message,
                            'entity_id'            => $log->entity_id,
                            'entity'               => $log->entity,
                            'entity_type'          => $log->entity_type,
                            'status'               => (string) $log->status,
                            'prev_hash'            => $log->prev_hash,
                            'timestamp'            => $log->timestamp,
                        ];

                        ksort($data);

                        $json = json_encode(
                            $data,
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                        );

                        $recomputed = hash('sha256', $json);

                        if ($recomputed !== $log->row_hash) {
                            $broken = true;
                            $errorMessage = "Hash mismatch at ID {$log->audit_log_id}\n" .
                                "Stored hash:    {$log->row_hash}\n" .
                                "Recomputed:     $recomputed\n\n" .
                                "Data used:\n" . json_encode($data, JSON_PRETTY_PRINT);
                            $this->error($errorMessage);
                            return false;
                        }

                        $previousHash = $log->row_hash;
                        $verifiedCount++;
                    }

                    return true;
                });

            if ($broken) {
                Log::error("Audit chain verification FAILED", [
                    'error'            => $errorMessage,
                    'verified_up_to'   => $verifiedCount,
                ]);
                return self::FAILURE;
            }

            $duration = $start->diffInSeconds(now());

            $this->info("Audit chain verified successfully.");
            $this->info("Processed {$verifiedCount} records in {$duration} seconds.");

            Log::info('Audit chain verification succeeded', [
                'records_verified' => $verifiedCount,
                'duration_seconds' => $duration,
                'run_at'           => now()->toDateTimeString(),
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Audit verification failed: ' . $e->getMessage());
            Log::error('Audit verification exception', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        } finally {
            $this->isVerifying = false;
        }
    }
}