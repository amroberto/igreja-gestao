<?php

namespace App\Http\Controllers\Sec;

use App\Models\User;
use App\Models\Member;
use Barryvdh\DomPDF\PDF;
use App\Models\Community;
use App\Enums\UserTypeEnum;
use App\Enums\GenderEnum;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Models\ParochialUnion;
use App\Enums\MaritalStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\DeliveryMethodEnum;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
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
        Log::info('Comunidades carregadas:', ['user_id' => $user->id, 'communities' => $communities->pluck('id')->toArray()]);

        if ($communities->isEmpty()) {
            return redirect()->route('sec.dashboard')->with('error', 'Nenhuma comunidade associada ao usuário.');
        }

        $accessibleCommunityIds = $communities->pluck('id')->toArray();

        $query = Member::query()->with('community')->whereIn('community_id', $accessibleCommunityIds)->orderBy('name');
        $members = $query->paginate(10);

        Log::info('Membros carregados:', [
            'user_id' => $user->id,
            'accessibleCommunityIds' => $accessibleCommunityIds,
            'member_count' => $members->total()
        ]);

        return view('sec.members.index', [
            'members' => $members,
            'communities' => $communities,
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

    public function getNameByCode($code)
    {
        $member = Member::where('id', $code)->first();

        if ($member) {
            return response()->json(['name' => $member->name]);
        } else {
            return response()->json(['name' => ''], 404);
        }
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
