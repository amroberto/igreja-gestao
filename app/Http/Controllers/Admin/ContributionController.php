<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use App\Models\Community;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contributions = Contribution::with('member')->get();
        return view('admin.contributions.index', compact('contributions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $members = Member::where('is_contributor', true)->orderBy('name')->get();
        $communities = Community::orderBy('name')->get();
        return view('admin.contributions.create', compact('members', 'communities'));
    }

    public function autocomplete(Request $request)
    {
        $term = $request->query('term');
        $members = Member::where('is_contributor', true)
            ->where(function ($query) use ($term) {
                $query->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            })
            ->select('id', 'code', 'name')
            ->take(10)
            ->get();

        return response()->json($members);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'member_id' => ['required', 'exists:members,id'],
                'amount' => ['required', 'numeric', 'min:0'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'description' => ['nullable', 'string'],
            ]);

            Contribution::create($request->all());
            Log::info('Contribuição criada por usuário ID: ' . Auth::id());
            return redirect()->route('admin.contributions.index')->with('success', 'Contribuição registrada.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar contribuição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao registrar contribuição.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contribution = Contribution::findOrFail($id);
        $members = Member::all();
        return view('admin.contributions.edit', compact('contribution', 'members'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $contribution = Contribution::findOrFail($id);
            $request->validate([
                'member_id' => ['required', 'exists:members,id'],
                'amount' => ['required', 'numeric', 'min:0'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'description' => ['nullable', 'string'],
            ]);

            $contribution->update($request->all());
            Log::info('Contribuição ID: ' . $id . ' atualizada por usuário ID: ' . Auth::id());
            return redirect()->route('admin.contributions.index')->with('success', 'Contribuição atualizada.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar contribuição ID: ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar contribuição.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $contribution = Contribution::findOrFail($id);
            $contribution->delete();
            Log::info('Contribuição ID: ' . $id . ' excluída por usuário ID: ' . Auth::id());
            return redirect()->route('admin.contributions.index')->with('success', 'Contribuição excluída.');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir contribuição ID: ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir contribuição.');
        }
    }
}
