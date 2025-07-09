<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Member;
use App\Models\User;
use App\Rules\ValidCpf;
use App\Services\AsaasService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        Log::info('Acessando método create para exibir formulário de registro');
        $communities = Community::where('id', '!=', 1)->get();
        return view('auth.register', compact('communities'));
    }

    /**
     * Remove masks from input data.
     */
    private function removeMasks(array $data): array
    {
        $fields = ['cnpj', 'phone', 'cellphone', 'cep', 'cpf'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D/', '', $data[$field]);
            }
        }
        return $data;
    }

    /**
     * Handle an incoming registration request.
     */ public function store(Request $request, AsaasService $asaas): RedirectResponse
    {
        try {
            // Remover máscaras dos dados
            $data = $this->removeMasks($request->all());
            Log::info('Iniciando registro', [
                'email' => $data['email'],
                'community_id' => $data['community_id'],
            ]);

            // Validação dos dados
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
                'cpf' => [
                    'required',
                    'string',
                    'size:11',
                    new ValidCpf,
                    function ($attribute, $value, $fail) {
                        $member = \App\Models\Member::where('cpf', $value)->first();
                        if ($member && $member->user_id) {
                            $fail('Este CPF já está vinculado a um usuário.');
                        }
                    },
                ],
                'community_id' => ['required', 'exists:communities,id'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'phone' => ['nullable', 'string', 'size:10'],
                'cellphone' => ['nullable', 'string', 'size:11'],
                'cep' => ['nullable', 'string', 'size:8'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:20'],
                'complement' => ['nullable', 'string', 'max:255'],
                'province' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'size:2'],
            ]);

            // Busca membro pelo CPF
            $member = Member::where('cpf', $data['cpf'])->first();

            if ($member && $member->member_asaas_id) {
                // Verifica se cliente existe na API do Asaas
                $asaasCustomerExists = $asaas->getCustomer($member->member_asaas_id);

                if ($asaasCustomerExists) {
                    // Atualiza cliente existente no Asaas
                    $asaasCustomer = $asaas->updateCustomer($member->member_asaas_id, [
                        'name' => $data['name'],
                        'cpfCnpj' => $data['cpf'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'mobilePhone' => $data['cellphone'] ?? null,
                        'address' => $data['address'] ?? null,
                        'addressNumber' => $data['number'] ?? null,
                        'complement' => $data['complement'] ?? null,
                        'province' => $data['province'] ?? null,
                        'postalCode' => $data['cep'] ?? null,
                        'notificationDisabled' => false,
                        'foreignCustomer' => false,
                    ]);
                } else {
                    // Cliente não existe mais, criar novo
                    $asaasCustomer = $asaas->createCustomer([
                        'name' => $data['name'],
                        'cpfCnpj' => $data['cpf'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'mobilePhone' => $data['cellphone'] ?? null,
                        'address' => $data['address'] ?? null,
                        'addressNumber' => $data['number'] ?? null,
                        'complement' => $data['complement'] ?? null,
                        'province' => $data['province'] ?? null,
                        'postalCode' => $data['cep'] ?? null,
                        'notificationDisabled' => false,
                        'foreignCustomer' => false,
                    ]);
                }
            } else {
                // Nenhum membro ou member_asaas_id, criar novo cliente no Asaas
                $asaasCustomer = $asaas->createCustomer([
                    'name' => $data['name'],
                    'cpfCnpj' => $data['cpf'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'mobilePhone' => $data['cellphone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'addressNumber' => $data['number'] ?? null,
                    'complement' => $data['complement'] ?? null,
                    'province' => $data['province'] ?? null,
                    'postalCode' => $data['cep'] ?? null,
                    'notificationDisabled' => false,
                    'foreignCustomer' => false,
                ]);
            }


            // Separar nome completo em first, middle e last name
            $fullName = explode(' ', trim($data['name']));
            $firstName = array_shift($fullName);
            $lastName = count($fullName) > 0 ? array_pop($fullName) : '';
            $middleName = count($fullName) > 0 ? implode(' ', $fullName) : '';

            return DB::transaction(function () use ($data, $asaasCustomer, $request, $firstName, $middleName, $lastName) {
                // Criar usuário
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'cpf' => $data['cpf'],
                    'community_id' => $data['community_id'],
                    'user_type' => 'membro',
                ]);

                // Verificar se existe um membro com o CPF informado
                $member = Member::where('cpf', $data['cpf'])->first();

                if ($member) {
                    // Vincular o usuário ao membro existente e atualizar nome
                    $member->update([
                        'user_id' => $user->id,
                        'member_asaas_id' => $asaasCustomer['id'],
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'email' => $data['email'],
                        'community_id' => $data['community_id'],
                    ]);
                } else {
                    // Criar novo membro com os campos separados
                    $member = Member::create([
                        'user_id' => $user->id,
                        'cpf' => $data['cpf'],
                        'member_asaas_id' => $asaasCustomer['id'],
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'email' => $data['email'],
                        'community_id' => $data['community_id'],
                    ]);
                }

                // Disparar evento de registro
                event(new Registered($user));

                // Fazer login do usuário
                Auth::login($user);

                return redirect()->route('member.profile.edit', $member->id)
                    ->with('success', 'Cadastro realizado! Por favor, complete seu perfil.');
            });
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Chave de API inválida')) {
                $errorMessage = 'Erro de configuração do serviço de pagamento. Contate o suporte.';
            } elseif (str_contains($errorMessage, 'CPF inválido')) {
                $errorMessage = 'O CPF informado é inválido para o serviço de pagamento.';
            } elseif (str_contains($errorMessage, 'Resposta inválida da API do Asaas')) {
                $errorMessage = 'Erro no serviço de pagamento: dados inválidos. Verifique os dados e tente novamente.';
            } else {
                $errorMessage = 'Erro ao realizar o cadastro. Tente novamente.';
            }

            Log::error('Erro ao registrar usuário', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email,
            ]);

            return back()->withErrors(['error' => $errorMessage]);
        }
    }
}
