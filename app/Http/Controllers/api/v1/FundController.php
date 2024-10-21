<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Http\Requests\FilterFundRequest;
use App\Models\Fund;
use App\Services\FundService;
use \Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FundController extends Controller
{
    protected $fundService;

    public function __construct(FundService $fundService)
    {
        $this->fundService = $fundService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(FilterFundRequest $request): JsonResponse
    {

        $funds = $this->fundService->getFundsCache($request);

        return response()->json($funds);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundRequest $request): JsonResponse
    {

        $fund = $this->fundService->create($request);

        return response()->json($fund->load('fundManager', 'fundAliases', 'companies'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Fund $fund): JsonResponse
    {
        // Request can ba cached too

        return response()->json($fund->load(['fundManager', 'fundAliases', 'companies']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundRequest $request, Fund $fund): JsonResponse
    {

        $this->fundService->update($request, $fund);

        return response()->json($fund->load('fundManager', 'fundAliases', 'companies'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fund $fund): JsonResponse
    {

        // Delete the fund
        $this->fundService->delete($fund);

        // Return a 204 No Content response
        return response()->json(['message' => 'Resource deleted successfully!'], 200);
    }

    
    // Not completed
    // fund duplications warning can be saved to database table
    // and then pulled here and send to front end
    public function getDuplications(Request $request): JsonResponse{
        
        // Pull data form database table

        // return fund duplications warning
        return response()->json([], 404);
    }
}
