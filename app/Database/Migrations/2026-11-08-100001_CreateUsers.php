<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'               => ['type' => 'VARCHAR', 'constraint' => 150],
            'password'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'                => ['type' => 'VARCHAR', 'constraint' => 15, 'default' => 'penyetor'], // penyetor | pendaur | admin
            'phone'               => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'city'                => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'address'             => ['type' => 'TEXT', 'null' => true],
            'bio'                 => ['type' => 'TEXT', 'null' => true],
            'photo'               => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'verification_status' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'none'], // none|pending|approved|rejected
            'verification_note'   => ['type' => 'TEXT', 'null' => true],
            'verification_doc'    => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'verified_at'         => ['type' => 'DATETIME', 'null' => true],
            'is_active'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'rating_avg'          => ['type' => 'DECIMAL', 'constraint' => '3,2', 'default' => 0],
            'rating_count'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
