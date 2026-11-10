<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateListings extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'category_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'       => ['type' => 'TEXT', 'null' => true],
            'weight_kg'         => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0],
            'item_condition'    => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'layak'],
            'city'              => ['type' => 'VARCHAR', 'constraint' => 80],
            'address'           => ['type' => 'TEXT', 'null' => true],
            'price_type'        => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'gratis'],
            'price'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'handover'          => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'pickup'],
            'status'            => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'tersedia'],
            'accepted_offer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'expires_at'        => ['type' => 'DATETIME', 'null' => true],
            'completed_at'      => ['type' => 'DATETIME', 'null' => true],
            'views'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('category_id');
        $this->forge->addKey('city');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('listings');
    }

    public function down(): void
    {
        $this->forge->dropTable('listings', true);
    }
}
