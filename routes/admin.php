<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ParishController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContributionController;
use App\Http\Controllers\Admin\ParochialUnionController;
use App\Http\Controllers\Admin\FinancialEntityController;

Route::middleware(['auth', 'verified', 'access-admin-panel'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('members', MemberController::class)->names('admin.members');
    Route::resource('communities', CommunityController::class)->names('admin.communities');
    Route::resource('parishes', ParishController::class)->names('admin.parishes');
    Route::resource('users', UserController::class)->names('admin.users');
    Route::resource('financial-entities', FinancialEntityController::class)->names('admin.financial_entities');
    Route::resource('contributions', ContributionController::class)->names('admin.contributions');
    Route::get('/admin/members/autocomplete', [MemberController::class, 'autocomplete'])->name('admin.members.autocomplete');
    Route::get('/members/{member}/pdf', [MemberController::class, 'pdf'])->name('admin.members.pdf');

    // Rotas para edição da união paroquial
    Route::get('/parochial-union/edit', [ParochialUnionController::class, 'edit'])->name('admin.parochial-union.edit');
    Route::put('/parochial-union', [ParochialUnionController::class, 'update'])->name('admin.parochial-union.update');
});
