<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sec\MemberController;
use App\Http\Controllers\Sec\DashboardController;
use App\Http\Controllers\Sec\ContributionController;
use App\Http\Controllers\Sec\MonthlyConfigController;

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
    Route::get('/members/data', [MemberController::class, 'data'])->name('sec.members.data');
    Route::get('/monthly-configs', [MonthlyConfigController::class, 'index'])->name('sec.monthly-configs.index');
    Route::get('/monthly-configs/create', [MonthlyConfigController::class, 'create'])->name('sec.monthly-configs.create');
    Route::post('/monthly-configs', [MonthlyConfigController::class, 'store'])->name('sec.monthly-configs.store');
    Route::get('/monthly-configs/{monthlyConfig}/edit', [MonthlyConfigController::class, 'edit'])->name('sec.monthly-configs.edit');
    Route::put('/monthly-configs/{monthlyConfig}', [MonthlyConfigController::class, 'update'])->name('sec.monthly-configs.update');
    Route::delete('/monthly-configs/{monthlyConfig}', [MonthlyConfigController::class, 'destroy'])->name('sec.monthly-configs.destroy');
    Route::get('/monthly-configs/{monthlyConfig}', [MonthlyConfigController::class, 'show'])->name('sec.monthly-configs.show');
    Route::get('/monthly-configs/{monthlyConfig}/preview', [MonthlyConfigController::class, 'preview'])->name('sec.monthly-configs.preview');
    Route::get('/monthly-configs/{monthlyConfig}/pdf', [MonthlyConfigController::class, 'pdf'])->name('sec.monthly-configs.pdf');
    Route::post('/monthly-configs/upload-image', [MonthlyConfigController::class, 'uploadImage'])->name('sec.monthly-configs.upload-image');
    
});
