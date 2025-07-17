<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Member;
use App\Models\User;
use App\Services\Asaas\AsaasCustomerService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        Log::info('Acessando formulário de registro');
        $communities = Community::where('id', '!=', 1)->get();
        return view('auth.register', compact('communities'));
    }

    /**
     * Remove masks from specific input data fields.
     */
    private function removeMasks(array $data): array
    {
        $fields = ['cnpj', 'cep', 'cpf'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D/', '', $data[$field]);
            }
        }
        return $data;
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request, AsaasCustomerService $asaasCustomerService): RedirectResponse
    {
        Log::info('Dados recebidos na requisição de registro', ['request_data' => $request->all()]);

        try {
            // Remover máscaras dos dados
            $data = $this->removeMasks($request->all());

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
                    'unique:users,cpf',
                    'unique:members,cpf,NULL,id,deleted_at,NULL',
                    function ($attribute, $value, $fail) {
                        $member = Member::where('cpf', $value)->first();
                        if ($member && $member->user_id) {
                            $fail('Este CPF já está vinculado a um usuário.');
                        }
                    },
                ],
                'community_id' => ['required', 'exists:communities,id'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'phone' => ['nullable', 'string', 'regex:/^\(\d{2}\)\s?\d{4}-\d{4}$/'],
                'cellphone' => ['nullable', 'string', 'regex:/^\(\d{2}\)\s?\d{5}-\d{4}$/'],
                'cep' => ['nullable', 'string', 'size:8'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:20'],
                'complement' => ['nullable', 'string', 'max:255'],
                'province' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'size:2'],
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
                'password.confirmed' => 'A confirmação da senha não corresponde.',
                'phone.regex' => 'O telefone deve estar no formato (XX) XXXX-XXXX.',
                'cellphone.regex' => 'O celular deve estar no formato (XX) XXXXX-XXXX.',
            ]);

            Log::info('Iniciando registro', [
                'email' => $validated['email'],
                'community_id' => $validated['community_id'],
                'cpf' => substr($data['cpf'], 0, 4) . '****',
            ]);

            return DB::transaction(function () use ($validated, $asaasCustomerService, $data) {
                Log::info('Iniciando transação de registro');

                // Dividir o nome completo
                $fullName = explode(' ', trim($validated['name']));
                $firstName = array_shift($fullName);
                $lastName = count($fullName) > 0 ? array_pop($fullName) : '';
                $middleName = count($fullName) > 0 ? implode(' ', $fullName) : '';

                // Dados do membro
                $memberData = [
                    'cpf' => $data['cpf'],
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => $validated['email'],
                    'community_id' => $validated['community_id'],
                    'phone' => $data['phone'] ?? null,
                    'cellphone' => $data['cellphone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'number' => $data['number'] ?? null,
                    'complement' => $data['complement'] ?? null,
                    'province' => $data['province'] ?? null,
                    'postal_code' => $data['cep'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'is_contributor' => true,
                    'is_active' => true,
                ];

                // Criar ou atualizar o membro
                $member = Member::withTrashed()->where('cpf', $data['cpf'])->first();
                if ($member) {
                    if ($member->trashed()) {
                        $member->restore();
                        Log::info('Membro restaurado', ['member_id' => $member->id]);
                    }
                    Log::info('Atualizando membro existente', ['member_id' => $member->id]);
                    $member->update($memberData);
                } else {
                    Log::info('Criando novo membro', ['cpf' => substr($data['cpf'], 0, 4) . '****']);
                    $member = Member::create($memberData);
                }

                // Criar cliente no Asaas
                $asaasData = [
                    'name' => trim($firstName . ' ' . ($lastName ?? '')),
                    'cpfCnpj' => $data['cpf'],
                    'email' => $validated['email'],
                    'phone' => $data['phone'] ?? null,
                    'mobilePhone' => $data['cellphone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'addressNumber' => $data['number'] ?? null,
                    'complement' => $data['complement'] ?? null,
                    'province' => $data['province'] ?? null,
                    'postalCode' => $data['cep'] ?? null,
                    'notificationDisabled' => false,
                    'externalReference' => (string) $member->id,
                    'foreignCustomer' => false,
                ];
                Log::info('Criando cliente no Asaas', ['member_id' => $member->id, 'asaas_data' => array_merge($asaasData, ['cpfCnpj' => substr($asaasData['cpfCnpj'], 0, 4) . '****'])]);
                $asaasResponse = $asaasCustomerService->createCustomer($asaasData);

                if (!isset($asaasResponse['id'])) {
                    Log::error('Falha ao criar cliente no Asaas', [
                        'member_id' => $member->id,
                        'cpf' => substr($data['cpf'], 0, 4) . '****',
                        'response' => $asaasResponse,
                    ]);
                    throw new \Exception('Erro ao criar cliente no Asaas: ' . (json_encode($asaasResponse) ?: 'Resposta inválida'));
                }
                $member->update(['member_asaas_id' => $asaasResponse['id']]);
                Log::info('Cliente Asaas criado', [
                    'member_id' => $member->id,
                    'member_asaas_id' => $asaasResponse['id'],
                ]);

                // Criar usuário
                Log::info('Criando usuário', ['email' => $validated['email']]);
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'cpf' => $data['cpf'],
                    'community_id' => $validated['community_id'],
                    'user_type' => 'membro',
                ]);
                Log::info('Usuário criado', ['user_id' => $user->id]);

                // Vincular membro ao usuário
                $member->update(['user_id' => $user->id]);
                Log::info('Membro vinculado ao usuário', ['member_id' => $member->id, 'user_id' => $user->id]);

                event(new Registered($user));
                Auth::login($user);

                return redirect()->route('member.profile.edit', $member->id)
                    ->with('success', 'Cadastro realizado! Por favor, complete seu perfil.');
            });
        } catch (ValidationException $e) {
            Log::warning('Erro de validação no cadastro', ['errors' => $e->errors(), 'request_data' => $request->all()]);
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
}