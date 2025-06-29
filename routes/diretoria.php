<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Diretoria\DashboardController;

Route::middleware(['auth', 'verified', 'access-diretoria-panel'])->prefix('diretoria')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('diretoria.dashboard');
});