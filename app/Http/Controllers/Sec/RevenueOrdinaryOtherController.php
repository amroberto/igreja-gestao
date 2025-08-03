<?php

namespace App\Http\Controllers\Sec;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RevenueOrdinaryOther;

class RevenueOrdinaryOtherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = RevenueOrdinaryOther::all();
        return view('sec.revenue-ordinary-category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sec.revenue-ordinary-category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        RevenueOrdinaryOther::create($request->all());

        return redirect()->route('sec.revenue-ordinary-categories.index')
                         ->with('success', 'Outra Categoria de Receita Ordinária criada com sucesso.');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = RevenueOrdinaryOther::findOrFail($id);
        $category->delete();

        return redirect()->route('sec.revenue-ordinary-categories.index')
                         ->with('success', 'Categoria de Receita Ordinária excluída com sucesso.'); 
    }
}
