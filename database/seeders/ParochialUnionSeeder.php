<?php

namespace Database\Seeders;

use App\Models\ParochialUnion;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ParochialUnionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se já existe um registro
        if (ParochialUnion::exists()) {
            return; // Sai se a tabela não estiver vazia
        }

        ParochialUnion::create([
            'corporate_name' => 'Igreja Evangélica Luterana de São João',
            'trade_name' => 'UP São João',
            'cnpj' => '12345678000190',
            'phone' => '(11) 1234-5678',
            'cellphone' => '(11) 91234-5678',
            'cep' => '01234-567',
            'address' => 'Rua Principal',
            'number' => '100',
            'complement' => 'Bloco A',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'website' => 'https://uniaoparoquial.org',
            'email' => 'contato@uniaoparoquial.org',
            'logo' => null,
        ]);
    }
}
