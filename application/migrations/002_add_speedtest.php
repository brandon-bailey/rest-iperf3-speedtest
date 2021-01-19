<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_speedtest extends CI_Migration
{
    public function up()
    {
        $this->speedtest();
    }

    private function speedtest()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INTEGER'
              ),
              'host' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
              ),
              'date' => array(
                 'type' => 'timestamp',
                 'null' => false,
                 'default' => 'CURRENT_TIMESTAMP'
              ),
              'results' => array(
                 'type' => 'text',
                 'null' => true
              ),
              'mac' => array(
                'type' => 'varchar',
                'constraint' => '255'
                )
            )
        );
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('speedtest');
    }

    public function down()
    {
        $this->dbforge->drop_table('speedtest');
    }
}
