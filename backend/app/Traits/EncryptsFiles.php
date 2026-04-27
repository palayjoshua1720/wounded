<?php

namespace App\Traits;

use App\Services\FileEncryptionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait EncryptsFiles
{
    /**
     * Get file fields that should be encrypted
     * Override in model: protected array $encryptableFiles = ['ivr_file', 'order_file'];
     */

    /**
     * Boot the trait
     */
    public static function bootEncryptsFiles(): void
    {
        static::saving(function ($model) {
            $model->encryptFileFields();
        });

        static::deleting(function ($model) {
            $model->deleteEncryptedFiles();
        });
    }

    /**
     * Encrypt file fields before saving
     */
    protected function encryptFileFields(): void
    {
        $fileEncryptionService = app(FileEncryptionService::class);
        $encryptableFiles = $this->getEncryptableFiles();

        foreach ($encryptableFiles as $field) {
            // Use getAttributes() directly to bypass decryption overrides and get the raw value
            $file = $this->getAttributes()[$field] ?? $this->$field ?? null;

            if (empty($file)) {
                continue;
            }

            // Skip if already encrypted (path ends in .enc)
            if (is_string($file) && $fileEncryptionService->isEncrypted($file)) {
                continue;
            }

            try {
                $path = $this->getFileStoragePath($field);

                // Handle UploadedFile object
                if ($file instanceof UploadedFile) {
                    $result = $fileEncryptionService->encryptAndStore($file, $path, 'local');
                    $this->attributes[$field] = $result['path'];
                    $this->attributes[$field . '_extension'] = $result['extension'];
                }
                // Handle file path string (file already stored on disk)
                elseif (is_string($file)) {
                    $disk = $this->getFileDisk($file);

                    if ($disk && Storage::disk($disk)->exists($file)) {
                        $fullPath = Storage::disk($disk)->path($file);
                        $result = $fileEncryptionService->encryptAndStore($fullPath, $path, 'local');
                        Storage::disk($disk)->delete($file);
                        $this->attributes[$field] = $result['path'];
                        $this->attributes[$field . '_extension'] = $result['extension'];
                    }
                }
            } catch (\Exception $e) {
                Log::error('File encryption failed in model', [
                    'model' => get_class($this),
                    'field' => $field,
                    'file'  => $file,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Determine which disk a file path belongs to
     */
    protected function getFileDisk(string $path): ?string
    {
        $disks = ['private', 'local', 'public'];
        
        foreach ($disks as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }
        
        return null;
    }

    /**
     * Delete encrypted files when model is deleted
     */
    protected function deleteEncryptedFiles(): void
    {
        $fileEncryptionService = app(FileEncryptionService::class);
        $encryptableFiles = $this->getEncryptableFiles();

        foreach ($encryptableFiles as $field) {
            $path = $this->getOriginal($field) ?? $this->$field;
            
            if (!empty($path) && $fileEncryptionService->isEncrypted($path)) {
                try {
                    $fileEncryptionService->secureDelete($path, 'local');
                } catch (\Exception $e) {
                    Log::error('Failed to delete encrypted file', [
                        'model' => get_class($this),
                        'field' => $field,
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Get list of encryptable file fields
     * Models should define: protected array $encryptableFiles = ['field1', 'field2']
     */
    protected function getEncryptableFiles(): array
    {
        return property_exists($this, 'encryptableFiles') 
            ? $this->encryptableFiles 
            : [];
    }

    /**
     * Get storage path for file field
     */
    protected function getFileStoragePath(string $field): string
    {
        $directory = strtolower(class_basename($this));
        $filename = $this->generateEncryptedFilename($field);
        
        return "encrypted/{$directory}/{$field}/{$filename}";
    }

    /**
     * Generate unique filename for encrypted file
     */
    protected function generateEncryptedFilename(string $field): string
    {
        $timestamp = now()->format('Ymd_His');
        $random = bin2hex(random_bytes(8));
        $id = $this->getKey() ?? 'new';
        
        return "{$id}_{$field}_{$timestamp}_{$random}";
    }

    /**
     * Decrypt and retrieve file contents
     */
    public function getDecryptedFile(string $field): ?array
    {
        $path = $this->$field;
        
        if (empty($path)) {
            return null;
        }

        $fileEncryptionService = app(FileEncryptionService::class);
        
        try {
            return $fileEncryptionService->decryptAndRetrieve($path, 'local');
        } catch (\Exception $e) {
            Log::error('Failed to decrypt file', [
                'model' => get_class($this),
                'field' => $field,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Stream decrypted file for download
     */
    public function streamDecryptedFile(string $field)
    {
        $path = $this->$field;
        
        if (empty($path)) {
            abort(404, 'File not found');
        }

        $fileEncryptionService = app(FileEncryptionService::class);
        
        return $fileEncryptionService->streamDecrypted($path, 'local');
    }

    /**
     * Check if file field has encrypted file
     */
    public function hasEncryptedFile(string $field): bool
    {
        $path = $this->$field;
        
        if (empty($path)) {
            return false;
        }

        $fileEncryptionService = app(FileEncryptionService::class);
        
        return $fileEncryptionService->isEncrypted($path) && 
               Storage::disk('local')->exists($path);
    }

    /**
     * Get file metadata without decrypting
     */
    public function getFileMetadata(string $field): ?array
    {
        $path = $this->$field;
        
        if (empty($path)) {
            return null;
        }

        $fileEncryptionService = app(FileEncryptionService::class);
        
        try {
            return $fileEncryptionService->getMetadata($path, 'local');
        } catch (\Exception $e) {
            return null;
        }
    }
}
