<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_settings extends CI_Migration
{
    public function up()
    {
        $this->servers();
        $this->used();
    }

    private function servers()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INT',
                 'auto_increment' => true
              ),
              'host' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
              ),
              'city' => array(
                'type' => 'varchar',
                'constraint' =>'45',
                'null' => false,
                'default'=>''
                ),
              'state' => array(
                'type' => 'varchar',
                'constraint' =>'45',
                'null' => false,
                'default'=>''
                ),
              'latitude' => array(
                'type' => 'varchar',
                'constraint' =>'45',
                'null' => false,
                'default'=>''
                ),
              'longitude' => array(
                'type' => 'varchar',
                'constraint' =>'45',
                'null' => false,
                'default'=>''
                )
            )
        );
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('servers');

        $this->load->model('Lookup_model', 'lookup');

        $address = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostbyname(gethostname());
        if (!empty($address) && $address != "127.0.0.1") {
            $location = $this->lookup->getLocationByAddress($address);
            $data = array(
            'host' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : gethostname() ,
            'city' => $location->city,
            'state' => $location->state,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            );
            $this->db->insert('servers', $data);
        }
    }

    private function used()
    {
        $this->dbforge->add_field(
            array(
              'id' => array(
                 'type' => 'INT',
                 'auto_increment' => true
              ),
              'server_id' => array(
                 'type' => 'INT',
                 'null' => false
              ),
              'date' => array(
                 'type' => 'timestamp',
                 'null' => false,
                 'default' => 'CURRENT_TIMESTAMP'
              ),
              'port' => array(
                 'type' => 'INT'
              ),
              'pid' => array(
                  'type'=>'INT'
              ),
              'in_use' => array(
                 'type' => 'INT',
                 'default' => 0
              )
            )
        );

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('used');
    }

    public function down()
    {
        $this->dbforge->drop_table('used');
        $this->dbforge->drop_table('servers');
    }
}
