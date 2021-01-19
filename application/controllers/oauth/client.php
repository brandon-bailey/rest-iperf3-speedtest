<?php
/**
* @package  oauth
* @author   arnouxor
**/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Client extends CI_Controller
{
    /** les variable de config */
    private $oauthConfig;
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper("url");
        // Chargement du config file
        $this->load->config("oauth2");
        $this->oauthConfig = $this->config->item("oauth");
        $this->load->model('OauthWeb_model', 'oauth_web');
        $this->load->model('OauthServer_model', 'oauth_server');
        $this->load->model('OauthLib_model', 'oauth_lib');
        $this->load->model('OauthClient_model', 'oauth_client');
    }
    /**
     * Try logging in with the specified provider
     * @param  [string] $provider the provider
     * @return [type]           [description]
     */
    public function login($provider = null)
    {
        $provider = ucfirst(trim($provider));
        try {
            if ($this->oauth_client->providerEnabled($provider)) {
                $service = $this->oauth_client->authenticate($provider);
                if (!empty($service) && $service->isUserConnected()) {
                    $userProfile = $service->getUserProfile();
                    // check if we got the user inside the oauth database
                    $user = $this->oauth_client->getUser($provider, $userProfile);
                    if (empty($user)) {
                        // load your method which create the user in your database if he doesn't exist
                        $userID = $this->oauth_lib->callCustomFunction("createUser", ["provider" => $provider, "userProfile" => $userProfile]);
                        if (empty($userID)) {
                            echo json_encode(array('success' => false, 'status' => 'error', 'message' => 'Your Creation of user failed'));
                            die;
                        }
                        // load your method which create the user in your database if he doesn't exist
                        if (!$this->oauth_client->createUser($provider, $userProfile, $userID)) {
                            return false;
                        }
                    } else {
                        $userID = $user->id;
                    }
                    $this->oauth_lib->callCustomFunction("post_login", ["user_id", $userID]);
                    redirect();
                    return true;
                } else { // Cannot authenticate user
                    show_error('Cannot authenticate user');
                }
            } else { // This service is not enabled.

                echo json_encode(array('success' => false, 'status' => 'error', 'message' => 'Provider is not enabled!'));
                die;
            }
        } catch (Exception $e) {
            $error = 'Unexpected error';
            switch ($e->getCode()) {
                case 0:
                    $error = 'Unspecified error.';
                    break;
                case 1:
                    $error = 'Hybriauth configuration error.';
                    break;
                case 2:
                    $error = 'Provider not properly configured.';
                    break;
                case 3:
                    $error = 'Unknown or disabled provider.';
                    break;
                case 4:
                    $error = 'Missing provider application credentials.';
                    break;
                case 5:
                    //redirect();
                    if (isset($service)) {
                        $service->logout();
                    }
                    show_error('User has cancelled the authentication or the provider refused the connection.');
                    break;
                case 6:
                    $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
                    break;
                case 7:
                    $error = 'User not connected to the provider.';
                    break;
            }
        }
        if (isset($service)) {
            $service->logout();
        }
        show_error('Error authenticating user.' . $error);
    }
    /**
     * on va autoposter sur le oauth server pour avoir un access token avec le code d'authorisation que l'on a
     * @return [type] [description]
     */
    public function authorize()
    {
        $this->load
           ->add_package_path(APPPATH.'third_party/restclient')
           ->library('restclient')
           ->remove_package_path(APPPATH.'third_party/restclient');

        $this->load->helper('url');

        $response = $this->restclient->post(site_url('oauth/server/token'), [
           'code' => $this->input->get("code"),
           'state' => $this->input->get("state"),
           'client_id' => $this->oauthConfig['providers']["Oauth"]["keys"]["id"],
           'client_secret' => $this->oauthConfig['providers']["Oauth"]["keys"]["secret"],
           'grant_type' => "authorization_code",
           'scope' => "public",
           'redirect_uri' => $this->oauthConfig['providers']["Oauth"]["keys"]["redirect_uri"],
        ]);

        //$this->restclient->debug();
        // si on a l'access token on redirige sur la page principale en le mettant en session
        if (!empty($response) && !empty($response["access_token"])) {
            redirect();
        }
    }
    /**
     * HybridAuth need to do some redirection so we create a endpoint here
     * @return [type] [description]
     */
    public function endpoint()
    {
        include_once FCPATH . 'vendor/hybridauth/hybridauth/hybridauth/index.php';
    }
}
