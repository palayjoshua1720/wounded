<?php

namespace App\Services;

use App\Services\HmacHashService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class GlobalEncryptionService
{
    /**
     * Fields that should NEVER be encrypted
     * (primary keys, foreign keys, timestamps, status fields, boolean flags)
     */
    protected array $neverEncrypt = [
        // Primary keys
        'id',
        'audit_log_id',
        'user_id',
        'clinic_id',
        'patient_id',
        'manufacturer_id',
        'brand_id',
        'ivr_id',
        'order_id',
        'invoice_id',
        'graft_size_id',
        'product_id',
        'return_id',
        'usage_log_id',
        'serial_payment_id',
        'biller_tracking_id',
        'backup_code_id',
        'email_verification_code_id',
        'magic_token_id',
        
        // Foreign keys
        'updated_by',
        'ordering_user_id',
        
        // Timestamps
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'verified_at',
        'submitted_at',
        'ordered_at',
        'followup_last_sent_at',
        'used_at',
        'last_logged_in',
        
        // Status/Type fields (integers/enums)
        'user_status',
        'user_role',
        'clinic_status',
        'order_status',
        'ivr_status',
        'eligibility_status',
        'invoice_status',
        'return_status',
        'entry_type',
        'action_type',
        'status',
        'entity_type',
        
        // Boolean flags
        'backup_codes_enabled',
        'one_time_email_verification',
        'force_password_change',
        
        // Numeric fields
        'stock',
        'quantity',
        'amount',
        'price',
        'total',
        'item_no',
        
        // Array/JSON fields (stored as JSON)
        'items',
        'other_product_items',
        'assigned_clinician_ids',
        'line_items',
        
        // Hash fields (already encrypted/hashed) and blind-index hash columns
        'password',
        'remember_token',
        'row_hash',
        'prev_hash',
        'email_hash',
        
        // Public identifiers
        'clinic_code',
        'clinic_public_id',
        'order_code',
        'order_number',
        'ivr_number',
        'tracking_code',
        'code',
        'token',
        
        // File paths (files are encrypted separately, not the paths)
        'ivr_file',
        'order_file',
        'onboarding_file',
        'filepath',
        'logo',
    ];

    /**
     * Data types that should not be encrypted
     */
    protected array $nonEncryptableTypes = [
        'integer',
        'int',
        'boolean',
        'bool',
        'float',
        'double',
        'array',
        'json',
        'object',
        'date',
        'datetime',
        'timestamp',
    ];

    /**
     * Fields that have a corresponding blind-index hash column.
     * When a field in this map is saved, its hash column is auto-populated.
     */
    protected array $hashedFields = [
        'email' => 'email_hash',
    ];

    public function encryptBeforeSave(Model $model): void
    {
        $attributes = $model->getAttributes();
        $hashService = app(HmacHashService::class);

        foreach ($attributes as $field => $value) {
            // Auto-populate blind-index hash for designated fields BEFORE encrypting
            if (isset($this->hashedFields[$field]) && is_string($value) && $value !== '' && !$this->isEncrypted($value)) {
                $hashColumn = $this->hashedFields[$field];
                $model->setAttribute($hashColumn, $hashService->hash($value));
            }

            if ($this->shouldEncrypt($model, $field, $value)) {
                try {
                    $encryptedValue = Crypt::encryptString((string) $value);
                    $model->setAttribute($field, $encryptedValue);
                    
                    $this->logEncryptionOperation($model, $field, 'encrypt');
                } catch (\Exception $e) {
                    Log::error('Global encryption failed', [
                        'model' => get_class($model),
                        'field' => $field,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \RuntimeException('Data encryption failed: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Decrypt all encrypted data after retrieval from database
     *
     * @param Model $model
     * @return void
     */
    public function decryptAfterRetrieval(Model $model): void
    {
        $attributes = $model->getAttributes();
        $decrypted = [];
        
        foreach ($attributes as $field => $value) {
            if (!is_null($value) && $this->isEncrypted($value) && !$this->isExcludedField($field)) {
                try {
                    $decrypted[$field] = Crypt::decryptString($value);
                } catch (\Exception $e) {
                    Log::error('Global decryption failed', [
                        'model' => get_class($model),
                        'field' => $field,
                        'error' => $e->getMessage(),
                    ]);
                    // Keep original encrypted value on failure
                }
            }
        }

        if (!empty($decrypted)) {
            // Use setRawAttributes with merge=true to avoid triggering dirty tracking,
            // then syncOriginal so Eloquent sees this as the clean DB state.
            $model->setRawAttributes(array_merge($model->getAttributes(), $decrypted), true);
        }
    }

    /**
     * Determine if a field should be encrypted
     *
     * @param Model $model
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function shouldEncrypt(Model $model, string $field, $value): bool
    {
        // Skip if field is in never-encrypt list
        if ($this->isExcludedField($field)) {
            return false;
        }

        // Skip if value is null
        if (is_null($value)) {
            return false;
        }

        // Skip if already encrypted
        if ($this->isEncrypted($value)) {
            return false;
        }

        // Skip non-string values
        if (!is_string($value)) {
            return false;
        }

        // Skip empty strings
        if ($value === '') {
            return false;
        }

        // Check model's casts - skip non-encryptable types
        $casts = $model->getCasts();
        if (isset($casts[$field])) {
            $castType = strtolower($casts[$field]);
            if (in_array($castType, $this->nonEncryptableTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a field is in the never-encrypt list
     *
     * @param string $field
     * @return bool
     */
    protected function isExcludedField(string $field): bool
    {
        return in_array($field, $this->neverEncrypt, true);
    }

    /**
     * Check if a value is already encrypted (Laravel encryption format)
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEncrypted($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Check for Laravel's encryption format: base64 encoded JSON with 'iv', 'value', 'mac'
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $data = json_decode($decoded, true);
        if (!is_array($data)) {
            return false;
        }

        return isset($data['iv']) && isset($data['value']) && isset($data['mac']);
    }

    /**
     * Log encryption operation for audit trail
     *
     * @param Model $model
     * @param string $field
     * @param string $operation
     * @return void
     */
    protected function logEncryptionOperation(Model $model, string $field, string $operation): void
    {
        Log::info('HIPAA Data Encryption', [
            'operation' => $operation,
            'model' => get_class($model),
            'table' => $model->getTable(),
            'field' => $field,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Manually encrypt a value
     *
     * @param string $value
     * @return string
     */
    public function encrypt(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Manually decrypt a value
     *
     * @param string $value
     * @return string
     */
    public function decrypt(string $value): string
    {
        return Crypt::decryptString($value);
    }

    /**
     * Check if encryption is properly configured
     *
     * @return bool
     */
    public function isEncryptionConfigured(): bool
    {
        $key = config('app.key');
        return !empty($key) && strlen($key) >= 32;
    }

    /**
     * Find a model by an encrypted field value.
     * Since fields are encrypted, we cannot use WHERE clause directly.
     * This loads all records and compares decrypted values.
     *
     * @param string $modelClass  Fully-qualified model class name
     * @param string $field       Field name (e.g. 'email')
     * @param string $plainValue  Plain-text value to match
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByEncryptedField(string $modelClass, string $field, string $plainValue): ?\Illuminate\Database\Eloquent\Model
    {
        return $modelClass::all()->first(fn($record) => $record->$field === $plainValue);
    }
}
