<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@loakin.test';
        if ($this->db->table('users')->where('email', $email)->countAllResults() > 0) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'name' => 'Admin LoakIN', 'email' => $email,
            'password' => password_hash('admin12345', PASSWORD_DEFAULT),
            'role' => 'admin', 'phone' => '081200000000', 'city' => 'Bekasi',
            'verification_status' => 'approved', 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
