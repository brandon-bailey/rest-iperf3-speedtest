<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_oauth extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
        $this->db_oauth = $this->load->database("oauth", true);
        $this->myforge = $this->load->dbforge($this->db_oauth, true);
    }

    public function up()
    {
        if (!$this->db_oauth->table_exists('oauth_access_tokens')) {
            $this->access_tokens();
        }
        if (!$this->db_oauth->table_exists('oauth_authorization_codes')) {
            $this->auth_codes();
        }
        if (!$this->db_oauth->table_exists('oauth_clients')) {
            $this->clients();
        }
        if (!$this->db_oauth->table_exists('oauth_jwt')) {
            $this->jwt();
        }
        if (!$this->db_oauth->table_exists('oauth_refresh_tokens')) {
            $this->refresh_tokens();
        }
        if (!$this->db_oauth->table_exists('oauth_scopes')) {
            $this->oauth_scopes();
        }
        if (!$this->db_oauth->table_exists('oauth_users')) {
            $this->oauth_users();
        }
        if (!$this->db_oauth->table_exists('oauth_users_changepassword')) {
            $this->oauth_users_changepassword();
        }
        if (!$this->db_oauth->table_exists('oauth_users_emailvalidation')) {
            $this->oauth_users_emailvalidation();
        }
    }

    private function access_tokens()
    {
        $this->myforge->add_field(
            array(
                'access_token' => array(
                 'type' => 'varchar',
                 'constraint' => '40',
                 'null' => false
                ),
                'client_id' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'null' => false
                ),
                'user_id' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
                ),
                'expires' => array(
                 'type' => 'timestamp',
                 'null' => false,
                 'default' =>'CURRENT_TIMESTAMP'
                ),
                'scope' => array(
                 'type' => 'TEXT',
                 'default' => null
                )
                )
        );
        $this->myforge->add_key('access_token', true);
        $this->myforge->create_table('oauth_access_tokens');

        $data = array(
                'access_token' => '28f44f6b02f8f254fe1e640a8cdef384bba3f1653',
                'client_id' => 'MY_PRIVATE_APP_DEV',
                'user_id' => '1',
                'expires' => '2017-06-28 17:35:59',
                'scope' => 'private'
        
            );

        $this->db_oauth->insert('oauth_access_tokens', $data);
    }

    private function auth_codes()
    {
        $this->myforge->add_field(
            array(
              'authorization_code' => array(
                 'type' => 'varchar',
                 'constraint' => '40',
                 'null' => false
              ),
              'client_id' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'null' => false
              ),
              'user_id' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'null' => false
              ),
              'redirect_uri' => array(
                 'type' => 'TEXT',
                 'null' => false
              ),
              'expires' => array(
                 'type' => 'timestamp',
                 'null' => false,
                 'default' =>'CURRENT_TIMESTAMP'
              ),
              'scope' => array(
                 'type' => 'TEXT',
                 'default' => null
              )
            )
        );
        $this->myforge->add_key('authorization_code', true);
        $this->myforge->create_table('oauth_authorization_codes');
    }

    private function clients()
    {
        $this->myforge->add_field(
            array(
              'client_id' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'null' => false
              ),
              'client_secret' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'default' => null
              ),
              'redirect_uri' => array(
                 'type' => 'TEXT',
                 'null' => false
              ),
              'grant_types' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'default' => null
              ),
              'scope' => array(
                 'type' => 'varchar',
                 'constraint' => '100',
                 'default' => null
              ),
              'user_id' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'default' => null
              )

            )
        );
        $this->myforge->add_key('client_id', true);
        $this->myforge->create_table('oauth_clients');
    }

    private function jwt()
    {
        $this->myforge->add_field(
            array(
              'client_id' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'null' => false
              ),
              'subject' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'default' => null
              ),
              'public_key' => array(
                 'type' => 'TEXT',
                 'default' => null
              )
            )
        );
        $this->myforge->add_key('client_id', true);
        $this->myforge->create_table('oauth_jwt');
    }

    private function refresh_tokens()
    {
        $this->myforge->add_field(
            array(
              'refresh_token' => array(
                 'type' => 'varchar',
                 'constraint' => '40',
                 'null' => false
              ),
              'client_id' => array(
                 'type' => 'varchar',
                 'constraint' => '80',
                 'null' => false
              ),
              'user_id' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'default' => null
              ),
              'expires' => array(
                 'type' => 'timestamp',
                 'null' => false,
                 'default' => 'CURRENT_TIMESTAMP'
              ),
              'scope' => array(
                 'type' => 'TEXT',
                 'default' => null
              )
            )
        );
        $this->myforge->add_key('refresh_token', true);
        $this->myforge->create_table('oauth_refresh_tokens');
    }

    private function oauth_scopes()
    {
        $this->myforge->add_field(
            array(
              'scope' => array(
                 'type' => 'text'
              ),
              'is_default' => array(
                 'type' => 'tinyint',
                 'constraint' => '1',
                 'default' => null
              )
            )
        );
        $this->myforge->create_table('oauth_scopes');

        $data1 = array('scope' => 'private', 'is_default' => 0);
        $data2 = array('scope' => 'public', 'is_default' => 1);

        $this->db_oauth->insert('oauth_scopes', $data1);
        $this->db_oauth->insert('oauth_scopes', $data2);
    }

    private function oauth_users()
    {
        $this->myforge->add_field(
            array(
              'id' => array(
                 'type' => 'INTEGER'
              ),
              'username' => array(
                 'type' => 'varchar',
                 'constraint' => '255',
                 'unique' => true,
                 'null' => false
              ),
              'email' => array(
                 'type' => 'varchar',
                 'constraint' => '80'
              ),
              'password' => array(
                 'type' => 'TEXT',
                 'default' => null
              ),
              'user_id' => array(
                 'type' => 'int',
                 'constraint' =>'11',
                 'unique' => true,
                 'default' => '0'
              ),
              'enabled' => array(
                 'type' => 'tinyint',
                 'constraint' => '4',
                 'default' => '0'
              ),
              'verified' => array(
                 'type' => 'tinyint',
                 'constraint' => '4',
                 'default' => '0'
              ),
              'removed' => array(
                 'type' => 'tinyint',
                 'constraint' => '4',
                 'default' => '0'
              ),
              'facebook_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              ),
              'twitter_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              ),
              'google_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              ),
              'linkedin_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              ),
              'live_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              ),
              'myspace_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              ),
              'yahoo_id' => array(
                 'type' => 'varchar',
                 'constraint' => '128',
                 'unique' => true,
                 'default' => null
              )
            )
        );
        $this->myforge->add_key('id', true);
        $this->myforge->add_key('username');
        $this->myforge->add_key('user_id');
        $this->myforge->add_key('facebook_id');
        $this->myforge->add_key('twitter_id');
        $this->myforge->add_key('google_id');
        $this->myforge->add_key('linkedin_id');
        $this->myforge->add_key('live_id');
        $this->myforge->add_key('myspace_id');
        $this->myforge->add_key('yahoo_id');
        $this->myforge->create_table('oauth_users');
    }

    private function oauth_users_changepassword()
    {
        $this->myforge->add_field(
            array(
              'id' => array(
                 'type' => 'INTEGER'
              ),
              'oauth_users_id' => array(
                 'type' => 'int',
                 'constraint' => '11',
                 'default' => '0'
              ),
              'dateinsert' => array(
                 'type' => 'datetime',
                 'default' => null
              ),
              'datedone' => array(
                 'type' => 'datetime',
                 'default' => null
              )
            )
        );
        $this->myforge->add_key('id', true);
        $this->myforge->create_table('oauth_users_changepassword');
    }

    private function oauth_users_emailvalidation()
    {
        $this->myforge->add_field(
            array(
              'id' => array(
                 'type' => 'INTEGER'
              ),
              'oauth_users_id' => array(
                 'type' => 'int',
                 'constraint' => '11',
                 'default' => '0'
              ),
              'dateinsert' => array(
                 'type' => 'datetime',
                 'default' => null
              )
            )
        );
        $this->myforge->add_key('id', true);
        $this->myforge->create_table('oauth_users_emailvalidation');
    }

    public function down()
    {
        $this->myforge->drop_table('oauth_access_tokens', true);
        $this->myforge->drop_table('oauth_authorization_codes', true);
        $this->myforge->drop_table('oauth_clients', true);
        $this->myforge->drop_table('oauth_jwt', true);
        $this->myforge->drop_table('oauth_refresh_tokens', true);
        $this->myforge->drop_table('oauth_scopes', true);
        $this->myforge->drop_table('oauth_users', true);
        $this->myforge->drop_table('oauth_users_changepassword', true);
        $this->myforge->drop_table('oauth_users_emailvalidation', true);
    }
}
