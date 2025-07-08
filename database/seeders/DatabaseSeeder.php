<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // Panggil seeder dummy Anda di sini
            DummyStockDataSeeder::class,
            // Anda mungkin punya seeder lain di sini, biarkan saja
        ]);
    }
}