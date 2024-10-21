<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarning;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleDuplicateFundWarning implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DuplicateFundWarning $event): void
    {
        // Handle the duplicate fund warning
        $fund = $event->fund;

        // Log the warning 
        // We replace this with any custom logic (like notifying the admin)
        \Log::warning("Duplicate fund warning: A potential duplicate for fund '{$fund->name}' with manager ID {$fund->fund_manager_id} has been detected.");
    }
}
