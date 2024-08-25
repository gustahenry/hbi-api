<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAddressTable extends Migration
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
            'zip_code' => [
                'type'       => 'INT',
                'constraint' => '8',
                'unsigned'   => true,
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'street_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'address_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'address_line' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'neighborhood' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
        $this->forge->addKey('zip_code');
        $this->forge->addForeignKey('id_contact', 'contacts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('address');
    }

    public function down()
    {
        $this->forge->dropTable('address');
    }
}
