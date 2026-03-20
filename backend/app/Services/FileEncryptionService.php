<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class FileEncryptionService
{
    /**
     * Encrypt and store a file
     *
     * @param UploadedFile|string $file File to encrypt (UploadedFile or path)
     * @param string $path Storage path
     * @param string $disk Storage disk
     * @return array File metadata
     */
    public function encryptAndStore($file, string $path, string $disk = 'local'): array
    {
        try {
            // Get file contents
            if ($file instanceof UploadedFile) {
                $contents = file_get_contents($file->getRealPath());
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $extension = $file->getClientOriginalExtension();
            } else {
                $contents = file_get_contents($file);
                $originalName = basename($file);
                $mimeType = mime_content_type($file);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
            }

            // Generate encryption metadata
            $encryptionKey = config('app.key');
            $iv = random_bytes(16);
            
            // Encrypt file contents using AES-256-CBC
            $encrypted = openssl_encrypt(
                $contents,
                'AES-256-CBC',
                $encryptionKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encrypted === false) {
                throw new \RuntimeException('File encryption failed');
            }

            // Combine IV + encrypted data + HMAC for integrity
            $hmac = hash_hmac('sha256', $encrypted, $encryptionKey, true);
            $encryptedData = base64_encode($iv . $hmac . $encrypted);

            // Store encrypted file
            $encryptedPath = $path . '.enc';
            Storage::disk($disk)->put($encryptedPath, $encryptedData);

            // Log encryption for audit trail
            Log::info('HIPAA File Encryption', [
                'operation' => 'encrypt',
                'original_name' => $originalName,
                'stored_path' => $encryptedPath,
                'disk' => $disk,
                'size_original' => strlen($contents),
                'size_encrypted' => strlen($encryptedData),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return [
                'path' => $encryptedPath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'disk' => $disk,
                'size_original' => strlen($contents),
                'size_encrypted' => strlen($encryptedData),
                'encrypted' => true,
            ];

        } catch (\Exception $e) {
            Log::error('File encryption failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('File encryption failed: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt and retrieve a file
     *
     * @param string $encryptedPath Path to encrypted file
     * @param string $disk Storage disk
     * @return array File data with contents
     */
    public function decryptAndRetrieve(string $encryptedPath, string $disk = 'local'): array
    {
        try {
            // Read encrypted file
            $encryptedData = Storage::disk($disk)->get($encryptedPath);
            
            if (!$encryptedData) {
                throw new \RuntimeException('Encrypted file not found: ' . $encryptedPath);
            }

            // Decode base64
            $data = base64_decode($encryptedData);
            
            // Extract components (IV + HMAC + encrypted content)
            $iv = substr($data, 0, 16);
            $hmac = substr($data, 16, 32);
            $encrypted = substr($data, 48);

            $encryptionKey = config('app.key');

            // Verify integrity
            $calculatedHmac = hash_hmac('sha256', $encrypted, $encryptionKey, true);
            if (!hash_equals($hmac, $calculatedHmac)) {
                throw new \RuntimeException('File integrity check failed - possible tampering');
            }

            // Decrypt
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $encryptionKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                throw new \RuntimeException('File decryption failed');
            }

            // Log decryption for audit trail
            Log::info('HIPAA File Decryption', [
                'operation' => 'decrypt',
                'path' => $encryptedPath,
                'disk' => $disk,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return [
                'contents' => $decrypted,
                'path' => $encryptedPath,
                'disk' => $disk,
                'size' => strlen($decrypted),
            ];

        } catch (\Exception $e) {
            Log::error('File decryption failed', [
                'path' => $encryptedPath,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('File decryption failed: ' . $e->getMessage());
        }
    }

    /**
     * Stream decrypted file for download
     *
     * @param string $encryptedPath
     * @param string $disk
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamDecrypted(string $encryptedPath, string $disk = 'local')
    {
        $fileData = $this->decryptAndRetrieve($encryptedPath, $disk);

        return response()->stream(function () use ($fileData) {
            echo $fileData['contents'];
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($encryptedPath, '.enc') . '"',
            'Content-Length' => $fileData['size'],
        ]);
    }

    /**
     * Delete encrypted file securely
     *
     * @param string $encryptedPath
     * @param string $disk
     * @return bool
     */
    public function secureDelete(string $encryptedPath, string $disk = 'local'): bool
    {
        try {
            // Overwrite with random data before deletion (secure wipe)
            if (Storage::disk($disk)->exists($encryptedPath)) {
                $size = Storage::disk($disk)->size($encryptedPath);
                $randomData = random_bytes(min($size, 1024 * 1024)); // Max 1MB chunks
                Storage::disk($disk)->put($encryptedPath, $randomData);
            }

            // Delete file
            $result = Storage::disk($disk)->delete($encryptedPath);

            Log::info('HIPAA File Secure Delete', [
                'operation' => 'secure_delete',
                'path' => $encryptedPath,
                'disk' => $disk,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('File secure delete failed', [
                'path' => $encryptedPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if file is encrypted
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function isEncrypted(string $path, string $disk = 'local'): bool
    {
        return str_ends_with($path, '.enc');
    }

    /**
     * Get file metadata without decrypting
     *
     * @param string $encryptedPath
     * @param string $disk
     * @return array
     */
    public function getMetadata(string $encryptedPath, string $disk = 'local'): array
    {
        if (!Storage::disk($disk)->exists($encryptedPath)) {
            throw new \RuntimeException('File not found: ' . $encryptedPath);
        }

        return [
            'path' => $encryptedPath,
            'disk' => $disk,
            'size' => Storage::disk($disk)->size($encryptedPath),
            'last_modified' => Storage::disk($disk)->lastModified($encryptedPath),
            'encrypted' => $this->isEncrypted($encryptedPath),
        ];
    }
}
