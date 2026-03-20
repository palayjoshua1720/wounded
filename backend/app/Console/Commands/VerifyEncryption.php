<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GlobalEncryptionService;
use Illuminate\Support\Facades\Crypt;

class VerifyEncryption extends Command
{
    protected $signature = 'encryption:verify';
    protected $description = 'Verify HIPAA encryption configuration';

    public function handle(GlobalEncryptionService $encryptionService): int
    {
        $this->info('Verifying HIPAA Encryption Configuration...');
        $this->newLine();

        // Check encryption key
        $this->checkEncryptionKey();
        
        // Check cipher
        $this->checkCipher();
        
        // Test encryption/decryption
        $this->testEncryption();
        
        // Check session encryption
        $this->checkSessionEncryption();
        
        // List encrypted models
        $this->listEncryptedModels();

        $this->newLine();
        $this->info('Encryption verification complete!');

        return self::SUCCESS;
    }

    private function checkEncryptionKey(): void
    {
        $key = config('app.key');
        $this->info('1. Encryption Key:');
        
        if (empty($key)) {
            $this->error('   ✗ APP_KEY is not set!');
            $this->warn('   Run: php artisan key:generate');
        } else {
            $this->info('   ✓ APP_KEY is set');
            $this->info('   ✓ Key length: ' . strlen($key) . ' characters');
            $this->info('   ✓ Cipher: ' . config('app.cipher'));
        }
        $this->newLine();
    }

    private function checkCipher(): void
    {
        $this->info('2. Encryption Algorithm:');
        $cipher = config('app.cipher');
        
        if ($cipher === 'AES-256-CBC' || $cipher === 'AES-256-GCM') {
            $this->info('   ✓ Using HIPAA-compliant cipher: ' . $cipher);
        } else {
            $this->warn('   ⚠ Cipher may not meet HIPAA requirements: ' . $cipher);
        }
        $this->newLine();
    }

    private function testEncryption(): void
    {
        $this->info('3. Encryption/Decryption Test:');
        
        try {
            $testData = 'HIPAA Encryption Test - ' . now()->toDateTimeString();
            $encrypted = Crypt::encryptString($testData);
            $decrypted = Crypt::decryptString($encrypted);
            
            if ($decrypted === $testData) {
                $this->info('   ✓ Encryption working correctly');
                $this->info('   ✓ Encrypted length: ' . strlen($encrypted) . ' chars');
            } else {
                $this->error('   ✗ Decryption mismatch!');
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Encryption test failed: ' . $e->getMessage());
        }
        $this->newLine();
    }

    private function checkSessionEncryption(): void
    {
        $this->info('4. Session Encryption:');
        
        if (config('session.encrypt')) {
            $this->info('   ✓ Session encryption enabled');
        } else {
            $this->warn('   ⚠ Session encryption disabled');
            $this->warn('   Set SESSION_ENCRYPT=true in .env');
        }

        if (config('session.secure')) {
            $this->info('   ✓ Secure cookies enabled');
        } else {
            $this->warn('   ⚠ Secure cookies disabled');
        }
        $this->newLine();
    }

    private function listEncryptedModels(): void
    {
        $this->info('5. Models with Encryption Enabled:');
        
        $models = [
            'User',
            'PatientInfo',
            'Clinic',
            'IVR',
            'Orders',
            'Invoices',
            'Manufacturer',
            'Returns',
            'BillerTracking',
            'Brand',
            'GraftSize',
            'UsageLog',
            'Product',
            'OtherProduct',
            'SerialPayment',
            'BackupCode',
            'EmailVerificationCode',
        ];

        foreach ($models as $model) {
            $class = 'App\\Models\\' . $model;
            if (class_exists($class)) {
                if (in_array('App\\Traits\\EncryptsData', class_uses($class))) {
                    $this->info("   ✓ {$model}");
                } else {
                    $this->warn("   ⚠ {$model} (no encryption trait)");
                }
            } else {
                $this->error("   ✗ {$model} (not found)");
            }
        }
    }
}
