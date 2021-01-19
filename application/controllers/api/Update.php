<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Update extends REST_Controller
{
    public function index()
    {
        $this->set_response([
                $this->config->item('rest_status_field_name') => true,
                'message' => 'Welcome to the Speedtest REST API'
            ], REST_Controller::HTTP_OK);
    }

    public function tag_post()
    {
        $input = $this->post();
        log_message('debug', json_encode($input));
        $this->load->model('Servers_model', 'servers');
        $slaves = $this->servers->getAvailableServers();
        foreach ($slaves as $slave) {
            if ($slave->host !== $_SERVER['SERVER_NAME']) {
                $curl = new Curl\Curl();
                $curl->get('https://' . $slave->host . '/api/update/download');
                $curl->setHeader('API-KEY', $this->rest->key);
                log_message('debug', 'REST KEY: ' . $this->rest->key);
                if ($curl->error) {
                    log_message('error', $curl->errorCode . ': ' . $curl->errorMessage);
                } else {
                    log_message('debug', 'Successfully called api endpoint for ' . $slave->host . ' to update itself');
                }
            }
        }
        $this->load->model('Update_model', 'update');
        $this->update->download();
        $this->set_response(true, REST_Controller::HTTP_OK);
    }

    public function commit_post()
    {
        $input = $this->post();
        log_message('debug', json_encode($input));
        $this->set_response(true, REST_Controller::HTTP_OK);
    }

    public function download_get()
    {
        log_message('debug', 'Got the call to update myself');
    }
}
