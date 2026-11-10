<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data contoh untuk demo / presentasi. Semua akun berpassword "password123".
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('DatabaseSeeder');

        $now  = date('Y-m-d H:i:s');
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $users = [
            ['Sari Wulandari', 'sari@loakin.test', 'penyetor', '081211110001', 'Bekasi', 'none'],
            ['Budi Santoso', 'budi@loakin.test', 'penyetor', '081211110002', 'Jakarta Timur', 'none'],
            ['Bank Sampah Hijau', 'hijau@loakin.test', 'pendaur', '081211110003', 'Bekasi', 'approved'],
            ['Kriya Plastik Nusantara', 'kriya@loakin.test', 'pendaur', '081211110004', 'Jakarta Timur', 'approved'],
            ['Dedi Pengepul', 'dedi@loakin.test', 'pendaur', '081211110005', 'Bekasi', 'pending'],
        ];
        $ids = [];
        foreach ($users as [$name, $email, $role, $phone, $city, $verif]) {
            $exists = $this->db->table('users')->where('email', $email)->get()->getRowArray();
            if ($exists) {
                $ids[$email] = (int) $exists['id'];
                continue;
            }
            $this->db->table('users')->insert([
                'name' => $name, 'email' => $email, 'password' => $hash, 'role' => $role, 'phone' => $phone,
                'city' => $city, 'verification_status' => $verif, 'is_active' => 1,
                'verification_note' => $verif === 'pending' ? 'Pengepul barang bekas di Bekasi sejak 2019, punya gudang sendiri.' : null,
                'verified_at' => $verif === 'approved' ? $now : null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $ids[$email] = (int) $this->db->insertID();
        }

        $cat = [];
        foreach ($this->db->table('categories')->get()->getResultArray() as $c) {
            $cat[$c['slug']] = (int) $c['id'];
        }

        if ($this->db->table('listings')->countAllResults() > 0) {
            return;
        }

        $exp = date('Y-m-d H:i:s', strtotime('+14 days'));
        // [pemilik, kategori, judul, deskripsi, berat, kondisi, kota, tipe harga, harga, serah terima]
        $rows = [
            ['sari@loakin.test', 'plastik', 'Botol plastik bekas (±120 buah)', 'Botol air mineral & teh kemasan, sudah dibilas. Cocok untuk kerajinan pot atau didaur ulang.', 6, 'layak', 'Bekasi', 'gratis', 0, 'pickup'],
            ['sari@loakin.test', 'kertas-kardus', 'Kardus bekas pindahan', 'Kardus tebal ukuran sedang, kondisi masih rapi, sekitar 30 lembar.', 8, 'baik', 'Bekasi', 'berbayar', 25000, 'pickup'],
            ['budi@loakin.test', 'elektronik', 'Kipas angin & dispenser rusak', 'Dua unit rusak motor/pemanasnya. Bisa dibongkar untuk komponen.', 9, 'rusak', 'Jakarta Timur', 'nego', 60000, 'janjian'],
            ['budi@loakin.test', 'logam', 'Besi tua & kaleng aluminium', 'Pagar besi bekas dan kaleng minuman terkumpul 1 karung besar.', 22, 'rusak', 'Jakarta Timur', 'nego', 90000, 'pickup'],
            ['sari@loakin.test', 'kayu-furnitur', 'Kursi kayu 4 buah', 'Kayu jati belanda, ada yang goyang. Bisa diperbaiki atau dipakai bahan kerajinan.', 14, 'layak', 'Bekasi', 'berbayar', 120000, 'diantar'],
            ['budi@loakin.test', 'minyak-jelantah', 'Minyak jelantah 10 liter', 'Sudah disaring, dalam jeriken bersih.', 9, 'layak', 'Jakarta Timur', 'berbayar', 40000, 'diantar'],
        ];
        foreach ($rows as [$owner, $slug, $title, $desc, $w, $cond, $city, $pt, $price, $ho]) {
            $this->db->table('listings')->insert([
                'user_id' => $ids[$owner], 'category_id' => $cat[$slug], 'title' => $title, 'description' => $desc,
                'weight_kg' => $w, 'item_condition' => $cond, 'city' => $city, 'price_type' => $pt, 'price' => $price,
                'handover' => $ho, 'status' => 'tersedia', 'expires_at' => $exp, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
