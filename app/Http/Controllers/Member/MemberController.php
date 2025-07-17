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
     * Exibe o painel do membro.
     */
    public function dashboard(): View
    {
        Log::debug('Acessando o dashboard do membro', ['user_id' => Auth::id()]);
        return view('member.dashboard');
    }

    /**
     * Exibe o formulário de edição do membro.
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
     * Atualiza o perfil do membro.
     *
     * @param UpdateMemberRequest $request
     * @param Member $member
     * @param AsaasCustomerService $asaasCustomerService
     * @return RedirectResponse
     */
    public function update(UpdateMemberRequest $request, Member $member, AsaasCustomerService $asaasCustomerService)
    {
        Log::debug('Iniciando método update do MemberController', ['request_data' => $request->all(), 'member_id' => $member->id]);

        $data = $request->validated();
        $data = $this->removeMasks($data);

        DB::beginTransaction();
        try {
            $member->update($data);
            if ($member->asaas_member_id) {
                $asaasCustomerService->updateCustomer($member->asaas_member_id, $data);
            }
            DB::commit();
            Log::info('Membro atualizado com sucesso', ['member_id' => $member->id]);
            return redirect()->route('member.dashboard')->with('success', 'Perfil atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar membro', ['error' => $e->getMessage(), 'member_id' => $member->id]);
            throw ValidationException::withMessages(['error' => 'Erro ao atualizar o perfil. Por favor, tente novamente.']);
        }
    }

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

    /**
     * [Description for index]
     *
     * @return View
     * 
     */
    public function index(): View
    {
        return view('member.members.index');
    }
}
