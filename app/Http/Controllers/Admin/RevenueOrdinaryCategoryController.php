<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RevenueOrdinaryCategory;

class RevenueOrdinaryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = RevenueOrdinaryCategory::all();
        return view('admin.revenue-ordinary-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.revenue-ordinary-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        RevenueOrdinaryCategory::create($request->all());

        return redirect()->route('admin.revenue-ordinary-categories.index')
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
        $category = RevenueOrdinaryCategory::findOrFail($id);
        return view('admin.revenue-ordinary-categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RevenueOrdinaryCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($request->all());
        return redirect()->route('admin.revenue-ordinary-categories.index')
                         ->with('success', 'Categoria de Receita Ordinária atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = RevenueOrdinaryCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.revenue-ordinary-categories.index')
                         ->with('success', 'Categoria de Receita Ordinária excluída com sucesso.');
    }
}
