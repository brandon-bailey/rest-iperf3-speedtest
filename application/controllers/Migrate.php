<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    public function index()
    {
        // load migration library
        $this->load->library('migration');
        $this->load->model('Servers_model', 'servers');
        if ($this->servers->getDefaultHost() === true) {
            $this->run();
        } else {
            if (is_cli()) {
                echo 'The initial migration must be run from a web interface' . PHP_EOL;
            } else {
                $this->run();
            }
        }
    }

    private function run()
    {
        if (! $this->migration->current()) {
            echo 'Error' . $this->migration->error_string() . PHP_EOL;
        } else {
            echo 'Migrations ran successfully!' . PHP_EOL;
        }
    }
}
