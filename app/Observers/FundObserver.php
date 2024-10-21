<?php

namespace App\Observers;

use App\Models\Fund;
use App\Events\DuplicateFundWarning;
use App\Services\FundService;
use Illuminate\Support\Facades\Cache;

class FundObserver
{
    protected $fundService;

    /**
     * Create a new observer instance.
     *
     * @param FundService $fundService
     */
    public function __construct(FundService $fundService)
    {
        $this->fundService = $fundService;
    }

    /**
     * Handle the Fund "created" event.
     */
    public function created(Fund $fund): void
    {
        // Clear all cache entries associated with the 'funds' tag
        Cache::tags(['funds'])->flush();

        if ($this->fundService->checkForDuplicates($fund)) {

            // Trigger the duplicate warning event
            event(new DuplicateFundWarning($fund));
        }
    }

    /**
     * Handle the Fund "updated" event.
     */
    public function updated(Fund $fund): void
    {
        // Clear all cache entries associated with the 'funds' tag
        Cache::tags(['funds'])->flush();
    }

    /**
     * Handle the Fund "deleted" event.
     */
    public function deleted(Fund $fund): void
    {
        // Clear all cache entries associated with the 'funds' tag
        Cache::tags(['funds'])->flush();
    }

    /**
     * Handle the Fund "restored" event.
     */
    public function restored(Fund $fund): void
    {
        //
    }

    /**
     * Handle the Fund "force deleted" event.
     */
    public function forceDeleted(Fund $fund): void
    {
        //
    }
}
