<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Models\Member;
use App\Enums\GenderEnum;
use App\Models\Community;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Enums\MaritalStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Services\Asaas\AsaasCustomerService;
use App\Http\Requests\UpdateMemberRequest;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    protected AsaasCustomerService $asaas;

    public function __construct(AsaasCustomerService $asaas)
    {
        $this->asaas = $asaas;
    }

    /**
     * Dashboard do membro
     */
    public function dashboard(): View
    {
        Log::debug('Acessando dashboard do membro', [
            'user_id' => Auth::id()
        ]);

        return view('member.dashboard');
    }

    /**
     * Formulário de edição do membro
     */
    public function edit(): View
    {
        $user = Auth::user();

        $member = Member::where('user_id', $user->id)->firstOrFail();

        $communities = Community::where('id', '!=', 1)->get();

        return view('member.member.edit', [
            'user' => $user,
            'member' => $member,
            'communities' => $communities,
            'genders' => GenderEnum::cases(),
            'maritalStatuses' => MaritalStatusEnum::cases(),
            'error' => null,
        ]);
    }

    /**
     * Atualiza o membro e sincroniza com Asaas
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        Log::debug('Iniciando update do MemberController', [
            'member_id' => $member->id
        ]);

        $data = $this->removeMasks($request->validated());

        DB::beginTransaction();

        try {

            $member->update($data);

            $payload = [
                'name' => trim($member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name),
                'cpfCnpj' => $member->cpf,
                'email' => $member->email,
                'phone' => $member->phone,
                'mobilePhone' => $member->cellphone,
                'address' => $member->address,
                'addressNumber' => $member->number,
                'complement' => $member->complement,
                'province' => $member->district,
                'postalCode' => $member->cep,
            ];

            /**
             * Se já existe ID do Asaas → atualizar
             */
            if ($member->member_asaas_id) {

                Log::info('Atualizando cliente no Asaas', [
                    'asaas_id' => $member->member_asaas_id
                ]);

                $this->asaas->updateCustomer(
                    $member->member_asaas_id,
                    $payload
                );
            }

            /**
             * Se não existe ID → buscar por CPF
             */
            else {

                Log::info('Buscando cliente no Asaas pelo CPF', [
                    'cpf' => $member->cpf
                ]);

                $existingCustomer = $this->asaas->findCustomerByCpf($member->cpf);

                /**
                 * Cliente já existe no Asaas
                 */
                if (!empty($existingCustomer) && isset($existingCustomer['id'])) {

                    Log::info('Cliente encontrado no Asaas', [
                        'asaas_id' => $existingCustomer['id']
                    ]);

                    $member->member_asaas_id = $existingCustomer['id'];
                    $member->save();

                    $this->asaas->updateCustomer(
                        $member->member_asaas_id,
                        $payload
                    );
                }

                /**
                 * Cliente não existe → criar
                 */
                else {

                    Log::info('Cliente não encontrado no Asaas. Criando...', [
                        'cpf' => $member->cpf
                    ]);

                    $customer = $this->asaas->createCustomer($payload);

                    if (!empty($customer) && isset($customer['id'])) {

                        $member->member_asaas_id = $customer['id'];
                        $member->save();

                        Log::info('Cliente criado no Asaas', [
                            'asaas_id' => $customer['id']
                        ]);
                    } else {

                        Log::error('Erro ao criar cliente no Asaas', [
                            'payload' => $payload,
                            'response' => $customer
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('member.dashboard')
                ->with('success', 'Perfil atualizado com sucesso.');
        }

        catch (\Exception $e) {

            DB::rollBack();

            Log::error('Erro ao sincronizar membro com Asaas', [
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);

            throw ValidationException::withMessages([
                'error' => 'Erro ao atualizar o perfil. Tente novamente.'
            ]);
        }
    }

    /**
     * Remove máscaras de campos
     */
    private function removeMasks(array $data): array
    {
        $fields = ['cnpj', 'cep', 'cpf', 'phone', 'cellphone'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D/', '', $data[$field]);
            }
        }

        return $data;
    }
}