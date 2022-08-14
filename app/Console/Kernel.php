<?php

namespace App\Console;

use App\Http\Controllers\WebhookShopify;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\Ventas::class,
        
    ];
    /** 
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('salesSync')->everyMinutes();
        //$schedule->command('webhookShopify:productos')->everyTwoHours();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
       
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
