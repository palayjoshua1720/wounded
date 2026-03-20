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
    protected function logAudit(
        Request $request,
        string $actionType,
        string $actionMessage,
        ?int $entityId = null,
        int $status = 0,
        ?string $attemptedIdentifier = null,
        ?string $entityOverride = null,
        ?string $entityTypeOverride = null
    ) {
        DB::transaction(function () use (
            $request,
            $actionType,
            $actionMessage,
            $entityId,
            $status,
            $attemptedIdentifier,
            $entityOverride,
            $entityTypeOverride
        ) {
            $userId = $request->user()->id ?? null;

            # If user is authenticated, attempted_identifier MUST be null
            if ($attemptedIdentifier === null) {
                $attemptedIdentifier = $userId
                    ? (string) $userId
                    : $request->input('email');
            }

            $ipAddress = $request->ip();

            # Use override values if provided, otherwise fall back to controller methods
            $entity = $entityOverride ?? $this->getEntityName();
            $entityType = $entityTypeOverride ?? $this->getEntityType();

            # Get prev_hash from the latest audit log entry
            $lastLog = DB::table('woundmed_audit_logs')->latest('audit_log_id')->first();
            $prevHash = $lastLog ? $lastLog->row_hash : null;

            # Insert new log entry (row_hash temporarily empty string)
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
                'row_hash' => '',
                'timestamp' => now()->toDateTimeString(),
            ]);

            # Fetch inserted row (now it has real prev_hash)
            $log = DB::table('woundmed_audit_logs')
                ->where('audit_log_id', $logId)
                ->first();

            # DETERMINISTIC HASH COMPUTATION - Build explicit array to match the verification command
            $dataForHash = [
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

            # DETERMINISTIC ORDER - Sort keys alphabetically
            ksort($dataForHash);

            # Encode with the same flags used previously in verification process
            $jsonString = json_encode(
                $dataForHash,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );

            $rowHash = hash('sha256', $jsonString);

            DB::table('woundmed_audit_logs')
                ->where('audit_log_id', $logId)
                ->update(['row_hash' => $rowHash]);
        });
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
