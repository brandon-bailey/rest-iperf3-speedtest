<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Tcp extends REST_Controller
{
    public function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

    public function server_get()
    {
        $this->load->model('Servers_model', 'servers');
        $test = $this->get('test');

        $client = $_SERVER['REMOTE_ADDR'];

        $server = $this->servers->startNewServerInstance($client, 'tcp', $test);
        log_message('debug', 'Server: ' . json_encode($server));
        if (!empty($server)) {
            $this->set_response($server, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->set_response([
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => 'Speedtest server could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function servers_post()
    {
        // $this->some_model->update_user( ... );
        $message = [
            'id' => 100, // Automatically generated by the model
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'message' => 'Added a resource'
        ];

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function servers_delete()
    {
        $this->load->model('Servers_model', 'servers');
        $input = $this->query();
        $this->servers->deleteServerInstance($input);
        //$this->set_response($message, REST_Controller::HTTP_CREATED);
    }


    public function server_post()
    {
        $input = $this->post();
        $this->load->model('Servers_model', 'servers');
        $return = $this->servers->addServer($input);
        
        $this->set_response($return['message'], $return['response_code']);
    }
}