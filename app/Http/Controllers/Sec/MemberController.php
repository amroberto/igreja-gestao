<?php

namespace App\Http\Controllers\Sec;

use App\Models\User;

use App\Models\Member;
use Barryvdh\DomPDF\PDF;
use App\Enums\GenderEnum;
use App\Models\Community;
use App\Enums\UserTypeEnum;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Models\ParochialUnion;
use App\Services\AsaasCustomerService;
use App\Enums\MaritalStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\DeliveryMethodEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\UpdateMemberRequest;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $communities = $user->communities;
        $members = Member::whereIn('community_id', $communities->pluck('id'))
            ->with(['community', 'contributions'])
            ->get();

        return view('sec.members.index', [
            'members' => $members
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $community = Auth::user()->community;

        return view('sec.members.create', ['community' => $community]);
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

        return redirect()->route('sec.members.index')->with('success', 'Membro cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        try {
            Log::debug('Iniciando carregamento do membro', ['member_id' => $member->id]);

            // Carrega a relação community e as últimas 5 contribuições ordenadas por data descrescente
            $member->load([
                'community',
                'contributions' => function ($query) {
                    $query->orderBy('contribution_date', 'desc')->take(5); // Limita a 5 contribuições
                }
            ]);

            Log::debug('Membro carregado com sucesso', [
                'member_id' => $member->id,
                'birth_date' => $member->birth_date,
                'contributions_count' => $member->contributions->count(),
                'has_community' => $member->community ? true : false
            ]);

            return view('sec.members.show', compact('member'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar membro', [
                'member_id' => $member->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('sec.members.index')->with('error', 'Erro ao carregar membro: ' . $e->getMessage());
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
        return view('sec.members.edit', compact('member'));
    }

    /**
     * Update the specified member in storage.
     *
     * @param UpdateMemberRequest $request
     * @param Member $member
     * @return RedirectResponse
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        return redirect()->route('sec.members.index')->with('success', __('Membro atualizado com sucesso!'));
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
        $pdf = $pdf->loadView('sec.members.pdf', compact('member', 'parochialUnion'));
        return $pdf->download('membro_' . $member->id . '.pdf');
    }
}
