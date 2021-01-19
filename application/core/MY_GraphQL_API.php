<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Point d'entrée pour notre API privée (web service interne au site)
 * Méthode de connexion : authentification pour choper l'access token puis user_credential
 * Exemple d'utilisation : Utilisation interne de notre API (pour notre appli mobile par exemple)
 */
class MY_GraphQL_API extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->add_package_path(APPPATH.'third_party/oauth');
        $this->load->library('oauth_server');
        $this->load->remove_package_path(APPPATH.'third_party/oauth');

        // On vérifie qu'il a les droit d'accéder à cette ressource en fonction de son token et de ses scope(s)
        $this->oauth_server->verifyResourceRequest("private");
    }


    /**
     * Envoi les resultats en format JSON
     * @param  array  $results [description]
     * @return [type]          [description]
     */
    public function sendResponse(array $results)
    {
        $this->output->set_output(json_encode($results));
    }
}
