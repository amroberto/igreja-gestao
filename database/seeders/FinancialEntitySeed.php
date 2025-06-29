<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinancialEntitySeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Sec UPSJ',
            'community_id' => 1,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'On-line',
            'community_id' => 1,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Sec Igrejinha',
            'community_id' => 2,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Sec Ágape',
            'community_id' => 3,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Sec Esperança',
            'community_id' => 4,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Sec Luz',
            'community_id' => 5,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Sec Vida Nova',
            'community_id' => 6,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Banco Itaú',
            'community_id' => 1,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Asaas - Boletos',
            'community_id' => 1,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Asaas - Avulso',
            'community_id' => 1,
        ]);

        \App\Models\FinancialEntity::factory()->create([
            'name' => 'Asaas - Pix',
            'community_id' => 1,
        ]);

    }
}
