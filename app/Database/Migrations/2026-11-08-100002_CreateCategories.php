<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategories extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            // estimasi kg CO2 yang dihemat untuk tiap 1 kg barang yang didaur ulang / dipakai ulang
            'co2_factor'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 1.0],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('categories');
    }

    public function down(): void
    {
        $this->forge->dropTable('categories', true);
    }
}
