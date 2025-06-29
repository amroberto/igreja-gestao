<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj'] ?? '');
        $data['phone'] = preg_replace('/\D/', '', $data['phone'] ?? '');
        $data['cellphone'] = preg_replace('/\D/', '', $data['cellphone'] ?? '');
        $data['cep'] = preg_replace('/\D/', '', $data['cep'] ?? '');
        $data['cpf'] = preg_replace('/\D/', '', $data['cpf'] ?? '');
        return $data;
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $data = $this->removeMasks($request->all());
            Log::info('Iniciando registro', [
                'email' => $data['email'],
                'input' => $request->except('password', 'password_confirmation'),
            ]);

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
                'cpf' => ['required', 'string', 'size:11', 'unique:members,cpf'],
                'community_id' => ['required', 'exists:communities,id'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'cpf' => $data['cpf'],
                'community_id' => $data['community_id'],
                'user_type' => 'membro',
            ]);

            $member = Member::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'user_id' => $user->id,
                'cpf' => $data['cpf'],
                'community_id' => $data['community_id'],
            ]);

            Log::info('Usuário e membro criados', [
                'user_id' => $user->id,
                'member_id' => $member->id,
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('member.profile.edit', $member->id)
                ->with('success', 'Cadastro realizado! Por favor, complete seu perfil.');
        } catch (\Exception $e) {
            Log::error('Erro ao registrar usuário', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email,
            ]);
            return back()->withErrors(['error' => 'Erro ao realizar o cadastro: ' . $e->getMessage()]);
        }
    }
}