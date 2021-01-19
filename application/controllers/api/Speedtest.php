<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Speedtest extends REST_Controller
{
    public function index()
    {
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                'message' => 'Welcome to the Speedtest REST API'
            ], REST_Controller::HTTP_OK);
    }

    public function results_post()
    {
        $input = $this->post();
        $input['date'] = date("Y-m-d H:i:s");
        $input['host'] = $_SERVER['REMOTE_ADDR'];
        $results = $input['results'];
        if (!empty($results['upload']) && !empty($results['download'])) {
            $this->load->model('Speedtest_model', 'speedtest');
            $return = $this->speedtest->saveResults($input);
            $this->set_response($return, REST_Controller::HTTP_OK);
        } else {
            $this->set_response([
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => 'Missing parameter'
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
