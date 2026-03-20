<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HIPAA Compliance Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for HIPAA-compliant encryption and security measures.
    |
    */

    'encryption' => [
        // Enable/disable database field encryption
        'enabled' => env('HIPAA_ENCRYPTION_ENABLED', true),
        
        // Enable/disable file encryption
        'files_enabled' => env('HIPAA_FILE_ENCRYPTION_ENABLED', true),
        
        // Encryption algorithm
        'cipher' => 'AES-256-CBC',
        
        // Key rotation settings
        'key_rotation' => [
            'enabled' => env('HIPAA_KEY_ROTATION_ENABLED', false),
            'interval_days' => env('HIPAA_KEY_ROTATION_DAYS', 365),
        ],
    ],

    'files' => [
        // Storage disk for encrypted files
        'disk' => env('HIPAA_FILE_DISK', 'local'),
        
        // Base directory for encrypted files
        'directory' => 'encrypted',
        
        // Allowed file types for upload
        'allowed_types' => [
            'pdf',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'txt',
        ],
        
        // Maximum file size in MB
        'max_size_mb' => env('HIPAA_MAX_FILE_SIZE', 50),
        
        // Enable secure delete (overwrite before delete)
        'secure_delete' => true,
    ],

    'audit' => [
        // Enable audit logging
        'enabled' => true,
        
        // Log encryption operations
        'log_encryption' => true,
        
        // Log file operations
        'log_file_operations' => true,
        
        // Log decryption access
        'log_decryption_access' => true,
    ],

    'access' => [
        // Require authentication for file access
        'require_auth' => true,
        
        // Session timeout in minutes
        'session_timeout' => env('HIPAA_SESSION_TIMEOUT', 15),
        
        // Maximum login attempts
        'max_login_attempts' => 5,
        
        // Lockout duration in minutes
        'lockout_duration' => 30,
    ],
];
