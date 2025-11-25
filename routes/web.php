<?php

use App\Http\Controllers\CheckinController;
use Illuminate\Support\Facades\Route;

// Rotas para pacientes (interface simplificada)
Route::middleware(['web'])->group(function () {
    Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');
    Route::post('/checkin', [CheckinController::class, 'store'])->name('checkin.store');
    Route::get('/checkin/success', [CheckinController::class, 'success'])->name('checkin.success');
});
