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
use App\Http\Controllers\Admin\RevenueOrdinaryCategoryController;

Route::middleware(['auth', 'verified', 'access-admin-panel'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('members', MemberController::class)->names('admin.members');
    Route::get('/members/name-by-code/{code}', [MemberController::class, 'getNameByCode'])->name('admin.members.nameByCode');
    
    // Rotas de Relatórios de Contribuição
    Route::get('/contributions/reports/{id}/receipt', [ContributionController::class, 'generateReceipt'])->name('admin.contributions.receipt');
    Route::get('/contributions/report/by-financial-entity', [ContributionController::class, 'reportByFinancialEntity'])->name('admin.contributions.report-by-financial-entity');
    Route::post('/contributions/report/by-financial-entity', [ContributionController::class, 'generateReportByFinancialEntity'])->name('admin.contributions.generate-report-by-financial-entity');
    Route::get('/contributions/report/by-community', [ContributionController::class, 'reportByCommunity'])->name('contributions.reportByCommunity');
    Route::post('/contributions/report/by-community', [ContributionController::class, 'generateReportByCommunity'])->name('admin.contributions.generateReportByCommunity');
    Route::get('/contributions/report/by-period', [ContributionController::class, 'reportByPeriod'])->name('admin.contributions.report.by.period');
    Route::post('/contributions/report/by-period', [ContributionController::class, 'generateReportByPeriod'])->name('admin.contributions.report.by.period.generate');
    
    // Rotas de Baixa de Contribuição por Documento (Formulário Payoff)
    Route::get('/contributions/payoff', [ContributionController::class, 'payoffForm'])->name('admin.contributions.payoff');
    Route::post('/contributions/payoff', [ContributionController::class, 'storePayoff'])->name('admin.contributions.payoff.store');
    Route::get('/contributions/find-pending-doc/{id}', [ContributionController::class, 'findPendingDocument'])->name('admin.contributions.find_pending_doc');

    // Resources
    Route::resource('communities', CommunityController::class)->names('admin.communities');
    Route::resource('parishes', ParishController::class)->names('admin.parishes');
    Route::resource('users', UserController::class)->names('admin.users');
    Route::resource('financial-entities', FinancialEntityController::class)->names('admin.financial_entities');
    Route::resource('contributions', ContributionController::class)->names('admin.contributions');
    
    Route::get('/members/autocomplete', [MemberController::class, 'autocomplete'])->name('admin.members.autocomplete');
    Route::get('/members/{member}/pdf', [MemberController::class, 'pdf'])->name('admin.members.pdf');
    Route::resource('/revenue-ordinary-categories', RevenueOrdinaryCategoryController::class)->names('admin.revenue-ordinary-categories');

    // Rotas para edição da união paroquial
    Route::get('/parochial-union/edit', [ParochialUnionController::class, 'edit'])->name('admin.parochial-union.edit');
    Route::put('/parochial-union', [ParochialUnionController::class, 'update'])->name('admin.parochial-union.update');
});