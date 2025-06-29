<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\ContributionController;

Route::middleware(['auth', 'verified'])->prefix('member')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('member.dashboard');
    Route::get('/edit/{id}', [MemberController::class, 'edit'])->name('member.edit');
    Route::put('/update/{id}', [MemberController::class, 'update'])->name('member.update');
    Route::resource('contributions', ContributionController::class)->names('member.contributions')->only(['index', 'create', 'store', 'edit', 'update']);
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    Route::get('/profile', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
    Route::patch('/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
});
