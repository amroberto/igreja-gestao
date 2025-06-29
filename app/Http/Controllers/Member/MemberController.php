<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Models\User;
use App\Models\Member;
use App\Models\Community;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class MemberController extends Controller
{
    /**
     * [Description for index]
     *
     * @return [type]
     *
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Usuário não autenticado.');
        }
        $members = Member::where('user_id', $user->id)->with('community')->get();
        if ($members->isEmpty()) {
            return redirect()->route('member.members.create')->with('error', 'Nenhum membro associado ao usuário.');
        }
        return view('member.members.dashboard', compact('members'));
    }

    public function dashboard()
    {
        return view('member.dashboard');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    private function removeMasks(array $data): array
    {
        $data['rg'] = preg_replace('/\D/', '', $data['rg'] ?? '');
        $data['cpf'] = preg_replace('/\D/', '', $data['cpf'] ?? '');
        $data['birth_date'] = isset($data['birth_date']) ? date('Y-m-d', strtotime($data['birth_date'])) : null;
        $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj'] ?? '');
        $data['phone'] = preg_replace('/\D/', '', $data['phone'] ?? '');
        $data['cellphone'] = preg_replace('/\D/', '', $data['cellphone'] ?? '');
        $data['cep'] = preg_replace('/\D/', '', $data['cep'] ?? '');
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->removeMasks($request->all());
        $member = new Member();

        $member->name = $data['name'];
        $member->cpf = $data['cpf'];
        $member->rg = $data['rg'];
        $member->birth_date = $data['birth_date'];
        $member->gender = $data['gender'];
        $member->email = $data['email'];
        $member->phone = $data['phone'];
        $member->cellphone = $data['cellphone'];
        $member->cep = $data['cep'];
        $member->address = $data['address'];
        $member->number = $data['number'];
        $member->complement = $data['complement'];
        $member->district = $data['district'];
        $member->city = $data['city'];
        $member->state = $data['state'];
        $member->community_id = $data['community_id'];
        $member->is_active = $data['is_active'];
        $member->is_contributor = $data['is_contributor'];
        $member->marital_status = $data['marital_status'];
        $member->father_name = $data['father_name'];
        $member->mother_name = $data['mother_name'];
        $member->delivery_method = $data['delivery_method'];
        $member->payment_method = $data['payment_method'];
        $member->due_day = $data['due_day'];

        $amount = str_replace(['.', ','], ['', '.'], $data['amount']);
        $member->amount = (float) $amount;

        $member->observations = $data['observations'] ?? '';

        $member->save();

        return redirect()->route('member.members.index')->with('success', 'Membro cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $member = Member::findOrFail($id);
            if ($member->user_id !== Auth::id()) {
                Log::warning('Tentativa de acesso não autorizado ao Member ID: ' . $id . ' por usuário ID: ' . Auth::id());
                return redirect()->route('home')->with('error', 'Acesso não autorizado.');
            }

            $communities = Community::all();
            Log::info('Formulário de edição do Member ID: ' . $id . ' carregado para usuário ID: ' . Auth::id());
            return view('member.edit', compact('member', 'communities'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição do Member ID: ' . $id . ': ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */

public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'cpf' => ['required', 'string', 'size:14', 'unique:members,cpf,' . Auth::id()],
                'birth_date' => ['nullable', 'date', 'before:today'],
                'email' => ['required', 'email', 'max:255', 'unique:members,email,' . Auth::id()],
                // Adicione outros campos conforme necessário
            ]);

            $member = Member::where('user_id', Auth::id())->firstOrFail();
            $member->update([
                'name' => $validated['name'],
                'cpf' => $validated['cpf'],
                'birth_date' => $validated['birth_date'] ?? null,
                'email' => $validated['email'],
            ]);

            Log::info('Dados do membro atualizados com sucesso', [
                'member_id' => $member->id,
                'updated_fields' => array_keys($validated),
            ]);

            return redirect()->route('member.member.dashboard')->with('success', 'Dados atualizados com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar dados do membro', [
                'member_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Erro ao atualizar dados: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        //
    }
}
