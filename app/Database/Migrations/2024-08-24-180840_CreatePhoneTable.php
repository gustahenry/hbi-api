<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhoneTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_contact' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'phone' => [
                'type'       => 'BIGINT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_contact');
        $this->forge->addKey('phone');
        $this->forge->addForeignKey('id_contact', 'contacts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('phone');
        
    }

    public function down()
    {
        $this->forge->dropTable('phone');
    }
}
