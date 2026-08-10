<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Web3 Wallet API
Route::middleware(['auth'])->prefix('web3')->group(function () {
    Route::get('/config', [\App\Http\Controllers\User\Web3WalletController::class, 'config'])->name('api.web3.config');
    Route::post('/connect', [\App\Http\Controllers\User\Web3WalletController::class, 'connect'])->name('api.web3.connect');
    Route::post('/{wallet}/disconnect', [\App\Http\Controllers\User\Web3WalletController::class, 'disconnect'])->name('api.web3.disconnect');
    Route::post('/{wallet}/set-primary', [\App\Http\Controllers\User\Web3WalletController::class, 'setPrimary'])->name('api.web3.set-primary');
});

// Crypto chart data
Route::middleware(['auth'])->prefix('crypto')->group(function () {
    Route::get('/chart/{symbol}', [\App\Http\Controllers\User\CryptoChartController::class, 'chart'])->name('api.crypto.chart');
    Route::get('/prices', [\App\Http\Controllers\User\CryptoChartController::class, 'prices'])->name('api.crypto.prices');
});
