<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\FundController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('funds', function () {
    return response()->json([
        'funds' => 100
    ]);
});
Route::prefix('v1')->group(function () {

    // Not completed
    Route::get('/funds/duplications', [FundController::class, 'getDuplications']);

    // Fund Routes
    Route::apiResource('funds', FundController::class);
});
