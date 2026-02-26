<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\UsageLog;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic expiry status update - runs daily at midnight
Schedule::call(function () {
    $updated = UsageLog::where('expired_at', '<', now()->startOfDay())
        ->where('log_status', '!=', 6) // Not already expired
        ->whereNotIn('log_status', values: [2, 3]) // Exclude used (2) and partially_used (3)
        ->update([
            'log_status' => 6, // Set to expired
            'updated_at' => now()
        ]);
    
    Log::info("Auto-expiry check completed. Updated {$updated} items to expired status.");
})->daily()->at('00:00')->description('Auto-update expired inventory items status');

// Manual command to run expiry check immediately
Artisan::command('inventory:check-expiry', function () {
    $updated = UsageLog::where('expired_at', '<', now()->startOfDay())
        ->where('log_status', '!=', 6)
        ->whereNotIn('log_status', [2, 3])
        ->update([
            'log_status' => 6,
            'updated_at' => now()
        ]);
    
    $this->info("Expiry check completed. Updated {$updated} items to expired status.");
})->purpose('Manually check and update expired inventory items');
