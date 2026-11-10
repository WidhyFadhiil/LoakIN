<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // [nama, deskripsi, estimasi kg CO2 yang dihemat per kg barang]
        $rows = [
            ['Plastik', 'Botol, galon, wadah, dan kemasan plastik', 1.5],
            ['Kertas & Kardus', 'Kardus, koran, buku bekas, kertas HVS', 0.9],
            ['Logam', 'Besi, aluminium, kaleng, tembaga', 4.0],
            ['Elektronik', 'Ponsel, kipas, TV, kabel, perangkat rusak', 2.5],
            ['Kayu & Furnitur', 'Kursi, meja, lemari, papan bekas', 1.0],
            ['Kaca', 'Botol kaca, toples, pecahan kaca bersih', 0.3],
            ['Tekstil', 'Pakaian, kain perca, sepatu, tas', 3.0],
            ['Minyak Jelantah', 'Minyak goreng bekas untuk biodiesel', 2.0],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($rows as [$name, $desc, $co2]) {
            $slug = url_title($name, '-', true);
            if ($this->db->table('categories')->where('slug', $slug)->countAllResults() > 0) {
                continue;
            }
            $this->db->table('categories')->insert([
                'name' => $name, 'slug' => $slug, 'description' => $desc, 'co2_factor' => $co2,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
