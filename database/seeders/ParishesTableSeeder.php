<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParishesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('parishes')->insert([
            [
                'name' => 'ADM UP São João',
                'cnpj' => '12.345.678/0001-88',
                'cep' => '12345-678',
                'address' => 'Rua Exemplo 5',
                'number' => '123',
                'complement' => '',
                'district' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'phone' => '(11) 1234-5678',
                'cellphone' => '(11) 98765-4321',
                'email' => 'upsaojoao@exemplo.com',
                'website' => 'http://upaojoao.com.br',
                'logo' => 'logo_sao_joao.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paróquia São João',
                'cnpj' => '12.345.678/0001-90',
                'cep' => '12345-678',
                'address' => 'Rua Exemplo 1',
                'number' => '123',
                'complement' => 'Casa Paroquial',
                'district' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'phone' => '(11) 1234-5678',
                'cellphone' => '(11) 98765-4321',
                'email' => 'paroquiasaojoao@exemplo.com',
                'website' => 'http://paroquiasaojoao.com.br',
                'logo' => 'logo_sao_joao.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paróquia Nossa Senhora Aparecida',
                'cnpj' => '23.456.789/0001-91',
                'cep' => '23456-789',
                'address' => 'Avenida Exemplo 2',
                'number' => '456',
                'complement' => 'Sala de Reuniões',
                'district' => 'Jardim',
                'city' => 'São Bernardo do Campo',
                'state' => 'SP',
                'phone' => '(11) 2345-6789',
                'cellphone' => '(11) 98765-4322',
                'email' => 'paroquianossaaparecida@exemplo.com',
                'website' => 'http://paroquianossaaparecida.com.br',
                'logo' => 'logo_nossa_aparecida.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paróquia Santa Teresa',
                'cnpj' => '34.567.890/0001-92',
                'cep' => '34567-890',
                'address' => 'Rua Santa Teresa 3',
                'number' => '789',
                'complement' => 'Anexo',
                'district' => 'Vila Maria',
                'city' => 'São Paulo',
                'state' => 'SP',
                'phone' => '(11) 3456-7890',
                'cellphone' => '(11) 98765-4323',
                'email' => 'paroquiasantateresa@exemplo.com',
                'website' => 'http://paroquiasantateresa.com.br',
                'logo' => 'logo_santa_teresa.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paróquia São José',
                'cnpj' => '45.678.901/0001-93',
                'cep' => '45678-901',
                'address' => 'Rua São José 4',
                'number' => '101',
                'complement' => 'Edifício Paroquial',
                'district' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'phone' => '(11) 4567-8901',
                'cellphone' => '(11) 98765-4324',
                'email' => 'paroquiasaosjose@exemplo.com',
                'website' => 'http://paroquiasaosjose.com.br',
                'logo' => 'logo_sao_jose.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paróquia Cristo Rei',
                'cnpj' => '56.789.012/0001-94',
                'cep' => '56789-012',
                'address' => 'Rua Cristo Rei 5',
                'number' => '202',
                'complement' => 'Igreja Matriz',
                'district' => 'Boa Vista',
                'city' => 'Santos',
                'state' => 'SP',
                'phone' => '(13) 5678-9012',
                'cellphone' => '(13) 98765-4325',
                'email' => 'paroquiacristorei@exemplo.com',
                'website' => 'http://paroquiacristorei.com.br',
                'logo' => 'logo_cristo_rei.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
