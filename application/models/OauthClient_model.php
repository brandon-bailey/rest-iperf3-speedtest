<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class OauthClient_Model extends CI_Model
{
    private $hybridauth;
    private $oauthConfig;

    public function __construct()
    {
        parent::__construct();

        // initialisation de notre outil hybridauth
        $this->oauthConfig = $this->config->item("oauth");
        $this->hybridauth = new Hybrid_Auth($this->oauthConfig);

        $this->db_oauth = $this->load->database("oauth", true);
    }

    /**
     * Vérifie si le provider qu'on solicite est configuré et enabled dans notre config
     * @param  $provider the provider (facebook, twitter...)
     * @return bool true if provider is enabled
     */
    public function providerEnabled($provider)
    {
        return (isset($this->oauthConfig["providers"][$provider]) && $this->oauthConfig["providers"][$provider]['enabled']);
    }

    /**
     * Lance la méthode d'authentification de hybriauth ou d'oauth server
     * @param  [string] $provider [description]
     * @return [type]           [description]
     */
    public function authenticate($provider)
    {
        // si oauth Demande un code au propriétaire de la ressource distant et le renvoi au client web
        if ($provider == "Oauth") {
            $endpoint = $this->oauthConfig["providers"]["Oauth"]["keys"]["endpoint"];
            $response_type = "code";
            $redirect_uri = $this->oauthConfig["providers"]["Oauth"]["keys"]["redirect_uri"];
            $scope = "public";
            $state = $this->oauthConfig["providers"]["Oauth"]["keys"]["state"];
            $client_id = $this->oauthConfig["providers"]["Oauth"]["keys"]["id"];

            redirect("$endpoint?response_type=$response_type&redirect_uri=$redirect_uri&scope=$scope&state=$state&client_id=$client_id");
            exit;
        }

        return $this->hybridauth->authenticate($provider);
    }


    public function createUser(string $provider, $userProfile, int $userID)
    {
        $providerField = $this->getProviderField($provider);

        if ($providerField == false) {
            return false;
        }

        // create if doesn't exist
        if (empty($user)) {
            $email = (!empty($userProfile->emailVerified)) ? $userProfile->emailVerified : $userProfile->email;

            $dataUser = [
                "{$providerField}" => $userProfile->identifier,
                "user_id" => $userID,
                "enabled" => 1, // autoenable
                "username" => $email,
                "password" => $this->hashPass(randomPassword())
            ];
            $this->db_oauth->insert("oauth_users", $dataUser);
        }


        return false;
    }

    /**
     * Check if the user exists in oauth and create him if not
     * @param  string $provider    [description]
     * @param  object  $userProfile [description]
     * @return [type]              [description]
     */
    public function getUser(string $provider, $userProfile)
    {
        $providerField = $this->getProviderField($provider);

        if ($providerField == false) {
            return false;
        }

        $user = $this->db_oauth
                    ->from("oauth_users")
                    ->where("{$providerField}", $userProfile->identifier)
                    ->get()
                    ->result();

        return $user;
    }

    /**
     * Return the database field corresponding to the provider
     * @var boolean
     */
    private function getProviderField(string $provider)
    {
        $providerField = false;

        switch ($provider) {
            case "Facebook":
                $providerField = "facebook_id";
                break;
            case "Twitter":
                $providerField = "twitter_id";
                break;
            case "Google":
                $providerField = "google_id";
                break;
            case "LinkedIn":
                $providerField = "linkedin_id";
                break;
            case "Live":
                $providerField = "live_id";
                break;
            case "MySpace":
                $providerField = "myspace_id";
                break;
            case "Yahoo":
                $providerField = "yahoo_id";
                break;
        }

        return $providerField;
    }

    /**
     * Generate a password
     * @param  integer $length [description]
     * @return [type]          [description]
     */
    private function randomPassword($length = 12)
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
