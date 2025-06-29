<?php

namespace App\Http\Controllers\Admin;

use App\Models\Parish;
use App\Models\Community;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class CommunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $communities = Community::all();

        return view('admin.communities.index', [
            'communities' => $communities
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parishes = Parish::all();

        return view('admin.communities.create', [
            'parishes' => $parishes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $community = new Community();

        $community->cnpj = $request->cnpj;
        $community->name = $request->name;
        $community->parish_id = $request->parish_id;
        $community->phone = $request->phone;
        $community->cellphone = $request->cellphone;
        $community->email = $request->email;
        $community->website = $request->website;
        $community->cep = $request->cep;
        $community->address = $request->address;
        $community->number = $request->number;
        $community->complement = $request->complement;
        $community->district = $request->district;
        $community->city = $request->city;
        $community->state = $request->state;

        $community->save();

        return redirect()->route('admin.communities.index')->with('success', 'Comunidade cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Community $community)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Community $community)
    {
        $community = Community::find($community->id);
        $parishes = Parish::all();

        if (is_null($community)) {
            return redirect()->route('admin.communities.index')->with('error', 'Comunidade não encontrada.');
        }
        return view('admin.communities.edit', [
            'community' => $community,
            'parishes' => $parishes
        ]);
    }
    private function removeMasks(array $data): array
    {
        $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj'] ?? '');
        $data['phone'] = preg_replace('/\D/', '', $data['phone'] ?? '');
        $data['cellphone'] = preg_replace('/\D/', '', $data['cellphone'] ?? '');
        $data['cep'] = preg_replace('/\D/', '', $data['cep'] ?? '');
        return $data;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Community $community)
    {
        $data = $this->removeMasks($request->all());

        $validatedData = validator($data, [
            'cnpj' => 'required|string|max:14',
            'name' => 'required|string|max:255',
            'parish_id' => 'required|integer|exists:parishes,id',
            'phone' => 'nullable|string|max:15',
            'cellphone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
        ])->validate([
                'cnpj.required' => 'O campo CNPJ é obrigatório.',
                'name.required' => 'O campo Nome é obrigatório.',
                'parish_id.required' => 'O campo Paróquia é obrigatório.',
                'email.email' => 'O campo E-mail deve ser um endereço de e-mail válido.',
                'website.url' => 'O campo Site deve ser uma URL válida.',
            ]);

        $community->update($validatedData);

        return redirect()->route('admin.communities.index')
            ->with('success', 'Comunidade atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Community $community)
    {
        $community->delete();
        return redirect()->route('admin.communities.index')->with('success', 'Comunidade excluída com sucesso.');
    }
}
