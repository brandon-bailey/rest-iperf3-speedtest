<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Modify_servers extends CI_Migration
{
    public function up()
    {
        $this->servers();
    }

    private function servers()
    {
        $fields =  array(
              'status' => array(
                 'type' => 'tinyint',
                 'constraint' => '1',
                 'null' => false,
                 'default' => 1
              )
        );
        $this->dbforge->add_column('servers', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('servers', 'status');
    }
}
