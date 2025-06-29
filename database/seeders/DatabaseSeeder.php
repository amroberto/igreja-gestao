<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\FinancialEntitySeed;
use Database\Seeders\ParishesTableSeeder;
use Database\Seeders\ParochialUnionSeeder;

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
        ]);
    }
}
