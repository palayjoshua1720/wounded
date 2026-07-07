<?php

// ============================================================================
// HIPAA COMPLIANCE - ENCRYPT EXISTING INVENTORY LEDGER & PATIENT GRAFT LOG
// ----------------------------------------------------------------------------
// Backfills encryption on rows that were created BEFORE the EncryptsData
// trait was added to the InventoryLedger and PatientGraftLog models.
//
// This migration is idempotent:
//  - Rows that already have encrypted values are skipped (the encryption
//    service checks isEncrypted() before re-encrypting).
//  - Rows with only plaintext values are re-saved, triggering the saving
//    event which encrypts in-place.
//
// Only the sensitive free-text fields are encrypted. Identifiers used in
// WHERE/LIKE queries (serial_number, product_type, graft_log_code, etc.)
// are kept in plaintext via the GlobalEncryptionService $neverEncrypt list.
// ============================================================================

use App\Models\InventoryLedger;
use App\Models\PatientGraftLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        // Backfill InventoryLedger rows.
        try {
            InventoryLedger::withTrashed()->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    // Save quietly (no events) would skip encryption, so we
                    // must use save() to trigger the encryption lifecycle.
                    // timestamps=false prevents bumping updated_at unnecessarily.
                    $row->timestamps = false;
                    $row->save();
                }
            }, 'ledger_id');
        } catch (\Throwable $e) {
            Log::warning('Backfill encryption skipped for InventoryLedger', [
                'error' => $e->getMessage(),
            ]);
        }

        // Backfill PatientGraftLog rows.
        try {
            PatientGraftLog::withTrashed()->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $row->timestamps = false;
                    $row->save();
                }
            }, 'graft_log_id');
        } catch (\Throwable $e) {
            Log::warning('Backfill encryption skipped for PatientGraftLog', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: we do not roll encryption back. Removing encryption on
        // sensitive data would be a HIPAA regression, so the down path
        // intentionally does nothing.
    }
};
