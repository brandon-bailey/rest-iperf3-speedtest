<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Servers extends REST_Controller
{
    public function index()
    {
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                'message' => 'Welcome to the Speedtest REST API'
            ], REST_Controller::HTTP_OK);
    }
    
    public function cleanUpServers()
    {
        $this->load->model('Servers_model', 'servers');
        if (is_cli()) {
            echo $this->servers->cleanUpServers();
        }
    }

    public function list_get()
    {
        $this->load->model('Servers_model', 'servers');
        $this->load->model('Lookup_model', 'lookup');
        $list = $this->servers->getAvailableServers();
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                'message' => $list
                ], REST_Controller::HTTP_OK);
    }

    public function location_get()
    {
        $this->load->model('Servers_model', 'servers');
        $name = $this->get('hostname');
        $ip = $this->get('ip');

        if (!empty($name)) {
            $location = $this->servers->getLocationByName($name);
            $this->set_response($location, REST_Controller::HTTP_OK);
        } elseif (!empty($ip)) {
            $location = $this->servers->getLocationByName($ip);
            $this->set_response($location, REST_Controller::HTTP_OK);
        } else {
            $this->set_response([
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => "Missing parameter"
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function closest_get()
    {
        $this->load->model('Servers_model', 'servers');
        $servers = $this->servers->getClosestServer($_SERVER['REMOTE_ADDR']);
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                'message' => $servers
                ], REST_Controller::HTTP_OK);
    }

    public function running_get()
    {
        $this->load->model('Servers_model', 'servers');
        $resources = $this->servers->getRunningServers();
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                'message' => $resources
                ], REST_Controller::HTTP_OK);
    }

    /**
     * Set the specified server to be not running.
     * @return [type] [description]
     */
    public function running_put()
    {
        $input = $this->put();
        if (!empty($input)) {
            $this->load->model('Servers_model', 'servers');
            $return = $this->servers->stopServer($input);
            $this->set_response([
                $this->config->item('rest_status_field_name') => $return['status'],
                $this->config->item('rest_message_field_name') => $return['message']
                ], $return['response_code']);
        } else {
            $this->set_response([
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => "Missing parameter"
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function update_put()
    {
        $input = $this->put();
        $this->load->model('Servers_model', 'servers');
        $return = $this->servers->updateServers($input);
        $this->set_response($return['message'], $return['response_code']);
    }
}
