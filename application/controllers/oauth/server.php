<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Server extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper("url");
        $this->load->model('OauthServer_model', 'oauth_server');
        // pour le cross domain
        /**
         *
         * Your code is actually attempting to make a Cross-domain (CORS) request, not an ordinary POST.
         * That is: Modern browsers will only allow Ajax calls to services in the same domain as the HTML page.
         * Example: A page in http://www.example.com/myPage.html can only directly request services that are in http://www.example.com, like http://www.example.com/testservice/etc. If the service is in other domain, the browser won't make the direct call (as you'd expect). Instead, it will try to make a CORS request.
         * To put it shortly, to perform a CORS request, your browser:
         * Will first send an OPTION request to the target URL
         * And then only if the server response to that OPTION contains the adequate headers (Access-Control-Allow-Origin is one of them) to allow the CORS request, the browse will perform the call (almost exactly the way it would if the HTML page was at the same domain).
         * If the expected headers don't come, the browser simply gives up (like it did to you).
         * How to solve it? The simplest way is to enable CORS (enable the necessary headers) on the server.
         */
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        if (!empty($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "OPTIONS") {
            exit();
        }
    }
    /**
     * Récupération d'un token avec la méthode client credentials
     * @return [type] [description]
     */
    public function clientCredentials()
    {
        $this->oauth_server->client_credentials();
    }
    /**
     * Récupération d'un token avec la méthode user credentials
     * @return [type] [description]
     */
    public function userCredentials()
    {
        $this->oauth_server->user_credentials();
    }
    public function refreshToken()
    {
        $this->oauth_server->refresh_token();
    }
    /**
     * Demande un code au propriétaire de la ressource
     * @return [type] [description]
     */
    public function authorize()
    {
        // false si on est à l'authentification du user, true s'il a donné la permission
        $authorized = $this->input->post("authorized");
        // si il n'a pas d'autorisation encore
        if (empty($authorized)) {
            $this->oauth_server->validateAuthorizeRequest();
            // Affiche le formulaire de connexion & d'autorisation sur le site de ressource
            $data = array(
                "scope" => $this->input->get("scope"),
                "state" => $this->input->get("state"),
                "client_id" => $this->input->get("client_id"),
                "redirect_uri" => $this->input->get("redirect_uri"),
                "response_type" => "code",
            );
            $this->load->view("authorize", $data);
        } else {
            // si il a déjà autorisé le client (l'appli) à se connecter à sa place on va chercher le code
            if ($authorized != true) {
                return false;
            }
            $this->oauth_server->authorize_getcode(true);
        }
    }
    /**
     * Utilise le code d'authorisation pour avoir un access_token
     * @return [type] [description]
     */
    public function token()
    {
        // Sécurité : vérifie que les infos de l'url sont ok !
        $this->oauth_server->authorize_getAccessToken();
    }
    /**
     * Méthode de test pour valider la récupération de ressource
     * @return [type] [description]
     */
    public function getResource()
    {
        // On vérifie qu'il a les droit d'accéder à cette ressource en fonction de son / ses scope(s)
        $this->oauth_server->verifyResourceRequest("public");
        // si ok on lui retourne un succès
        echo json_encode(array('success' => true, 'message' => 'You can access APIs!'));
    }
}
