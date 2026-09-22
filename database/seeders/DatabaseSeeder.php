<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\AccountPlanSeeder;
use Database\Seeders\FinancialEntitySeed;
use Database\Seeders\ParishesTableSeeder;
use Database\Seeders\ParochialUnionSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ParochialUnionSeeder::class,
            ParishesTableSeeder::class,
            CommunitySeeder::class,
            FinancialEntitySeed::class,
            UsersTableSeeder::class,
            MemberSeeder::class,
            AccountPlanSeeder::class,
        ]);
    }
}
