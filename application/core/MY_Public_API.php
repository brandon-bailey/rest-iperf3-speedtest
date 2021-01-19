<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Point d'entrée pour notre API publique (web service accessible pour certaines application)
 * Méthode de connexion : authentification pour choper l'access token puis client_credential
 * Exemple d'utilisation : Un site de confiance utilise un morceau de notre API
 */
class MY_Public_API extends Restserver_controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->add_package_path(APPPATH.'third_party/oauth');
        $this->load->library('oauth_server');
        $this->load->remove_package_path(APPPATH.'third_party/oauth');

        // On vérifie qu'il a les droit d'accéder à cette ressource en fonction de son access token et de ses scope(s)
        $this->oauth_server->verifyResourceRequest("public");
    }

    public function get()
    {
        $this->restserver->response();
    }

    public function post()
    {
        $this->restserver->response();
    }

    public function put()
    {
        $this->restserver->response();
    }

    public function delete()
    {
        $this->restserver->response();
    }
}
