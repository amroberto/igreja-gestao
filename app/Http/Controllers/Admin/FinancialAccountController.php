<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;

class FinancialAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $financialAccounts = FinancialAccount::all();
        return view('admin.financial_accounts.index', compact('financialAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $communities = Community::all();

        return view('admin.financial_accounts.create',['communities' => $communities]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'community_id' => 'required|exists:communities,id',
        'name' => 'required|string|max:255',
        // Type, est tipo deve ser com rferencia ao enum AccountTypeEnum metodo getValues, mas aqui está como string para simplificação
        'type' => 'required|in:' . implode(',', AccountTypeEnum::getValues()),
        'bank_name' => 'required|string|max:255',
        'account_number' => 'required|string|max:255',
        'agency_number' => 'required|string|max:255',
        'opening_balance' => 'required|numeric|min:0',
        'opening_balance_date' => 'required|date',
        'is_active' => 'boolean',
        ]);

        FinancialAccount::create($request->all());

        return redirect()->route('admin.financial_accounts.index')
                         ->with('success', 'Financial account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $financialAccount = FinancialAccount::findOrFail($id);
        return view('admin.financial_accounts.show', compact('financialAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $communities = Community::all();

        $financialAccount = FinancialAccount::findOrFail($id);
        return view('admin.financial_accounts.edit', compact('financialAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'community_id' => 'required|exists:communities,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', AccountTypeEnum::getValues()),
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'agency_number' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'opening_balance_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $financialAccount = FinancialAccount::findOrFail($id);
        $financialAccount->update($request->all());

        return redirect()->route('admin.financial_accounts.index')
                         ->with('success', 'Financial account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $financialAccount = FinancialAccount::findOrFail($id);
        $financialAccount->delete();

        return redirect()->route('admin.financial_accounts.index')
                         ->with('success', 'Financial account deleted successfully.');
    }
}
