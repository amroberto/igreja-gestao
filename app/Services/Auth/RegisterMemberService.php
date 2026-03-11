<?php

namespace App\Services\Auth;

use App\Models\Member;
use App\Models\User;
use App\Services\Asaas\AsaasCustomerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterMemberService
{
    public function execute(array $data)
    {
        return DB::transaction(function () use ($data) {

            Log::info('Iniciando registro de membro');

            $cpf = $data['cpf'];

            /*
            |--------------------------------------------------
            | Separar nome
            |--------------------------------------------------
            */

            $names = explode(' ', trim($data['name']));

            $firstName = array_shift($names);
            $lastName = count($names) ? array_pop($names) : '';
            $middleName = count($names) ? implode(' ', $names) : '';

            /*
            |--------------------------------------------------
            | Criar usuário
            |--------------------------------------------------
            */

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'cpf' => $cpf,
                'community_id' => $data['community_id'],
                'user_type' => 'membro',
            ]);

            Log::info('Usuário criado', ['user_id' => $user->id]);

            /*
            |--------------------------------------------------
            | Buscar membro existente
            |--------------------------------------------------
            */

            $member = Member::withTrashed()
                ->where('cpf', $cpf)
                ->first();

            if ($member) {

                if ($member->trashed()) {
                    $member->restore();
                }

                $member->update([
                    'user_id' => $user->id,
                    'email' => $data['email'],
                    'community_id' => $data['community_id'],
                ]);

                Log::info('Membro existente vinculado', [
                    'member_id' => $member->id
                ]);

            } else {

                $member = Member::create([
                    'cpf' => $cpf,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'user_id' => $user->id,
                    'email' => $data['email'],
                    'community_id' => $data['community_id'],
                    'is_contributor' => true,
                    'is_active' => true,
                ]);

                Log::info('Novo membro criado', [
                    'member_id' => $member->id
                ]);
            }

            return [
                'user' => $user,
                'member' => $member
            ];
        });
    }
}