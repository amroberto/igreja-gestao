<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Models\User;
use App\Models\Member;
use App\Models\Community;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use App\Services\AsaasService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class MemberController extends Controller
{
    protected AsaasService $asaas;

    public function __construct(AsaasService $asaas)
    {
        $this->asaas = $asaas;
    }

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

        $member->first_name = $data['first_name'];
        $member->middle_name = $data['middle_name'] ?? '';
        $member->last_name = $data['last_name'];
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
        $member->amount = number_format((float) $amount, 2, '.', '');

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
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'cpf' => ['required', 'string', 'size:14', 'unique:members,cpf,' . Auth::id()],
                'rg' => ['nullable', 'string', 'max:20'],
                'gender' => ['nullable', 'string', 'in:masculino,feminino,nao_informado'],
                'birth_date' => ['nullable', 'date', 'before:today'],
                'email' => ['required', 'email', 'max:255', 'unique:members,email,' . Auth::id()],
                'phone' => ['nullable', 'string', 'max:20'],
                'cellphone' => ['nullable', 'string', 'max:20'],
                'marital_status' => ['nullable', 'string', 'in:solteiro,casado,divorciado,viuvo'],
                'father_name' => ['nullable', 'string', 'max:255'],
                'mother_name' => ['nullable', 'string', 'max:255'],
                'delivery_method' => ['nullable', 'string', 'in:email,whatsapp,telefone'],
                'payment_method' => ['nullable', 'string', 'in:pix,boleto,cartao_de_credito'],
                'due_day' => ['nullable', 'integer', 'between:1,31'],
                'amount' => ['nullable', 'numeric', 'min:0'],
                'observations' => ['nullable', 'string', 'max:1000'],
                'cep' => ['nullable', 'string', 'max:9'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:20'],
                'complement' => ['nullable', 'string', 'max:100'],
                'district' => ['nullable', 'string', 'max:100'],
                'city' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:2'],
            ]);

            // Remover máscaras dos dados
            $data = $this->removeMasks($validated);

            // Buscar o membro
            $member = Member::where('user_id', Auth::id())->firstOrFail();

            $name = trim("{$data['first_name']} {$data['middle_name']} {$data['last_name']}");
            
            // Preparar payload para o Asaas
            $asaasPayload = [
                'name' => $name,
                'cpfCnpj' => $data['cpf'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'mobilePhone' => $data['cellphone'] ?? null,
                'address' => $data['address'] ?? null,
                'addressNumber' => $data['number'] ?? null,
                'complement' => $data['complement'] ?? null,
                'province' => $data['district'] ?? null,
                'postalCode' => $data['cep'] ?? null,
                'notificationDisabled' => false,
                'externalReference' => (string) $member->id,
                'observations' => $data['observations'] ?? null,
                'foreignCustomer' => false,
                'groupName' => null,
                'company' => null,
            ];

            // Log do payload para depuração
            Log::debug('Payload para atualização no Asaas', [
                'member_id' => $member->id,
                'member_asaas_id' => $member->member_asaas_id,
                'payload' => array_merge(
                    array_diff_key($asaasPayload, array_flip(['cpfCnpj'])),
                    ['cpfCnpj' => substr($data['cpf'] ?? 'N/A', 0, 4) . '****']
                ),
            ]);

            // Atualizar ou criar no Asaas
            if ($member->member_asaas_id) {
                try {
                    $asaasResponse = $this->asaas->updateCustomer($member->member_asaas_id, array_filter($asaasPayload, fn($value) => !is_null($value)));
                    Log::info('Cliente atualizado no Asaas', [
                        'member_id' => $member->id,
                        'member_asaas_id' => $member->member_asaas_id,
                        'response' => $asaasResponse,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Falha ao atualizar cliente no Asaas', [
                        'member_id' => $member->id,
                        'member_asaas_id' => $member->member_asaas_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw new \Exception('Erro ao atualizar cliente no Asaas: ' . $e->getMessage());
                }
            } else {
                Log::warning('Membro sem member_asaas_id, tentando criar cliente no Asaas', [
                    'member_id' => $member->id,
                ]);
                try {
                    $asaasResponse = $this->asaas->createCustomer($asaasPayload);
                    $member->member_asaas_id = $asaasResponse['id'];
                    $member->save();
                    Log::info('Cliente criado no Asaas durante atualização', [
                        'member_id' => $member->id,
                        'member_asaas_id' => $member->member_asaas_id,
                        'external_reference' => $member->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Falha ao criar cliente no Asaas durante atualização', [
                        'member_id' => $member->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw new \Exception('Erro ao criar cliente no Asaas: ' . $e->getMessage());
                }
            }

            // Atualizar os dados no banco local
            $member->update([
                'name' => $data['name'],
                'cpf' => $data['cpf'],
                'rg' => $data['rg'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'cellphone' => $data['cellphone'] ?? null,
                'cep' => $data['cep'] ?? null,
                'address' => $data['address'] ?? null,
                'number' => $data['number'] ?? null,
                'complement' => $data['complement'] ?? null,
                'district' => $data['district'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'delivery_method' => $data['delivery_method'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'due_day' => $data['due_day'] ?? null,
                'amount' => $data['amount'] ?? null,
                'observations' => $data['observations'] ?? null,
            ]);

            Log::info('Dados do membro atualizados com sucesso', [
                'member_id' => $member->id,
                'updated_fields' => array_keys($data),
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
