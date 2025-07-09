<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Member;
use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use App\Enums\StatusMemberEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Log dos dados recebidos
        Log::info('Dados recebidos no register: ' . json_encode($request->all()));

        try {
            // Sanitiza o CPF
            $cpf = preg_replace('/\D/', '', $request->input('cpf'));

            // Validação dos dados
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'cpf' => 'required|string|size:11|unique:users,cpf|unique:members,cpf,NULL,id,deleted_at,NULL',
                'community_id' => 'required|exists:communities,id',
                'email' => 'required|string|email|max:255|unique:users,email|unique:members,email,NULL,id,deleted_at,NULL',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'O nome é obrigatório.',
                'name.string' => 'O nome deve ser uma string.',
                'name.max' => 'O nome não pode ter mais de 255 caracteres.',
                'cpf.required' => 'O CPF é obrigatório.',
                'cpf.string' => 'O CPF deve ser uma string.',
                'cpf.size' => 'O CPF deve ter 11 dígitos.',
                'cpf.unique' => 'Já existe um usuário ou membro cadastrado com este CPF.',
                'community_id.required' => 'A comunidade é obrigatória.',
                'community_id.exists' => 'A comunidade selecionada não existe.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.string' => 'O e-mail deve ser uma string.',
                'email.email' => 'O e-mail deve ser um endereço de e-mail válido.',
                'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
                'email.unique' => 'Já existe um usuário ou membro cadastrado com este e-mail.',
                'password.required' => 'A senha é obrigatória.',
                'password.string' => 'A senha deve ser uma string.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
                'password.confirmed' => 'A confirmação da senha não confere.',
            ]);

            // Dividir o nome em first_name, middle_name, last_name
            $nameParts = array_filter(explode(' ', trim($validated['name'])));
            $firstName = array_shift($nameParts) ?? '';
            $lastName = array_pop($nameParts) ?? '';
            $middleName = !empty($nameParts) ? implode(' ', $nameParts) : null;

            // Iniciar transação
            DB::beginTransaction();

            // Criação do usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'cpf' => $cpf,
                'user_type' => UserTypeEnum::MEMBER->value, 
            ]);

            Log::info('Usuário criado com sucesso: user_id=' . $user->id);

            // Verifica se já existe membro com o mesmo CPF (incluindo soft-deleted)
            $member = Member::withTrashed()->where('cpf', $cpf)->first();

            if ($member) {
                if ($member->trashed()) {
                    $member->restore();
                    Log::info('Membro restaurado: member_id=' . $member->id);
                }
                // Atualiza o membro existente
                $member->update([
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => $validated['email'],
                    'community_id' => $validated['community_id'],
                    'is_active' => true,
                    'is_contributor' => true,
                    'status' => StatusMemberEnum::ATIVO,
                    'delivery_method' => 'online',
                    'payment_method' => 'Transferência Bancária',
                    'due_day' => 5,
                    'amount' => 0.00,
                ]);
                Log::info('Membro atualizado: member_id=' . $member->id);
            } else {
                // Cria um novo membro
                $member = Member::create([
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'cpf' => $cpf,
                    'email' => $validated['email'],
                    'user_id' => $user->id,
                    'community_id' => $validated['community_id'],
                    'is_active' => true,
                    'is_contributor' => true,
                    'status' => StatusMemberEnum::ATIVO,
                    'delivery_method' => 'online',
                    'payment_method' => 'transf_bancaria',
                    'due_day' => 5,
                    'amount' => 0.00,
                ]);
                Log::info('Membro criado: member_id=' . $member->id);
            }

            // Commit da transação
            DB::commit();

            Log::info('Cadastro concluído com sucesso: user_id=' . $user->id . ', member_id=' . $member->id);

            // Autenticar o usuário após o registro
            \Illuminate\Support\Facades\Auth::login($user);

            return redirect()->route('member.profile.edit', $member->id)
                ->with('success', 'Usuário registrado! Pedimos que complemente seus dados cadastrais.');

        } catch (ValidationException $e) {
            Log::warning('Erro de validação no cadastro: ' . json_encode($e->errors()));
            throw $e; // Re-throw para exibir erros de validação na view
        } catch (\Exception $e) {
            // Rollback da transação em caso de erro
            DB::rollBack();
            Log::error('Erro ao realizar o cadastro: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Erro ao realizar o cadastro: ' . $e->getMessage());
        }
    }
}