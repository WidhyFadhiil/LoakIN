<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRatings extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'offer_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'listing_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rater_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'ratee_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'score'      => ['type' => 'TINYINT', 'constraint' => 1],
            'comment'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['offer_id', 'rater_id']);
        $this->forge->addKey('ratee_id');
        $this->forge->addForeignKey('offer_id', 'offers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rater_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('ratee_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ratings');
    }

    public function down(): void
    {
        $this->forge->dropTable('ratings', true);
    }
}
