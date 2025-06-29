<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sec\MemberController;
use App\Http\Controllers\Sec\DashboardController;
use App\Http\Controllers\Sec\ContributionController;

Route::middleware(['auth', 'verified', 'access-sec-panel'])->prefix('sec')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('sec.dashboard');
    Route::resource('/members', MemberController::class)->names('sec.members');
    Route::get('/members/name-by-code/{code}', [MemberController::class, 'getNameByCode'])->name('sec.members.nameByCode');
    Route::resource('contributions', ContributionController::class)->names('sec.contributions');
    Route::get('/contributions/reports/{id}/receipt', [ContributionController::class, 'generateReceipt'])->name('sec.contributions.receipt');
    Route::get('/contributions/report/by-financial-entity', [ContributionController::class, 'reportByFinancialEntity'])->name('sec.contributions.report-by-financial-entity');
    Route::post('/contributions/report/by-financial-entity', [ContributionController::class, 'generateReportByFinancialEntity'])->name('sec.contributions.generate-report-by-financial-entity');
    Route::get('/contributions/report/by-community', [ContributionController::class, 'reportByCommunity'])->name('contributions.reportByCommunity');
    Route::post('/contributions/generate-report-by-community', [ContributionController::class, 'generateReportByCommunity'])->name('contributions.generateReportByCommunity');
    Route::get('/members/{member}/pdf', [MemberController::class, 'pdf'])->name('sec.members.pdf');
});
