<?php

// routes/api.php

use App\Http\Controllers\CryptoController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas de criptomonedas
Route::prefix('cryptocurrencies')->group(function () {
    Route::get('/', [CryptoController::class, 'index']);
    Route::get('/search', [CryptoController::class, 'search']);
    Route::post('/track', [CryptoController::class, 'track']);
    Route::delete('/{id}', [CryptoController::class, 'destroy']);
    Route::get('/prices', [CryptoController::class, 'getPrices']);
});

// Rutas de historial
Route::prefix('history')->group(function () {
    Route::get('/{id}', [HistoryController::class, 'show']);
    Route::get('/{id}/range', [HistoryController::class, 'getRange']);
});
