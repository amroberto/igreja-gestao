<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Member;
use App\Models\User;
use App\Rules\ValidCpf;
use App\Services\Asaas\AsaasCustomerService;
use App\Enums\DeliveryMethodEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\UserTypeEnum;
use App\Enums\StatusMemberEnum;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        Log::info('Acessando formulário de registro');
        $communities = Community::where('id', '!=', 1)->get();
        return view('auth.register', [
            'communities' => $communities,
            'deliveryMethods' => DeliveryMethodEnum::cases(),
            'paymentMethods' => PaymentMethodEnum::cases(),
        ]);
    }

    public function register(Request $request, AsaasCustomerService $asaasCustomerService): RedirectResponse
    {
        Log::info('Dados recebidos no register', ['request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    'unique:users,email',
                    'unique:members,email,NULL,id,deleted_at,NULL',
                ],
                'cpf' => [
                    'required',
                    'string',
                    'size:11',
                    new ValidCpf,
                    'unique:users,cpf',
                    'unique:members,cpf,NULL,id,deleted_at,NULL',
                ],
                'community_id' => ['required', 'exists:communities,id'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'phone' => ['nullable', 'string', 'size:10'],
                'cellphone' => ['nullable', 'string', 'size:11'],
                'cep' => ['nullable', 'string', 'size:8'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:20'],
                'complement' => ['nullable', 'string', 'max:255'],
                'district' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'size:2', 'in:AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO'],
                'delivery_method' => ['required', Rule::in(DeliveryMethodEnum::getValues())],
                'payment_method' => ['required', Rule::in(PaymentMethodEnum::getValues())],
                'due_day' => ['required', 'integer', 'between:1,31'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'is_active' => ['nullable', 'boolean'],
            ], [
                'name.required' => 'O nome completo é obrigatório.',
                'name.regex' => 'O nome deve conter apenas letras e espaços.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.unique' => 'Este e-mail já está cadastrado.',
                'cpf.required' => 'O CPF é obrigatório.',
                'cpf.size' => 'O CPF deve ter 11 dígitos.',
                'cpf.unique' => 'Este CPF já está cadastrado.',
                'community_id.required' => 'A comunidade é obrigatória.',
                'community_id.exists' => 'A comunidade selecionada não existe.',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
                'password.confirmed' => 'A confirmação da senha não confere.',
                'delivery_method.required' => 'O método de entrega é obrigatório.',
                'delivery_method.in' => 'O método de entrega selecionado é inválido.',
                'payment_method.required' => 'O método de pagamento é obrigatório.',
                'payment_method.in' => 'O método de pagamento selecionado é inválido.',
                'due_day.required' => 'O dia de vencimento é obrigatório.',
                'due_day.between' => 'O dia de vencimento deve estar entre 1 e 31.',
                'amount.required' => 'O valor da contribuição é obrigatório.',
                'amount.min' => 'O valor da contribuição deve ser maior que zero.',
            ]);

            $cpf = preg_replace('/\D/', '', $validated['cpf']);
            Log::info('Iniciando registro', [
                'email' => $validated['email'],
                'community_id' => $validated['community_id'],
                'cpf' => substr($cpf, 0, 4) . '****',
                'delivery_method' => $validated['delivery_method'],
                'payment_method' => $validated['payment_method'],
            ]);

            DB::beginTransaction();

            // Dividir o nome
            $nameParts = array_filter(explode(' ', trim($validated['name'])));
            $firstName = array_shift($nameParts) ?? '';
            $lastName = array_pop($nameParts) ?? '';
            $middleName = !empty($nameParts) ? implode(' ', $nameParts) : null;

            // Criar usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'cpf' => $cpf,
                'user_type' => UserTypeEnum::MEMBER->value,
                'community_id' => $validated['community_id'],
            ]);
            Log::info('Usuário criado', ['user_id' => $user->id]);

            // Criar ou atualizar membro
            $member = Member::withTrashed()->where('cpf', $cpf)->first();
            $memberData = [
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'cpf' => $cpf,
                'email' => $validated['email'],
                'user_id' => $user->id,
                'community_id' => $validated['community_id'],
                'is_active' => $validated['is_active'] ?? true,
                'is_contributor' => true,
                'status' => StatusMemberEnum::ATIVO,
                'delivery_method' => $validated['delivery_method'],
                'payment_method' => $validated['payment_method'],
                'due_day' => $validated['due_day'],
                'amount' => $validated['amount'],
                'phone' => $validated['phone'] ?? null,
                'cellphone' => $validated['cellphone'] ?? null,
                'address' => $validated['address'] ?? null,
                'number' => $validated['number'] ?? null,
                'complement' => $validated['complement'] ?? null,
                'postal_code' => $validated['cep'] ?? null,
                'district' => $validated['district'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
            ];

            if ($member) {
                if ($member->trashed()) {
                    $member->restore();
                    Log::info('Membro restaurado', ['member_id' => $member->id]);
                }
                $member->update($memberData);
                Log::info('Membro atualizado', ['member_id' => $member->id]);
            } else {
                $member = Member::create($memberData);
                Log::info('Membro criado', ['member_id' => $member->id]);
            }

            // Criar cliente no Asaas
            $asaasData = $this->mapMemberToAsaasPayload($member);
            Log::info('Criando cliente no Asaas', ['member_id' => $member->id, 'asaas_data' => $asaasData]);
            $asaasResponse = $asaasCustomerService->createCustomer($asaasData);
            if (!isset($asaasResponse['id'])) {
                Log::error('Falha ao criar cliente no Asaas', [
                    'member_id' => $member->id,
                    'cpf' => substr($cpf, 0, 4) . '****',
                    'response' => $asaasResponse,
                ]);
                throw new \Exception('Erro ao criar cliente no Asaas: ' . (json_encode($asaasResponse) ?: 'Resposta inválida'));
            }
            $member->update(['member_asaas_id' => $asaasResponse['id']]);
            Log::info('Cliente Asaas criado', [
                'member_id' => $member->id,
                'member_asaas_id' => $asaasResponse['id'],
            ]);

            DB::commit();

            Auth::login($user);
            event(new Registered($user));

            return redirect()->route('member.profile.edit', $member->id)
                ->with('success', 'Usuário registrado! Pedimos que complemente seus dados cadastrais.');

        } catch (ValidationException $e) {
            Log::warning('Erro de validação no cadastro', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            $userMessage = match (true) {
                str_contains($errorMessage, '401 Unauthorized') => 'Erro de autenticação com o serviço de pagamento. Contate o suporte.',
                str_contains($errorMessage, 'cpfCnpj') || str_contains($errorMessage, 'CPF inválido') => 'O CPF informado é inválido para o serviço de pagamento.',
                str_contains($errorMessage, 'Erro ao criar cliente no Asaas') => 'Erro ao integrar com o serviço de pagamento: ' . $errorMessage,
                str_contains($errorMessage, 'Duplicate entry') => 'E-mail ou CPF já cadastrado no sistema.',
                default => 'Erro ao realizar o cadastro: ' . $errorMessage,
            };

            Log::error('Erro ao registrar usuário', [
                'message' => $errorMessage,
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'N/A',
                'cpf' => substr($request->cpf ?? 'N/A', 0, 4) . '****',
                'request_data' => $request->all(),
            ]);

            return back()->withErrors(['error' => $userMessage])->withInput();
        }
    }

    private function mapMemberToAsaasPayload(Member $member): array
    {
        return [
            'name' => trim($member->first_name . ' ' . ($member->last_name ?? '')),
            'cpfCnpj' => $member->cpf,
            'email' => $member->email,
            'phone' => $member->phone,
            'mobilePhone' => $member->cellphone,
            'address' => $member->address,
            'addressNumber' => $member->number,
            'complement' => $member->complement,
            'province' => $member->state,
            'postalCode' => $member->postal_code,
            'notificationDisabled' => $member->delivery_method === 'correio',
            'externalReference' => (string) $member->id,
            'observations' => $member->observations,
        ];
    }
}
