<?php

namespace App\Traits;

use App\Services\GlobalEncryptionService;

trait EncryptsData
{
    /**
     * Boot the trait - register model event listeners
     */
    public static function bootEncryptsData(): void
    {
        static::saving(function ($model) {
            app(GlobalEncryptionService::class)->encryptBeforeSave($model);
        });

        static::retrieved(function ($model) {
            app(GlobalEncryptionService::class)->decryptAfterRetrieval($model);
        });
    }

    /**
     * Override getAttribute to decrypt on access
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Decrypt if encrypted and not already decrypted by retrieved event
        if (is_string($value) && $this->isEncryptedValue($value)) {
            try {
                return app(GlobalEncryptionService::class)->decrypt($value);
            } catch (\Exception $e) {
                // If decryption fails, return original value
                return $value;
            }
        }

        return $value;
    }

    /**
     * Override setAttribute to handle encryption
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // Let the parent set the value
        parent::setAttribute($key, $value);

        return $this;
    }

    /**
     * Override attributesToArray to decrypt for serialization
     *
     * @return array
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();
        $encryptionService = app(GlobalEncryptionService::class);

        foreach ($attributes as $key => $value) {
            if (is_string($value) && $this->isEncryptedValue($value)) {
                try {
                    $attributes[$key] = $encryptionService->decrypt($value);
                } catch (\Exception $e) {
                    // Keep encrypted if decryption fails
                }
            }
        }

        return $attributes;
    }

    /**
     * Get the original attributes with decryption
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getOriginal($key = null, $default = null)
    {
        $value = parent::getOriginal($key, $default);

        if ($key === null) {
            // Return all original attributes
            $encryptionService = app(GlobalEncryptionService::class);
            foreach ($value as $k => $v) {
                if (is_string($v) && $this->isEncryptedValue($v)) {
                    try {
                        $value[$k] = $encryptionService->decrypt($v);
                    } catch (\Exception $e) {
                        // Keep encrypted
                    }
                }
            }
            return $value;
        }

        if (is_string($value) && $this->isEncryptedValue($value)) {
            try {
                return app(GlobalEncryptionService::class)->decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Check if a value is encrypted
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEncryptedValue($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

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
     * Temporarily disable encryption for this model instance
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutEncryption(callable $callback)
    {
        // Store current encryption state
        $wasEncrypting = property_exists($this, 'encrypting') ? $this->encrypting : true;
        $this->encrypting = false;
        
        $result = $callback($this);
        
        $this->encrypting = $wasEncrypting;
        return $result;
    }
}
