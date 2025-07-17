<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\CreditCardController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\Member\MemberProfileController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\ContributionController;

Route::middleware(['auth', 'verified'])->prefix('member')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('member.dashboard');
    Route::get('/profile', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    Route::resource('contributions', ContributionController::class)->names('member.contributions')->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('credit-cards', CreditCardController::class)->names('credit-cards');
});