<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Key_Model extends CI_Model
{
    private $key;

    public function __construct()
    {
        parent::__construct();
    }

    public function getKey()
    {
        $query = $this->db->select('*')
        ->get('keys');
        foreach ($query->result() as $result) {
            return $result;
        }
    }

    public function getAccessOfKey($key)
    {
        $query = $this->db->select('*')
        ->where('key', $key)
        ->get('keys');
        if ($query->num_rows() > 0) {
            return $query->result();
        }
    }
}
