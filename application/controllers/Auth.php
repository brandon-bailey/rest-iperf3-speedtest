<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Auth extends REST_Controller
{
    private $requiredForAuthGeneration = array('username', 'password');

    public function index()
    {
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                $this->config->item('rest_message_field_name') => 'Welcome to the Speedtest REST API'
            ], REST_Controller::HTTP_OK);
    }

    public function generate_post()
    {
        $paramsValid = $this->checkAuthGenerationParams($this->post());
        if ($paramsValid === true) {
            $username = $this->post('username');
            $password = $this->post('password');
            $ip_addresses = $this->post('ip_addresses');
            $realm = $this->config->item('rest_realm');
            $auth = sha1("{$username}:{$realm}:{$password}");
            $return = $this->createNewAuthKey($auth, $username, $ip_addresses);

            if ($return === true) {
                $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                $this->config->item('rest_message_field_name') => 'Successfully created new auth key for: ' . $username,
                'key' => $auth
                ], REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => $return
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $this->set_response([
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => 'Missing ' . $paramsValid['parameter']
                ], $paramsValid['response_type']);
        }
    }

    /**
     * [checkAuthGenerationParams description]
     * @param  post $input the parameters passed in the post body.
     * @return http response code
     */
    private function checkAuthGenerationParams($input)
    {
        $status = true;
        foreach ($this->requiredForAuthGeneration as $requirement) {
            if (!isset($input[$requirement]) || $input[$requirement] === null) {
                $status= false;
                if ($this->config->item('rest_api_debug')) {
                    $error = array('parameter' => $requirement, 'response_type' => REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $error = array('response_type' => REST_Controller::HTTP_BAD_REQUEST);
                }
                return $error;
            }
        }
        return $status;
    }

    private function createNewAuthKey($auth, $username, $ip_addresses)
    {
        $array = array(
            'key' => $auth,
            'username' => $username,
            'level' => 0,
            'ignore_limits' => 1,
            'is_private_key' => 0,
            'date_created' => time(),
            'ip_addresses' => $ip_addresses
            );

        $this->db->insert($this->config->item('rest_keys_table'), $array);

        if ($this->db->affected_rows() !== 1) {
            return $this->db->error();
        } else {
            return true;
        }
    }

    public function enableAccess_post()
    {
    }
}
