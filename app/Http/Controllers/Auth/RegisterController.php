<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Member;
use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use App\Enums\StatusMemberEnum;
use App\Enums\DeliveryMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'cpf' => 'required|string|unique:users,cpf',
            'name' => 'required|string|max:255',
            'community_id' => 'required|exists:communities,id',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser uma string.',
            'cpf.unique' => 'Já existe um usuário cadastrado com este CPF.',
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser uma string.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'community_id.required' => 'A comunidade é obrigatória.',
            'community_id.exists' => 'A comunidade selecionada não existe.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser uma string.',
            'email.email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            'email.unique' => 'Já existe um usuário cadastrado com este e-mail.',
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma string.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        // Criação do usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'cpf' => $validated['cpf'],
            'user_type' => UserTypeEnum::MEMBER,
        ]);

        // Verifica se já existe membro com o mesmo CPF
        $member = Member::where('cpf', $validated['cpf'])->first();

        if ($member) {
            $member->user_id = $user->id;
            $member->delivery_method = 'online';
            $member->community_id = $validated['community_id'];
            $member->is_active = true;
            $member->status = StatusMemberEnum::ATIVO;
            $member->is_contributor = true;
            $member->save();
        } else {
            $member = Member::create([
                'name' => $validated['name'],
                'cpf' => $validated['cpf'],
                'email' => $validated['email'],
                'user_id' => $user->id,
                'is_contributor' => true,
                'delivery_method' => 'online',
                'status' => StatusMemberEnum::ATIVO,
                'user_type' => UserTypeEnum::MEMBER,
                'community_id' => $validated['community_id'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('member.profile.edit', $member->id)
            ->with('success', 'Usuário Registrado! Pedimos que complemente seus dados cadastrais.');
    }
}
