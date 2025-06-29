<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Presbiterio\MemberController;
use App\Http\Controllers\Presbiterio\DashboardController;

Route::middleware(['auth', 'verified', 'access-presbiterio-panel'])->prefix('presbiterio')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('presbiterio.dashboard');
    Route::resource('members', MemberController::class)->names('presbiterio.members');
});