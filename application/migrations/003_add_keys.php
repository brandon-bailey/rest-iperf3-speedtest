<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_keys extends CI_Migration
{
    public function up()
    {
        $this->keys();
        $this->logs();
        $this->access();
        $this->limits();
    }

    private function keys()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INT',
                 'auto_increment' => true
              ),
              'user_id' => array(
                 'type' => 'int',
                 'constraint' => '11',
                 'null' => false
              ),
              'key' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
              ),
              'level' => array(
                 'type' => 'int',
                 'constraint' => '2',
                 'null' => false
              ),
              'ignore_limits' => array(
                 'type' => 'tinyint',
                 'constraint' => '1',
                 'null' => false,
                 'default' =>'0'
              ),
              'is_private_key' => array(
                 'type' => 'tinyint',
                 'constraint' => '1',
                 'null' => false,
                 'default' =>'0'
              ),
              'ip_addresses' => array(
                 'type' => 'text',
                 'null' => true
              ),
              'date_created' => array(
                 'type' => 'datetime',
                 'null' => false
              ),
              'username' => array(
                'type' => 'text',
                'null' => true
              )
            )
        );
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('keys');

        // Default Key that each pocketfi will have installed for use with api calls
        $data = array(
              'user_id' => 1,
              'key' => '4501c091b0366d76ea3218b6cfdd8097',
              'level' => 1,
              'ignore_limits' => 1,
              'is_private_key' => 0,
              'ip_addresses' => $_SERVER['SERVER_ADDR'],
              'date_created' => date("Y-m-d H:i:s"),
              'username' => 'admin'
            );
        $this->db->insert('keys', $data);
        $data = array(
              'user_id' => 0,
              'key' => '6bc8d61e5e09d750286bbaa94732262fd0435c86',
              'level' => 0,
              'ignore_limits' => 1,
              'is_private_key' => 0,
              'ip_addresses' => $_SERVER['SERVER_ADDR'],
              'date_created' => date("Y-m-d H:i:s"),
              'username' => $_SERVER['SERVER_NAME']
        );
        $this->db->insert('keys', $data);
    }

    private function logs()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INT',
                 'auto_increment' => true
              ),
              'uri' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
              ),
              'method' => array(
                 'type' => 'varchar',
                 'constraint' => '6',
                 'null' => false
              ),
              'params' => array(
                 'type' => 'text',
                 'default' => null
              ),
              'api_key' => array(
                 'type' => 'varchar',
                 'constraint' => '40',
                 'null' => false
              ),
              'ip_address' => array(
                 'type' => 'varchar',
                 'constraint' => '45',
                 'null' => false
              ),
              'time' => array(
                 'type' => 'INT',
                 'constraint' => '11',
                 'null' => false
              ),
              'rtime' => array(
                 'type' => 'float',
                 'default' => null
              ),
              'authorized' => array(
                 'type' => 'varchar',
                 'constraint' => '1',
                 'null' => false
              ),
              'response_code' => array(
                 'type' => 'smallint',
                 'constraint' => '3',
                 'default' => '0'
              )
            )
        );
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('logs');
    }

    private function access()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INT',
                 'unsigned' => true,
                 'auto_increment' => true
              ),
              'key' => array(
                 'type' => 'varchar',
                 'constraint' => '40',
                 'null' => false,
                 'default' => ''
              ),
              'all_access' => array(
                 'type' => 'tinyint',
                 'constraint' => '1',
                 'null' => false,
                 'default' => '0'
              ),
              'controller' => array(
                 'type' => 'varchar',
                 'constraint' => '50',
                 'null' => false,
                 'default' => ''
              ),
              'method' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false,
                 'default' => ''
              ),
              'date_created' => array(
                 'type' => 'datetime',
                 'null' => false
              ),
              'date_modified' => array(
                 'type' => 'datetime',
                 'null' => false,
              )
            )
        );
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('access');

        $data = array(
            'key' => '4501c091b0366d76ea3218b6cfdd8097',
            'all_access' => 1,
            'controller' => '',
            'method' => '',
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s")
        );
        $this->db->insert('access', $data);

        $data = array(
            'key' => '6bc8d61e5e09d750286bbaa94732262fd0435c86',
            'all_access' => 0,
            'controller' => 'api/tcp',
            'method' => '',
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s")
        );

        $this->db->insert('access', $data);

        $data = array(
            'key' => '6bc8d61e5e09d750286bbaa94732262fd0435c86',
            'all_access' => 0,
            'controller' => 'api/tcp',
            'method' => '/server',
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s")
          );
        $this->db->insert('access', $data);
        
        $data = array(
            'key' => '6bc8d61e5e09d750286bbaa94732262fd0435c86',
            'all_access' => 0,
            'controller' => 'api/servers',
            'method' => '',
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s")
          );
        $this->db->insert('access', $data);

        $data = array(
            'key' => '6bc8d61e5e09d750286bbaa94732262fd0435c86',
            'all_access' => 0,
            'controller' => 'api/servers',
            'method' => '/closest',
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s")
          );
        $this->db->insert('access', $data);
    }

    private function limits()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INT',
                 'auto_increment' => true
              ),
              'uri' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
              ),
              'count' => array(
                 'type' => 'INT',
                 'constraint' => '10',
                 'null' => false
              ),
              'hour_started' => array(
                 'type' => 'INT',
                 'constraint' => '11',
                 'null' => false
              ),
              'api_key' => array(
                 'type' => 'varchar',
                 'constraint' => '40',
                 'null' => false
              )
            )
        );
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('limits');
    }

    public function down()
    {
        $this->dbforge->drop_table('keys');
        $this->dbforge->drop_table('logs');
        $this->dbforge->drop_table('access');
        $this->dbforge->drop_table('limits');
    }
}
