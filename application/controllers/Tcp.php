<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tcp extends CI_Controller
{
    public function index()
    {
        $this->load->helper('url');

        $this->load->view('rest_server');
    }

    public function startNewInstance()
    {
    }
}
