<?php

use App\Http\Controllers\Api\PesertaController;
use App\Http\Controllers\Api\RegionController;
use Illuminate\Support\Facades\Route;

Route::get('/peserta', [PesertaController::class, 'index']);

Route::prefix('regions')->group(function () {
    Route::get('/kecamatan', [RegionController::class, 'kecamatan']);
    Route::get('/desa', [RegionController::class, 'desa']);
    Route::get('/sls', [RegionController::class, 'sls']);
});
