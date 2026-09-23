<?php

namespace App\Http\Controllers\Sec;

use App\Http\Controllers\Controller;
use App\Models\AccountPlan;
use Illuminate\Http\Request;

class AccountPlanController extends Controller
{
    public function index()
    {
        $accountPlans = AccountPlan::all();
        return view('sec.account_plans.index', compact('accountPlans'));
    }

    public function show(AccountPlan $accountPlan)
    {
        return view('sec.account_plans.show', compact('accountPlan'));
    }

    public function search()
    {
        $search = request()->query('search');
        return redirect()->route('sec.account_plans.index', ['search' => $search]);
    }

    public function data()
    {
        $accountPlans = AccountPlan::query()->select(['id', 'code', 'description', 'active']);

        return DataTables::of($accountPlans)
            ->addColumn('action', function ($accountPlan) {
                return view('sec.account_plans.partials.actions', compact('accountPlan'));
            })
            ->rawColumns(['action'])
            ->make(true);
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
