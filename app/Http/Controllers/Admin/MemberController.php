<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Member;
use Barryvdh\DomPDF\PDF;
use App\Models\Community;
use App\Enums\UserTypeEnum;
use App\Enums\GenderEnum;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\ParochialUnion;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Services\Asaas\AsaasCustomerService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $communities = Community::all();
        $members = Member::where('is_active', true)
            ->with(['community', 'contributions'])
            ->get();

        return view('admin.members.index', [
            'members' => $members
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $communities = Community::all();

        return view('admin.members.create', ['communities' => $communities]);
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
    public function store(Request $request, AsaasCustomerService $asaas)
    {
        $data = $this->removeMasks($request->all());
        $member = new Member();

        $member->first_name = $data['first_name'];
        $member->middle_name = $data['middle_name'] ?? '';
        $member->last_name = $data['last_name'] ?? '';
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
        $member->amount = number_format((float) $amount, 2, '.', '');

        $member->observations = $data['observations'] ?? '';

        $asaasPayload = [
            'name' => trim($data['first_name'] . ' ' . ($data['middle_name'] ?? '') . ' ' . $data['last_name']),
            'cpfCnpj' => $data['cpf'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobilePhone' => $data['cellphone'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['district'] ?? null,
            'postalCode' => $data['cep'] ?? null,
        ];

        // Verifica se já há member com CPF (talvez por outro cadastro anterior)
        $existingMember = Member::where('cpf', $data['cpf'])->whereNotNull('member_asaas_id')->first();

        if ($existingMember) {
            // Se já tem ID Asaas, tentar atualizar no Asaas
            try {
                $asaas->updateCustomer($existingMember->member_asaas_id, $asaasPayload);
                $member->member_asaas_id = $existingMember->member_asaas_id;
            } catch (\Exception $e) {
                Log::warning('Falha ao atualizar cliente Asaas existente.', ['cpf' => $data['cpf'], 'erro' => $e->getMessage()]);
            }
        } else {
            // Criar novo cliente no Asaas
            try {
                $asaasCustomer = $asaas->createCustomer($asaasPayload);
                $member->member_asaas_id = $asaasCustomer['id'] ?? null;
            } catch (\Exception $e) {
                Log::error('Erro ao criar cliente no Asaas ao cadastrar membro.', ['cpf' => $data['cpf'], 'erro' => $e->getMessage()]);
            }
        }

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

            // Carrega a relação community e as últimas 10 contribuições com status confirmed e ordenadas por data descrescente
            $member->load([
                'community',
                'contributions' => function ($query) {
                    $query->where('status', 'confirmed')->orderBy('contribution_date', 'desc')->take(10); // Limita a 10 contribuições
                }
            ]);

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

    public function getNameByCode($id)
    {
        $user = Auth::user();
        $communityId = $user->community_id;

        $member = Member::find($id);

        if (!$member) {
            return response()->json(['name' => ''], 404);
        }

        return response()->json([
            'name' => $member->name,
            'community_id' => $member->community_id,
            'belongs_to_user_community' => $member->community_id == $communityId,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $communities = Community::orderBy('name')->get(); // Ordena por nome para melhor UX

        return view('admin.members.edit', compact('member', 'communities'));
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, Member $member): RedirectResponse
    {
        // Validação direta (se não estiver usando UpdateMemberRequest personalizado)
        $validated = $request->validate([
            'first_name'       => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:100',
            'last_name'        => 'required|string|max:255',
            'cpf'              => 'required|string|max:14|unique:members,cpf,' . $member->id,
            'rg'               => 'nullable|string|max:20',
            'birth_date'       => 'nullable|date',
            'gender' => 'nullable|in:' . implode(',', array_map(fn($case) => $case->value, \App\Enums\GenderEnum::cases())),
            'email'            => 'nullable|email|unique:members,email,' . $member->id,
            'phone'            => 'nullable|string|max:20',
            'cellphone'        => 'nullable|string|max:20',
            'cep'              => 'nullable|string|max:9',
            'address'          => 'nullable|string|max:255',
            'number'           => 'nullable|string|max:20',
            'complement'       => 'nullable|string|max:100',
            'district'         => 'nullable|string|max:100',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:2',
            'community_id'     => 'required|exists:communities,id',  // ← Permite alterar
            'is_active'        => 'boolean',
            'is_contributor'   => 'boolean',
            'marital_status'   => 'nullable|string|max:50',
            'father_name'      => 'nullable|string|max:255',
            'mother_name'      => 'nullable|string|max:255',
            'delivery_method'  => 'nullable|string|max:50',
            'payment_method'   => 'nullable|string|max:50',
            'due_day'          => 'nullable|integer|min:1|max:31',
            'amount'           => 'nullable|numeric|min:0',
            'observations'     => 'nullable|string',
        ]);

        // Remove máscaras se necessário (opcional, já que o JS faz isso)
        $validated = $this->removeMasks($validated);

        // Atualiza o membro
        $member->update($validated);

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Membro atualizado com sucesso.');
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
        $parochialUnion = ParochialUnion::first();

        $member->load('contributions.community');
        $pdf = $pdf->loadView('admin.members.pdf', compact('member', 'parochialUnion'));
        return $pdf->download('membro_' . $member->id . '.pdf');
    }
}
