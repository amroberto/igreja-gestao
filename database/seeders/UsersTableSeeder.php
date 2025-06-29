<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Criação do usuário admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'cpf' => '12345678901',
            'status' => 'ativo',
        ]);

        User::create([
            'name' => 'Member',
            'email' => 'user@email.com',
            'password' => Hash::make('password'),
            'user_type' => 'membro',
            'cpf' => '12345678902',
            'community_id' => 1,
            'status' => 'ativo',
        ]);

        User::create([
            'name' => 'Sec',
            'email' => 'sec@email.com',
            'password' => Hash::make('password'),
            'user_type' => 'sec',
            'cpf' => '12345678903',
            'community_id' => 1,
            'status' => 'ativo',
        ]);
    }
}
