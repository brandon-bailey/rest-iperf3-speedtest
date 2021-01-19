<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class OauthServer_Model extends CI_Model
{
    public function __construct($config = array())
    {
        parent::__construct();
        $this->load->model('SqliteOauth_model', 'SqliteOauth');
        // to sync the session duration time and the access token validation
        $accessLifeTime = $this->config->item("sess_expiration");

        OAuth2\Autoloader::register();

        $this->db_oauth = $this->load->database("oauth", true);
        
        $this->SqliteOauth->setDatabase($this->db_oauth);
        
        $this->storage = $this->SqliteOauth;
        
        $this->server = new OAuth2\Server(
            $this->storage,
            [
            'allow_implicit' => true,
            'access_lifetime' => $accessLifeTime
            ]
        );

        $this->request = OAuth2\Request::createFromGlobals();

        // si c'est un appel de type fecth en react native les param sont passés en chaine JSON il faut les traiter
        $raw_input_stream = $this->input->raw_input_stream;
        if (!empty($raw_input_stream) && $this->isJSON($raw_input_stream)) {
            $stream_clean = $this->security->xss_clean($raw_input_stream);
            $request = json_decode($stream_clean, true);
            $this->request->request = $request;
        }
    }

    private function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
    * The Client Credentials grant type is used when the client is requesting access to protected resources under its control (i.e. there is no third party).
    * @link https://bshaffer.github.io/oauth2-server-php-docs/grant-types/client-credentials/
    */
    public function client_credentials()
    {
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($this->storage, array(
            "allow_credentials_in_request_body" => true
        )));
        $this->server->handleTokenRequest($this->request)->send();
    }

    /**
    *
    */
    public function user_credentials($return = false)
    {
        if (empty($this->request->request["username"]) || empty($this->request->request["password"])) {
            $this->server->handleTokenRequest($this->request)->send();
            exit;
        }

        // on check les credentials du user
        $this->storage->checkUserCredentials($this->request->request["username"], $this->request->request["password"]);

        $this->server->addGrantType(new OAuth2\GrantType\UserCredentials($this->storage));

        if ($return === false) {
            $this->server->handleTokenRequest($this->request)->send();
        } else {
            return $this->server->handleTokenRequest($this->request);
        }
    }

    /**
    * refresh_token
    */
    public function refresh_token()
    {
        $this->server->addGrantType(new OAuth2\GrantType\RefreshToken($this->storage, array(
            "always_issue_new_refresh_token" => true,
            "unset_refresh_token_after_use" => true,
            "refresh_token_lifetime" => 2419200,
        )));
        $this->server->handleTokenRequest($this->request)->send();
    }

    public function verifyResourceRequest($scope = "")
    {
        $this->response = new OAuth2\Response();
        if (!$this->server->verifyResourceRequest($this->request, $this->response, $scope)) {
            $this->server->getResponse()->send();
            exit;
        }
    }


    public function validateAuthorizeRequest()
    {
        $this->response = new OAuth2\Response();
        if (!$this->server->validateAuthorizeRequest($this->request, $this->response)) {
            $this->response->send();
            exit;
        }
    }

    /**
     * le propriétaire a autorisé le client à se connecter, on renvoie donc vers une URL avec un code d'authorisation
     * @param  boolean $is_authorized [description]
     * @return [type]                 [description]
     */
    public function authorize_getcode($is_authorized)
    {
        $this->response = new OAuth2\Response();
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($this->storage));
        $this->server->handleAuthorizeRequest($this->request, $this->response, $is_authorized);

        if (strpos($this->response->getHttpHeader('Location'), "error_description") !== false) {
            die($this->response->getHttpHeader('Location'));
        }

        if ($is_authorized) {
            $code = substr($this->response->getHttpHeader('Location'), strpos($this->response->getHttpHeader('Location'), 'code=')+5, 40);

            header("Location: ".$this->response->getHttpHeader('Location'));
            exit;
        }
        $this->response->send();
    }

    /**
     * Le client demande un token d'accés à l'aide du code d'autorisation obtenu
     * @return [type] [description]
     */
    public function authorize_getAccessToken()
    {
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($this->storage));
        $this->server->handleTokenRequest($this->request)->send();
    }
}
