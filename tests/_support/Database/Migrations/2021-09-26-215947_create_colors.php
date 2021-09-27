<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateColors extends Migration
{
    public function up()
    {
        $fields = [
            'name'       => ['type' => 'varchar', 'constraint' => 255],
            'hex'        => ['type' => 'varchar', 'constraint' => 7],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('name');
        $this->forge->addKey('created_at');

        $this->forge->createTable('colors');
    }

    public function down()
    {
        $this->forge->dropTable('colors');
    }
}
