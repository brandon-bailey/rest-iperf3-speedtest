<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Speedtest_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function saveResults($results)
    {
        $mac = isset($results['mac']) ? $results['mac'] : null;
        $array = array(
            'mac' => $mac,
            'host' => $results['host'],
            'date' => $results['date'],
            'results' => json_encode($results['results'])
            );
        $this->db->insert('speedtest', $array);
        if ($this->db->affected_rows() !== 1) {
            $array = array(
                $this->config->item('rest_status_field_name') => false,
                $this->config->item('rest_message_field_name') => $this->db->error()
                );
            return $array;
        } else {
            $array = array(
                $this->config->item('rest_status_field_name') => true,
                'message' => 'Successfully added the speedtest results'
                );
            return $array;
        }
    }
}
