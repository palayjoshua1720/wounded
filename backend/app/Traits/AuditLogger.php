<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait AuditLogger
{
    /**
     * Log audit trail for entity actions
     *
     * @param Request $request
     * @param string $actionType
     * @param string $actionMessage
     * @param int $entityId
     * @param int $status
     * @return void
     */
    protected function logAudit(Request $request, $actionType, $actionMessage, $entityId, int $status = 0, ?string $attemptedIdentifier = null)
    {
        $userId = $request->user()->id ?? null;

        # If user is authenticated, attempted_identifier MUST be null
        if ($attemptedIdentifier === null) {
            if ($userId) {
                $attemptedIdentifier = (string) $userId; # Authenticated -> default to user ID
            } else {
                $attemptedIdentifier = $request->input('email'); # Unauthenticated -> try email from request
            }
        }
        
        $ipAddress = $request->ip();
        $entity = $this->getEntityName();
        $entityType = $this->getEntityType();

        // Get prev_hash from the latest audit log entry
        $lastLog = DB::table('woundmed_audit_logs')->latest('audit_log_id')->first();
        $prevHash = $lastLog ? $lastLog->row_hash : null;

        // Insert new log entry (row_hash temporarily empty string)
        $logId = DB::table('woundmed_audit_logs')->insertGetId([
            'user_id' => $userId,
            'attempted_identifier' => $attemptedIdentifier,
            'ip_address' => $ipAddress,
            'action_type' => $actionType,
            'action_message' => $actionMessage,
            'entity_id' => $entityId,
            'entity' => $entity,
            'entity_type' => $entityType,
            'status' => $status,
            'prev_hash' => $prevHash,
            'row_hash' => '', // Temporarily empty string to avoid NULL constraint
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Fetch the inserted log data (excluding row_hash and audit_log_id for hashing)
        $logData = DB::table('woundmed_audit_logs')->where('audit_log_id', $logId)->first();
        unset($logData->audit_log_id);
        unset($logData->row_hash);
        $dataString = json_encode($logData);
        $rowHash = hash('sha256', $dataString);

        // Update the log entry with the computed row_hash
        DB::table('woundmed_audit_logs')->where('audit_log_id', $logId)->update(['row_hash' => $rowHash]);
    }

    /**
     * Get entity name for audit logging
     * Should be overridden in controllers that use this trait
     *
     * @return string
     */
    protected function getEntityName()
    {
        return 'woundmed_entities';
    }

    /**
     * Get entity type for audit logging
     * Should be overridden in controllers that use this trait
     *
     * @return string
     */
    protected function getEntityType()
    {
        return 'entity';
    }
}
