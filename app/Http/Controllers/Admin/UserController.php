<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Member;
use App\Models\Community;
use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\UserRequest;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();

            // Limpeza do CPF (remove a máscara)
            $cpf = preg_replace('/\D/', '', $request->cpf);  // Remove qualquer caractere não numérico

            $request->merge(['cpf' => $cpf]);  // Substitui o CPF com máscara pelo CPF limpo

            // Criação do usuário
            $userCreate = User::create($request->all());

            // Agora, procure um membro que tenha o mesmo CPF (sem máscara)
            $member = Member::where('cpf', $cpf)->first();

            if ($member) {
                $member->user_id = $userCreate->id;
                $member->save();
            }

            DB::commit();

            return redirect()->route('admin.users.edit', [
                'user' => $userCreate->id
            ])->with([
                'color' => 'success',
                'message' => 'Usuário cadastrado com sucesso!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $user = User::find($id);
        $communities = Community::all();
        if (is_null($user)) {
            abort(404, 'Usuário não encontrado');
        }

        return response()->view('admin.users.edit', [
            'user' => $user , 'communities' => $communities,  'userTypes' => UserTypeEnum::cases()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            abort(404, 'Usuário não encontrado');
        }

        try {
            DB::beginTransaction();

            // Logar dados recebidos
            Log::info('Dados recebidos no update:', $request->all());

            // Validar os dados
            $validated = $request->validate([
                'user_type' => 'required|in:' . implode(',', UserTypeEnum::getValues()),
                'community_id' => 'required|exists:communities,id',
                'communities' => 'nullable|array',
                'communities.*' => 'exists:communities,id',
            ]);

            // Atualizar campos do usuário
            $user->community_id = $request->community_id;
            $user->user_type = $request->user_type;
            $user->save();

            // Sincronizar comunidades acessíveis
            if ($request->user_type === UserTypeEnum::SEC->value) {
                // Incluir comunidade principal
                $communities = array_unique(array_merge($request->input('communities', []), [$user->community_id]));
                Log::info('Sincronizando comunidades para SEC:', [
                    'user_id' => $user->id,
                    'communities' => $communities
                ]);
                $user->communities()->sync($communities);
            } else {
                // Apenas comunidade principal para outros tipos
                Log::info('Sincronizando comunidade principal:', [
                    'user_id' => $user->id,
                    'community_id' => $user->community_id
                ]);
                $user->communities()->sync([$user->community_id]);
            }

            DB::commit();
            Log::info('Usuário ID: ' . $id . ' atualizado por usuário ID: ' . Auth::id());
            return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar Usuário ID: ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', ['user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
