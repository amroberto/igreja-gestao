<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Member;
use Barryvdh\DomPDF\PDF;
use App\Models\Community;
use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = Member::with('user')->get();

        return view('admin.members.index', [
            'members' => $members
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $communitys = Community::all();
        return view('admin.members.create', [
            'communitys' => $communitys
        ]);
    }

    private function removeMasks(array $data): array
    {
        $data['rg'] = preg_replace('/\D/', '', $data['rg'] ?? '');
        $data['cpf'] = preg_replace('/\D/', '', $data['cpf'] ?? '');
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

        return redirect()->route('admin.members.index')->with('success', 'Membro cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        try {
            Log::debug('Iniciando carregamento do membro', ['member_id' => $member->id]);
            // Carrega as relações se não estiverem carregadas
            $member->load('contributions', 'community');
            Log::debug('Membro carregado com sucesso', [
                'member_id' => $member->id,
                'birth_date' => $member->birth_date,
                'contributions_count' => $member->contributions->count(),
                'has_community' => $member->community ? true : false
            ]);
            return view('admin.members.show', compact('member'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar membro', [
                'member_id' => $member->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.members.index')->with('error', 'Erro ao carregar membro: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $member = Member::findOrFail($id);
            $communities = Community::all();
            Log::info('Formulário de edição completo do Member ID: ' . $id . ' carregado por usuário ID: ' . Auth::id());
            return view('admin.members.edit', compact('member', 'communities'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição do Member ID: ' . $id . ': ' . $e->getMessage());
            return redirect()->route('admin.members.index')->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        try {
            $member = Member::findOrFail($id);
            $data = $this->removeMasks($request->all());

            $validatedData = validator($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'string', 'email', 'max:255'],
                'cpf' => ['required', 'string', 'unique:members,cpf,' . $id],
                'community_id' => ['required', 'exists:communities,id'],
                'rg' => ['nullable', 'string', 'max:20'],
                'birth_date' => ['nullable', 'date'],
                'phone' => ['nullable', 'string'],
                'cellphone' => ['nullable', 'string'],
                'cep' => ['nullable', 'string'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:10'],
                'complement' => ['nullable', 'string', 'max:50'],
                'district' => ['nullable', 'string', 'max:100'],
                'city' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string'],
                'marital_status' => ['nullable', 'string', 'max:100'],
                'father_name' => ['nullable', 'string', 'max:255'],
                'mother_name' => ['nullable', 'string', 'max:255'],
                'is_active' => ['boolean'],
                'is_contributor' => ['boolean'],
                'delivery_method' => ['nullable', 'string', 'max:100'],
                'payment_method' => ['nullable', 'string', 'max:100'],
                'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
                'observations' => ['nullable', 'string'],
            ])->validate([
                'cpf.required' => 'O campo CPF é obrigatório.',
                'name.required' => 'O campo Nome é obrigatório.',
                'community_id.required' => 'O campo Comunidade é obrigatório.',
                'email.email' => 'O campo E-mail deve ser um endereço de e-mail válido.',
                'rg.max' => 'O campo RG não pode ter mais de 20 caracteres.',
                'birth_date.date' => 'O campo Data de Nascimento deve ser uma data válida.',
                'phone.max' => 'O campo Telefone não pode ter mais de 15 caracteres.',
                'cellphone.max' => 'O campo Celular não pode ter mais de 15 caracteres.',
                'cep.max' => 'O campo CEP não pode ter mais de 10 caracteres.',
                'address.max' => 'O campo Endereço não pode ter mais de 255 caracteres.',
                'number.max' => 'O campo Número não pode ter mais de 10 caracteres.',
                'complement.max' => 'O campo Complemento não pode ter mais de 50 caracteres.',
                'district.max' => 'O campo Bairro não pode ter mais de 100 caracteres.',
                'city.max' => 'O campo Cidade não pode ter mais de 100 caracteres.',
                'state.max' => 'O campo Estado não pode ter mais de 2 caracteres.',
                'marital_status.max' => 'O campo Estado Civil não pode ter mais de 100 caracteres.',
                'father_name.max' => 'O campo Nome do Pai não pode ter mais de 255 caracteres.',
                'mother_name.max' => 'O campo Nome da Mãe não pode ter mais de 255 caracteres.',
                'delivery_method.max' => 'O campo Método de Entrega não pode ter mais de 100 caracteres.',
                'payment_method.max' => 'O campo Método de Pagamento não pode ter mais de 100 caracteres.',
                'due_day.min' => 'O campo Dia de Vencimento deve ser pelo menos 1.',
                'due_day.max' => 'O campo Dia de Vencimento não pode ser maior que 31.',
            ]);

            $member->update($validatedData);

            if ($member->user) {
                $member->user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'community_id' => $request->community_id,
                    'status' => $request->is_active,
                ]);
            }

            Log::info('Member ID: ' . $id . ' atualizado por usuário ID: ' . Auth::id());
            return redirect()->route('admin.members.index')->with('success', 'Membro atualizado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar Member ID: ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar membro: ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        //
    }
    public function pdf(Member $member, PDF $pdf)
    {
        $member->load('contributions.community');
        $pdf = $pdf->loadView('sec.members.pdf', compact('member'));
        return $pdf->download('membro_' . $member->id . '.pdf');
    }
}
