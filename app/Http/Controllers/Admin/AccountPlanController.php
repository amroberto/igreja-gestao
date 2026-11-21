<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountPlan;

class AccountPlanController extends Controller
{
    public function index()
    {
        $accountPlans = AccountPlan::all();
        return view('admin.account_plans.index', compact('accountPlans'));
    }

    public function show(AccountPlan $accountPlan)
    {
        return view('admin.account_plans.show', compact('accountPlan'));
    }

    public function search()
    {
        $search = request()->query('search');
        return redirect()->route('admin.account_plans.index', ['search' => $search]);
    }

    public function getNameByCode($code)
    {
        $accountPlan = AccountPlan::where('code', $code)->first();

        if ($accountPlan) {
            return response()->json(['description' => $accountPlan->description]);
        } else {
            return response()->json(['description' => null]);
        }
    }
}
