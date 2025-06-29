<?php

namespace App\Http\Controllers\Admin;

use App\Models\Community;
use Illuminate\Http\Request;
use App\Models\FinancialEntity;
use App\Http\Controllers\Controller;

class FinancialEntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $financial_entities = FinancialEntity::with('community')->get();

        return view('admin.financial_entities.index', compact('financial_entities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $communities = Community::all();
        
        return view('admin.financial_entities.create')
            ->with('communities', $communities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:financial_entities,name',
            'community_id' => 'required|exists:communities,id',
        ]);

        FinancialEntity::create($request->all());

        return redirect()->route('admin.financial_entities.index')
            ->with('success', 'Entidade financeira criada com sucesso.');
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
        $financial_entity = FinancialEntity::findOrFail($id);
        $communities = Community::all();

        return view('admin.financial_entities.edit', compact('financial_entity', 'communities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:financial_entities,name,' . $id,
            'community_id' => 'required|exists:communities,id',
        ]);

        $financial_entity = FinancialEntity::findOrFail($id);
        $financial_entity->update($request->all());

        return redirect()->route('admin.financial_entities.index')
            ->with('success', 'Entidade financeira atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $financial_entity = FinancialEntity::findOrFail($id);
        $financial_entity->delete();

        return redirect()->route('admin.financial_entities.index')
            ->with('success', 'Entidade financeira excluída com sucesso.');
    }
}
