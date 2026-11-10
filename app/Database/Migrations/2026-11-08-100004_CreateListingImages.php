<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateListingImages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'listing_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'filename'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'sort_order' => ['type' => 'TINYINT', 'constraint' => 3, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('listing_id');
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_images');
    }

    public function down(): void
    {
        $this->forge->dropTable('listing_images', true);
    }
}
