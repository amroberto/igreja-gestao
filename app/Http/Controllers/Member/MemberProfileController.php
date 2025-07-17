<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Models\Community;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Services\Asaas\AsaasCustomerService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MemberProfileController extends Controller
{
    private const MAX_255 = 'max:255';

    private function removeMasks(array $data): array
    {
        $fields = ['rg', 'cpf', 'phone', 'cellphone', 'cep'];
        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/\D/', '', $data[$field]);
            }
        }
        return $data;
    }

    public function edit()
    {
        $member = Auth::user()->member;
        $communities = Community::all();
        if (!$member) {
            Log::warning('Membro não encontrado para o usuário', ['user_id' => Auth::user()->id]);
            return redirect()->route('member.dashboard')->withErrors(['error' => 'Membro não encontrado.']);
        }
        return view('member.profile.edit', compact('member', 'communities'));
    }

    /**
     * Valida os dados da requisição.
     *
     * @param Request $request
     * @param User $user
     * @param Member $member
     * @return array
     * @throws ValidationException
     */
    private function validateRequest(Request $request, User $user, Member $member): array
    {
        Log::debug('Validando dados da requisição', ['request_data' => $request->all()]);

        $rules = [
            'first_name' => ['required', 'string', self::MAX_255, 'regex:/^[A-Za-zÀ-ÿ\s]+$/'],
            'middle_name' => ['nullable', 'string', self::MAX_255, 'regex:/^[A-Za-zÀ-ÿ\s]*$/'],
            'last_name' => ['required', 'string', self::MAX_255, 'regex:/^[A-Za-zÀ-ÿ\s]*$/'],
            'cpf' => [
                'required',
                'string',
                'size:11',
                'unique:users,cpf,' . ($user->id ?? 'NULL') . ',id',
                'unique:members,cpf,' . $member->id . ',id,deleted_at,NULL',
            ],
            'gender' => ['nullable', 'in:masculino,feminino,outro'],
            'rg' => ['nullable', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                self::MAX_255,
                'unique:users,email,' . ($user->id ?? 'NULL') . ',id',
                'unique:members,email,' . $member->id . ',id,deleted_at,NULL',
            ],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string'],
            'cellphone' => ['nullable', 'string'],
            'cep' => ['nullable', 'string', 'size:8'],
            'address' => ['nullable', 'string', self::MAX_255],
            'number' => ['nullable', 'string', 'max:10'],
            'complement' => ['nullable', 'string', self::MAX_255],
            'district' => ['nullable', 'string', self::MAX_255],
            'city' => ['nullable', 'string', self::MAX_255],
            'state' => ['nullable', 'string', 'size:2'],
            'marital_status' => ['nullable', 'in:solteiro,casado,divorciado,viuvo'],
            'father_name' => ['nullable', 'string', self::MAX_255],
            'mother_name' => ['nullable', 'string', self::MAX_255],
            'community_id' => ['nullable', 'exists:communities,id'],
        ];

        $messages = [
            'first_name.required' => 'O primeiro nome é obrigatório.',
            'first_name.regex' => 'O primeiro nome deve conter apenas letras e espaços.',
            'middle_name.regex' => 'O nome do meio deve conter apenas letras e espaços.',
            'last_name.required' => 'O sobrenome é obrigatório.',
            'last_name.regex' => 'O sobrenome deve conter apenas letras e espaços.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'community_id.exists' => 'A comunidade selecionada não existe.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
        ];

        $validated = $request->validate($rules, $messages);
        Log::debug('Validação concluída com sucesso', ['validated' => $validated]);

        return $this->removeMasks($request->all());
    }

    /**
     * Atualiza o modelo Member.
     *
     * @param Member $member
     * @param array $validated
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function updateMember(Member $member, array $validated, array $data): bool
    {
        $memberData = [
            'cpf' => $data['cpf'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'] ?? null,
            'rg' => $data['rg'] ?? null,
            'email' => $validated['email'],
            'birth_date' => $validated['birth_date'] ?? null,
            'phone' => $data['phone'] ?? null,
            'cellphone' => $data['cellphone'] ?? null,
            'cep' => $data['cep'] ?? null,
            'address' => $validated['address'] ?? null,
            'number' => $validated['number'] ?? null,
            'complement' => $validated['complement'] ?? null,
            'district' => $validated['district'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'community_id' => $validated['community_id'] ?? null,
        ];

        Log::info('Atualizando membro no banco', ['member_id' => $member->id, 'member_data' => $memberData]);
        $saved = $member->update($memberData);

        if (!$saved) {
            Log::error('Falha ao salvar o modelo Member', ['member_id' => $member->id, 'data' => $memberData]);
            throw new \Exception('Falha ao salvar os dados do membro.');
        }

        return true;
    }

    /**
     * Atualiza o modelo User.
     *
     * @param User $user
     * @param string $fullName
     * @param string $email
     * @return bool
     * @throws \Exception
     */
    private function updateUser(User $user, string $fullName, string $email): bool
    {
        $user->name = $fullName;
        $user->email = $email;
        $saved = $user->save();

        if (!$saved) {
            Log::error('Falha ao salvar o modelo User', ['user_id' => $user->id, 'data' => ['name' => $fullName, 'email' => $email]]);
            throw new \Exception('Falha ao salvar os dados do usuário.');
        }

        return true;
    }

    /**
     * Atualiza o cliente no Asaas.
     *
     * @param Member $member
     * @param array $data
     * @param string $fullName
     * @param AsaasCustomerService $asaasCustomerService
     * @return void
     * @throws \Exception
     */
    private function updateAsaasCustomer(Member $member, array $data, string $fullName, AsaasCustomerService $asaasCustomerService): void
    {
        if (!$member->member_asaas_id) {
            Log::warning('Nenhum member_asaas_id encontrado para o membro', ['member_id' => $member->id]);
            return;
        }

        $asaasData = [
            'name' => $fullName,
            'cpfCnpj' => $data['cpf'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'mobilePhone' => $data['cellphone'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['number'] ?? null,
            'additionalEmails' => null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['district'] ?? null,
            'postalCode' => $data['cep'] ?? null,
            'notificationDisabled' => false,
            'externalReference' => (string) $member->id,
            'foreignCustomer' => false,
        ];

        Log::info('Atualizando cliente no Asaas', ['member_id' => $member->id, 'asaas_customer_id' => $member->member_asaas_id, 'asaas_data' => $asaasData]);
        $asaasResponse = $asaasCustomerService->updateCustomer($member->member_asaas_id, $asaasData);

        if (!isset($asaasResponse['id'])) {
            Log::error('Falha ao atualizar cliente no Asaas', [
                'member_id' => $member->id,
                'cpf' => substr($data['cpf'], 0, 4) . '****',
                'response' => $asaasResponse,
            ]);
            throw new \Exception('Erro ao atualizar cliente no Asaas: ' . (json_encode($asaasResponse) ?: 'Resposta inválida'));
        }
    }

    /**
     * Atualiza o perfil do membro.
     *
     * @param Request $request
     * @param AsaasCustomerService $asaasCustomerService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AsaasCustomerService $asaasCustomerService)
    {
        Log::debug('Iniciando método update do MemberProfileController', ['request_data' => $request->all(), 'user_id' => Auth::user()->id]);

        /** @var User $user */
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            Log::warning('Membro não encontrado para o usuário', ['user_id' => $user->id]);
            return redirect()->route('member.dashboard')->withErrors(['error' => 'Perfil de membro não encontrado.']);
        }

        try {
            Log::debug('Testando conexão com o banco de dados');
            DB::connection()->getPdo();

            $data = $this->validateRequest($request, $user, $member);
            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

            DB::transaction(function () use ($member, $data, $user, $fullName, $asaasCustomerService) {
                Log::info('Iniciando transação de atualização', ['member_id' => $member->id]);
                $this->updateMember($member, $data, $data);
                $this->updateUser($user, $fullName, $data['email']);
                $this->updateAsaasCustomer($member, $data, $fullName, $asaasCustomerService);
                Log::info('Membro atualizado com sucesso', ['member_id' => $member->id, 'user_id' => $user->id]);
            });

            return redirect()->route('member.profile.edit')->with('success', 'Perfil atualizado com sucesso.');
        } catch (ValidationException $e) {
            Log::warning('Erro de validação na atualização', ['errors' => $e->errors(), 'request_data' => $request->all()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar membro', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'member_id' => $member->id,
                'request_data' => $request->all(),
            ]);
            return back()->withErrors(['error' => 'Erro ao atualizar o perfil: ' . $e->getMessage()])->withInput();
        }
    }
}