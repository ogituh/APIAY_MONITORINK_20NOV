<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // panggil semua seeder di sini
        $this->call([
            UserSeeder::class,
            // MasterSeeder::class,
            // PartSeeder::class,
            // StockSeeder::class,
            // OrderSeeder::class,
            // SupplierSeeder::class,

        ]);
    }
}
