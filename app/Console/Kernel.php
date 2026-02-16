<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('results:cleanup')
        ->weeklyOn(0, '3:00')
        ->onOneServer(); // If using multiple servers
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\AssignSubjectsCommand::class,
        \App\Console\Commands\SetDefaultPassword::class,
        \App\Console\Commands\CleanupDeletedStudents::class,
        \App\Console\Commands\CleanupInvalidResults::class,
    ];
}
    
