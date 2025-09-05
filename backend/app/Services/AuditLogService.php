<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditLogService
{
    public function generateRowHash(array $data, $prevHash = null): string
    {
        $string = json_encode($data) . $prevHash;
        return hash('sha256', $string);
    }

    public function getLastRowHash(): ?string
    {
        $last = DB::table('woundmed_audit_logs')
            ->select('row_hash')
            ->latest('audit_log_id')
            ->first();

        return $last?->row_hash ?? null;
    }

    # Manual Blockchain verification
    public function verifyAuditChain()
    {
        $logs = DB::table('woundmed_audit_logs')
            ->orderBy('timestamp', 'asc')
            ->get();

        $previousHash = null;
        $issues = [];

        foreach ($logs as $log) {
            $data = $log->user_id . $log->attempted_identifier . $log->ip_address .
                    $log->action_type . $log->action_message . $log->entity_id .
                    $log->entity . $log->entity_type . $log->status .
                    $log->prev_hash . $log->timestamp;

            $calculatedHash = hash('sha256', $data);

            if ($calculatedHash !== $log->row_hash) {
                $issues[] = "Row {$log->audit_log_id} has been modified";
            }

            if ($previousHash !== null && $log->prev_hash !== $previousHash) {
                $issues[] = "Chain broken at row {$log->audit_log_id}";
            }

            $previousHash = $log->row_hash;
        }

        return $issues ?: ['status' => 'Chain verified successfully'];
    }
}
