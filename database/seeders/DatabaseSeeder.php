<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TahunAjaranSeeder::class,
            KelasSeeder::class,
            KategoriPrestasiSeeder::class,
            TingkatPenghargaanSeeder::class,
            EkstrakurikulerSeeder::class,
            ComprehensiveAllRolesSeeder::class,
            SiswaKelasXSeeder::class,
        ]);
    }
}