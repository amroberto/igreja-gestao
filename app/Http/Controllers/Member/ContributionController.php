<?php

namespace App\Http\Controllers\Member;

use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;

class ContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $member = Auth::user()->member;
            if (!$member) {
                Log::warning('Usuário ID: ' . Auth::id() . ' não possui Member associado.');
                return redirect()->route('member.dashboard')->with('error', 'Nenhum perfil de membro encontrado.');
            }

            $contributions = Contribution::where('member_id', $member->id)
                ->orderByDesc('created_at')
                ->paginate(10);

            Log::info('Lista de contribuições carregada para Member ID: ' . $member->id);
            return view('member.contributions.index', compact('contributions'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar contribuições para usuário ID: ' . Auth::id() . ': ' . $e->getMessage());
            return redirect()->route('member.dashboard')->with('error', 'Erro ao carregar contribuições.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $communities = Community::where('id', '!=', 1)->get();
        $member = Auth::user()->member;
        if (!$member) {
            Log::warning('Usuário ID: ' . Auth::id() . ' não possui Member associado.');
            return redirect()->route('member.dashboard')->with('error', 'Nenhum perfil de membro encontrado.');
        }
        Log::info('Formulário de criação de contribuição carregado para Member ID: ' . $member->id);
        return view('member.contributions.create', compact('member', 'communities'));
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        $member = Auth::user()->member;
        if (!$member) {
            return redirect()->route('dashboard')->with('error', 'Perfil de membro não encontrado.');
        }

        try {
            $data = $request->validate([
                'community_id' => 'required|exists:communities,id',
                'contribution_date' => 'required|date',
                'period_type' => 'required|in:monthly,range',
                'start_date' => 'required|date_format:Y-m',
                'end_date' => 'nullable|date_format:Y-m|after_or_equal:start_date|required_if:period_type,range',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:pix,creditcard,boleto_bancario',
                'description' => 'nullable|string|max:255',
            ]);

            // Converter start_date e end_date para o primeiro dia do mês
            $data['start_date'] = \Carbon\Carbon::createFromFormat('Y-m', $data['start_date'])->startOfMonth();
            if ($data['period_type'] === 'range' && !empty($data['end_date'])) {
                $data['end_date'] = \Carbon\Carbon::createFromFormat('Y-m', $data['end_date'])->startOfMonth();
            } else {
                $data['end_date'] = null;
            }

            // Criar contribuição
            Contribution::create([
                'member_id' => $member->id,
                'financial_entity_id' => 2,
                'community_id' => $data['community_id'],
                'contribution_date' => $data['contribution_date'],
                'amount' => $data['amount'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'description' => $data['description'],
                'payment_method' => $data['payment_method'],
            ]);

            Log::info('Contribuição registrada com sucesso', [
                'user_id' => Auth::id(),
                'member_id' => $member->id,
                'data' => $data,
            ]);

            return redirect()->route('member.contributions.index')->with('success', 'Contribuição registrada com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao registrar contribuição', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erro ao registrar contribuição: ' . $e->getMessage());
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
        //
    }
}
