<?php

namespace App\Http\Controllers\Admin;

use App\Models\Parish;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ParishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parishes = Parish::all();

        return view('admin.parishes.index', [
            'parishes' => $parishes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.parishes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cnpj' => ['nullable', 'string', 'max:18'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],
            'cellphone' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'cep' => ['nullable', 'string', 'max:9'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('parish_logos', 'public');
        }

        Parish::create($validated);

        return redirect()->route('admin.parishes.index')->with('success', __('Paróquia cadastrada com sucesso!'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parish = Parish::find($id);

        if (is_null($parish)) {
            abort(404, 'Paróquia não encontrada');
        }

        return view('admin.parishes.show', [
            'parish' => $parish
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $parish = Parish::find($id);

        if (is_null($parish)) {
            abort(404, 'Paróquia não encontrada');
        }

        return view('admin.parishes.edit', [
            'parish' => $parish
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

    public function update(Request $request, Parish $parish)
    {
        // Remove máscaras dos campos antes da validação
        $data = $this->removeMasks($request->all());

        // Validação dos dados
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:14|unique:parishes,cnpj,' . $parish->id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:15',
            'cellphone' => 'nullable|string|max:15',
            'cep' => 'nullable|string|max:8',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ])->validate();

        // Preenche os campos normalmente, exceto 'logo'
        $parish->fill(Arr::except($validated, 'logo'));

        // Se uma nova logo for enviada
        if ($request->hasFile('logo')) {
            // Remove a logo antiga, se existir
            if ($parish->logo && Storage::disk('public')->exists($parish->logo)) {
                Storage::disk('public')->delete($parish->logo);
            }

            // Salva a nova logo
            $path = $request->file('logo')->store('logos', 'public');
            $parish->logo = $path;
        }

        $parish->save();

        return redirect()->route('admin.parishes.index')->with('success', 'Paróquia atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $parish = Parish::find($id);

        if (is_null($parish)) {
            abort(404, __('Paróquia não encontrada'));
        }

        $parish->delete();

        return redirect()->route('admin.parishes.index')->with([
            'color' => 'success',
            'message' => __('Paróquia excluída com sucesso!')
        ]);
    }
}
