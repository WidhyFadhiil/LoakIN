<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php spark db:seed DatabaseSeeder
     * Kategori + akun admin selalu dibuat. Data contoh (pengguna & barang) hanya untuk demo:
     * php spark db:seed DemoSeeder
     */
    public function run(): void
    {
        $this->call('CategorySeeder');
        $this->call('AdminSeeder');
    }
}
