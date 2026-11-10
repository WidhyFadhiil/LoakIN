<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'offer_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sender_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'body'       => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('offer_id');
        $this->forge->addForeignKey('offer_id', 'offers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sender_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('messages', true);
    }
}
