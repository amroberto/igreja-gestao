<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Enums\UserTypeEnum;
use App\Enums\StatusMemberEnum;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Obtém IDs de comunidades existentes (ou deixa null se não houver)
        $communityIds = \App\Models\Community::pluck('id')->toArray();

        // Gera 10 membros fictícios
        for ($i = 0; $i < 2000; $i++) {
            $name = $faker->name;
            $nameParts = explode(' ', trim($name));
            $firstName = $nameParts[0];
            $lastName = count($nameParts) > 1 ? end($nameParts) : null;
            $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : null;

            Member::create([
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'cpf' => $faker->unique()->cpf(false), // CPF sem pontos e traços
                'rg' => $faker->rg(false), // RG sem pontos e traços
                'birth_date' => $faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
                'gender' => $faker->randomElement(['masculino', 'feminino', 'outro']),
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'cellphone' => $faker->cellphoneNumber,
                'cep' => $faker->postcode,
                'address' => $faker->streetAddress,
                'number' => $faker->buildingNumber,
                'complement' => $faker->optional()->secondaryAddress,
                'district' => $faker->streetName,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'community_id' => !empty($communityIds) ? $faker->randomElement($communityIds) : null,
                'user_id' => null, // Não vinculado a usuário
                'is_active' => true,
                'is_contributor' => true,
                'status' => StatusMemberEnum::ATIVO,
                'marital_status' => $faker->randomElement(['solteiro', 'casado', 'divorciado', 'viúvo']),
                'father_name' => $faker->name('male'),
                'mother_name' => $faker->name('female'),
                'delivery_method' => 'correio',
                'payment_method' => 'transf_bancaria',
                'due_day' => 5,
                'amount' => $faker->randomFloat(2, 0, 1000),
                'observations' => $faker->optional()->sentence,
                'member_asaas_id' => null,
            ]);
        }
    }
}
