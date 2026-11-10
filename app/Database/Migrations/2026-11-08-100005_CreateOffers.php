<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOffers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'listing_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'recycler_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'offered_price' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'counter_price' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'final_price'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'note'          => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'menunggu'],
            'responded_at'  => ['type' => 'DATETIME', 'null' => true],
            'completed_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey(['listing_id', 'recycler_id']);
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recycler_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('offers');
    }

    public function down(): void
    {
        $this->forge->dropTable('offers', true);
    }
}
