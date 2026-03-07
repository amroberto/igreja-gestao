<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Community::factory()->create([
            'name' => 'IESJ - Igreja Evangélica São João',
            'cnpj' => '12345678000190',
            'parish_id' => 1,
            'cep' => '12345678',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'complement' => 'Apto 1',
            'district' => 'Bairro Exemplo',
            'city' => 'São Paulo',
            'state' => 'SP',
            'phone' => '1112345678',
            'cellphone' => '11912345678',
            'email' => 'comunidadeigrejinha@email.com',
            'website' => 'https://comunidadeigrejinha.com',

        ]);

        
        \App\Models\Community::factory()->create([
            'name' => 'Comunidade Igrejinha',
            'cnpj' => '12345678000191',
            'parish_id' => 2,
            'cep' => '12345678',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'complement' => 'Apto 1',
            'district' => 'Bairro Exemplo',
            'city' => 'São Paulo',
            'state' => 'SP',
            'phone' => '1112345678',
            'cellphone' => '11912345678',
            'email' => 'comunidadeigrejinha@email.com',
            'website' => 'https://comunidadeigrejinha.com',

        ]);

        \App\Models\Community::factory()->create([
            'name' => 'Comunidade Agape',
            'cnpj' => '98765432000192',
            'parish_id' => 3,
            'cep' => '87654321',
            'street' => 'Avenida Exemplo',
            'number' => '456',
            'complement' => 'Sala 2',
            'district' => 'Bairro Exemplo 2',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'phone' => '1123456789',
            'cellphone' => '11998765432',
            'email' => 'agape@email.com',
            'website' => 'https://comunidadeagape.com',
        ]);

        \App\Models\Community::factory()->create([
            'name' => 'Comunidade Esperança',
            'cnpj' => '11223344000193',
            'parish_id' => 4,
            'cep' => '11223344',
            'street' => 'Rua Esperança',
            'number' => '789',
            'complement' => '',
            'district' => 'Bairro Esperança',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
            'phone' => '1134567890',
            'cellphone' => '11987654321',
            'email' => 'esperanca@email.com',
            'website' => 'https://comunidadeesperanca.com',
        ]);

        \App\Models\Community::factory()->create([
            'name' => 'Comunidade Luz',
            'cnpj' => '22334455000194',
            'parish_id' => 5,
            'cep' => '22334455',
            'street' => 'Avenida Luz',
            'number' => '321',
            'complement' => '',
            'district' => 'Bairro Luz',
            'city' => 'Curitiba',
            'state' => 'PR',
            'phone' => '1145678901',
            'cellphone' => '11976543210',
            'email' => 'luz@email.com',
            'website' => 'https://comunidadeluz.com',
        ]);
        \App\Models\Community::factory()->create([
            'name' => 'Comunidade Vida Nova',
            'cnpj' => '33445566000195',
            'parish_id' => 6,
            'cep' => '33445566',
            'street' => 'Rua Vida Nova',
            'number' => '654',
            'complement' => '',
            'district' => 'Bairro Vida Nova',
            'city' => 'Porto Alegre',
            'state' => 'RS',
            'phone' => '1156789012',
            'cellphone' => '11965432109',
            'email' => 'vidanova@email.com',
            'website' => 'https://comunidadeluz.com',
        ]);
    }
}
