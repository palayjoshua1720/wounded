<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string|string>
     */
    protected $commands = [
        // Register your command class
        \App\Console\Commands\VerifyAuditIntegrity::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the audit verification command to run daily at midnight
        $schedule->command('audit:verify')
            ->daily()
            ->at('00:00')
            ->withoutOverlapping() // Prevent multiple overlapping runs
            ->onSuccess(function () {
                Log::info('Audit verification completed successfully.');
            })
            ->onFailure(function () {
                Log::error('Audit verification failed.');
            });

        // Example: other scheduled tasks
        /*
        $schedule->call(function () {
            // Some closure logic
        })->daily()->at('01:00')->description('Some other task');
        */
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        // Optionally require routes/console.php if you have closure commands
        require base_path('routes/console.php');
    }
}