<?php

namespace App\Services;

use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Http\Requests\FilterFundRequest;
use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use PHPUnit\Util\Filter;

class FundService
{

    /**
     * Get filtered and paginated funds.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFunds(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {

        $query = Fund::with(['fundManager', 'fundAliases', 'companies'])
            ->filterByName($request->name)
            ->filterByFundManager($request->fund_manager)
            ->filterByStartYear($request->start_year)
            ->paginate($request->get('per_page', 10));

        return $query;
    }

    /**
     * Get filtered and paginated funds.
     *
     * @param array $filters
     * @param FilterFundRequest $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFundsCache(FilterFundRequest $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Define a cache key.
        $cacheKey = 'funds_index_' . md5(serialize($request->all()));

        // can be defined also globally
        $cacheDuration = 60;

        $funds = Cache::tags(['funds'])->remember($cacheKey, $cacheDuration, function () use ($request) {
            return Fund::with(['fundManager', 'fundAliases', 'companies'])
                ->filterByName($request->name)
                ->filterByFundManager($request->fund_manager)
                ->filterByStartYear($request->start_year)
                ->paginate($request->get('per_page', 10));
        });

        return $funds;
    }

    /**
     * Create a new fund, its aliases and attach companies.
     *
     * @param StoreFundRequest $request
     * @return Fund
     */
    public function create(StoreFundRequest $request): Fund
    {
        // Start the transaction
        DB::beginTransaction();

        try {
            $fundData = $request->only(['name', 'start_year', 'fund_manager_id']);  

            // Create the Fund
            $fund = Fund::create($fundData);

            // Create aliases
            $this->createAliases($fund, $request->aliases);

            // Attach companies
            $this->attachCompanies($fund, $request->companies);
            
            // Log success
            Log::info("{$request->name} created successfully!");

            // Commit the transaction if everything is successful
            DB::commit();

            return $fund;

        }catch (\Exception $e) {
            // Rollback the transaction if any query fails
            DB::rollBack();
    
            // throw an exception
            throw new Exception("Error creating fund: {$request->name}, {$e->getMessage()}");
        }
    }

    /**
     * Update a fund, its aliases and attach companies.
     *
     * @param StoreFundRequest $request
     * @return Fund
     */
    public function update(UpdateFundRequest $request, Fund $fund): Fund
    {
        $fundData = $request->only(['name', 'start_year', 'fund_manager_id']);  

        // Start the transaction
        DB::beginTransaction();

        try {

            // Update the Fund
            $fund->update($fundData);

            // Update aliases
            $this->updateAliases($fund, $request->aliases);

            // Sync companies
            $this->syncCompanies($fund, $request->companies);

            // Commit the transaction if everything is successful
            DB::commit();

            // Log success
            Log::info("{$request->name} updated successfully!");

            return $fund;

        }catch (\Exception $e) {
            // Rollback the transaction if any query fails
            DB::rollBack();
    
            // throw an exception
            throw new Exception("Error updating fund: {$request->name}, {$e->getMessage()}");
        }
    }

    /**
     * Delete a fund.
     *
     * @param Fund $fund
     * @return void
     */
    public function delete(Fund $fund): void
    {
        $fund->delete();
    }
    /**
     * Create multiple aliases for the fund.
     *
     * @param Fund $fund
     * @param array $aliases
     * @return void
     */
    public function createAliases(Fund $fund, array $aliases = []): void
    {
        if (!empty($aliases)) {
            $aliasesData = array_map(function ($aliasName) {
                return ['name' => $aliasName];
            }, $aliases);

            $fund->fundAliases()->createMany($aliasesData);
        }
    }

    /**
     * Create multiple aliases for the fund.
     *
     * @param Fund $fund
     * @param array $aliases
     * @return void
     */
    public function updateAliases(Fund $fund, array $aliases = []): void
    {
           
        if (!empty($aliases)) {
            $fund->fundAliases()->delete();
            foreach ($aliases as $aliasName) {
                $fund->fundAliases()->create(['name' => $aliasName]);
            }
        }
    }


    /**
     * Attach companies associated with the fund.
     * @param Fund $fund
     * @param array $companies
     * @return void
     */
    public function attachCompanies(Fund $fund, array $companies = []): void
    {
        if (!empty($companies)) {
            $fund->companies()->attach($companies);
        }
    }

    /**
     * Sync the fund's associated companies.
     *
     * @param Fund $fund
     * @param array $companyIds
     * @return void
     */
    public function syncCompanies(Fund $fund, array $companyIds = []): void
    {
        if (!empty($companyIds)) {
            $fund->companies()->sync($companyIds);
        }
    }

    /**
     * Check for potential duplicate funds.
     * Return true if there are duplicates
     *
     * @param Fund $fund
     * @return bool
     */
    public function checkForDuplicates(Fund $fund): bool
    {
        $aliases = $fund->fundAliases->pluck('name')->toArray();

        $duplicates = Fund::where('fund_manager_id', $fund->fund_manager_id)
            ->where(function ($q1) use ($fund, $aliases) {
                $q1->where('name', $fund->name)
                    ->orWhereIn('name', $aliases)
                    ->orWhereHas('fundAliases', function ($q2) use ($fund, $aliases) {
                        $q2->where('name', $fund->name)
                            ->orWhereIn('name', $aliases);
                    });
            })
            ->where('id', '!=', $fund->id)
            ->get();

        return $duplicates->isNotEmpty();
    }

}
