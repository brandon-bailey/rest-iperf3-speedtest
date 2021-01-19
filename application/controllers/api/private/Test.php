<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Test extends MY_Private_API
{
    public function __construct()
    {
        parent::__construct();
        $fields = [];
        // Configuration d'un champ
        $fields[] = new Restserver_field([
            'input' => 'id',
            'rules' => 'required_get|integer'
        ]);
        // Applique la configuration
        $this->restserver->add_field($fields);
    }
    public function get()
    {
        $response = $this->restserver->protocol();
        $id = $this->restserver->get('id');
        $response['status'] = true;
        $response['value'] = "Success : ".$id.";)";
        $this->restserver->response($response, 201);
    }
}
