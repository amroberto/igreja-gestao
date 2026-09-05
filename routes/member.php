<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\CreditCardController;
use App\Http\Controllers\Member\ContributionController;
use App\Http\Controllers\Member\MemberProfileController;

Route::middleware(['auth', 'verified'])->prefix('member')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('member.dashboard');
    Route::get('/profile', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    
    // Rota para a popup do PIX
    Route::get('/contributions/{contribution}/pix', [ContributionController::class, 'showPix'])->name('member.contributions.pix');
    
    Route::get('/contributions/{contribution}/boleto', [ContributionController::class, 'showBoleto'])->name('member.contributions.boleto');
    
    // Rota para atualizar o vencimento/valor de cobrança vencida (DEVE vir antes do resource)
    Route::put('/contributions/{contribution}/update-due-date', [ContributionController::class, 'updateDueDate'])->name('member.contributions.update-due-date');

    // Resource de Contribuições
    Route::resource('contributions', ContributionController::class)->names('member.contributions')->only(['index', 'create', 'store', 'edit', 'update']);
    
    Route::resource('credit-cards', CreditCardController::class)->names('credit-cards');
    Route::post('/member/credit-cards', [ContributionController::class, 'storeCreditCard'])->name('member.credit-cards.store');
    Route::post('/contributions/save-card', [ContributionController::class, 'saveCard'])->name('member.contributions.save-card');
});