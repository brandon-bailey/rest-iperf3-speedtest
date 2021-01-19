<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class OauthWeb_Model extends CI_Model
{
    /** Code igniter instance **/
    private $ciInstance;

    /** oauth config file variables **/
    private $oauthConfig;

    private $db_oauth;

    public function __construct()
    {
        parent::__construct();

        $this->load->library(["session", "form_validation", "encryption"]);
        $this->load->helper(["cookie", "date"]);
        $this->load->model('OauthServer_model', 'oauth_server');

        $this->load->config("oauth");
        $this->oauthConfig = $this->config->item("oauth");
        $this->db_oauth = $this->load->database("oauth", true);
        $this->load->model('OauthLib_model', 'oauth_lib');

        // si on a perdu la session on check si le cookie d'autoconnexion est présent
        if (empty($this->session->user_id)) {
            $this->checkCookie();
        }
    }

    private function checkCookie()
    {
        $this->load
           ->add_package_path(APPPATH.'third_party/restclient')
           ->library('restclient')
           ->remove_package_path(APPPATH.'third_party/restclient');

        // vérifie si un cookie de connexion existe et autolog l'utilisateur
        $loginCookie = get_cookie($this->oauthConfig["web"]["login_cookie_name"], true);

        // si cookie présent
        if (empty($loginCookie)) {
            return false;
        }

        // on le décrypte
        $decryptedCookie = $this->encryption->decrypt(base64_decode($loginCookie));

        $retour = false;
        $cookieParts = explode(":", $decryptedCookie);
        $id = (int) $cookieParts[0];
        $username = $cookieParts[1];
        $password = $cookieParts[2];

        $response = $this->restclient->post(site_url('oauth/web/login'), [
           'grant_type' => "password",
           'username' => $username,
           'password' => $password,
           'client_id' => $this->oauthConfig['web']["client_id"],
           'client_secret' => $this->oauthConfig['web']["client_secret"],
           'scope' => "private",
        ]);

        if (!empty($response) && $response["status"] === true) {
            redirect();
            exit;
        }
    }


    public function need2BeConnected($redirect = true)
    {
        if (!$this->isConnected()) {
            if ($redirect === true) {
                redirect();
            } else {
                echo "You need to be loged in!";
            }

            exit;
        }
    }



    public function isConnected()
    {
        return (!empty($this->session->user_id));
    }


    public function login($username, $password, $oauthServerParams, $keepmeconnected = false)
    {
        $this->oauth_lib->callCustomFunction("pre_login");

        $results = $this->db
                            ->from("oauth_users")
                            ->where("username", $username)
                            ->where("removed", 0)
                            ->get()
                            ->row();

        if (!$this->verifyPassword($password, $row->password)) {
            return false;
        }

        if (empty($results)) {
            return false;
        }

        $user = $results[0];

        $this->setConnectionSession($oauthServerParams["access_token"], $user->user_id, $user->verified);

        // choix d'une connexion persistante (création d'un cookie que l'on crypte)
        if ($keepmeconnected == true) {
            $autoLoginStr = $user->id . ":" . $user->username . ":". $password;
            $cookie_value = base64_encode($this->encryption->encrypt($autoLoginStr));

            $cookie = [
                'name' => $this->oauthConfig["web"]["login_cookie_name"],
                'value' => $cookie_value,
                'expire' => '86500',
            ];

            set_cookie($cookie);
        }

        $this->oauth_lib->callCustomFunction("post_login", ["user_id" => $user->user_id]);

        return $user;
    }

    public function logout()
    {
        // destruction complète de la session
        $this->session->sess_destroy();

        // destruction du cookie d'autoconnexion
        delete_cookie($this->oauthConfig["web"]["login_cookie_name"]);

        // redirection
        redirect();
    }

    public function signUp($username, $email, $password)
    {
        $preParams = [
            "username" => $username,
            "email" => $email,
        ];

        $password = $this->hashPass($password);

        // NEED TO RETURN THE USER_ID !
        $user_id = $this->oauth_lib->callCustomFunction("pre_signup", $preParams);

        if (empty($user_id)) {
            return false;
        }

        $this->login($username, $password);

        // ajout d'une entrée pour notre oauth2 server
        $entry = [
            "username" => $username,
            "password" => $password,
            "user_id" => $user_id
        ];

        $insertResult = $this->db_oauth->insert("oauth_users", $entry);

        if (!$insertResult) {
            return false;
        }

        $oauth_user_id = $this->db_oauth->insert_id();

        // a link to validate the email
        $validationLink = $this->getActivationLink($oauth_user_id, $email);

        $postParams = [
            "user_id" => $user_id,
            "email" => $email,
            "link" => $validationLink,
            "password" => $password
        ];

        // envoi d'un email de validation d'email par exemple (avec un lien de validation)
        $this->oauth_lib->callCustomFunction("post_signup", $postParams);

        return true;
    }

    /**
     * Generate a link to validate sign up
     * @return {[type] [description]
     */
    public function getActivationLink($oauth_user_id, $email)
    {
        $entry = [
            "oauth_users_id" => $oauth_user_id,
            "dateinsert" => date("Y-m-d H:i:s"),
        ];

        $insertResult = $this->db_oauth->insert("oauth_users_emailvalidation", $entry);

        $id_log = $this->db_oauth->insert_id();

        // generate a token
        $token = $this->doubleHash($email . $this->oauthConfig["web"]["oauthSalt"]) . '/' . $id_log;
        
        $link = site_url('oauth/web/activate/' . $token);

        return $link;
    }

    public function activate($emailHash, $logID)
    {
        if (empty($logID) || empty($emailHash) || $logID != (int) $logID) {
            return false;
        }

        $results = $this->db_oauth
                            ->from("oauth_users_emailvalidation")
                            ->join("oauth_users", "oauth_users.id = oauth_users_emailvalidation.oauth_users_id", "INNER")
                            ->where("oauth_users_emailvalidation.id", $logID)
                            ->get()
                            ->result();

        if (empty($results)) {
            return false;
        }

        $demand = $results[0];

        // verification
        if ($this->doubleHash($demand->username . $this->oauthConfig["web"]["oauthSalt"]) == $emailHash) {
            if ($demand->verified == 0) {
                // set the account to the verified status
                $this->db_oauth->where("id", $demand->id)->update("oauth_users", ["verified" => 1]);

                $postActivation = [
                    "user_id" => $demand->user_id
                ];
                $this->session->set_userdata('is_verified', true);
                $this->oauth_lib->callCustomFunction("post_activation", $postActivation);
            }
            return true;
        }

        return false;
    }

    /**
     * Send the activation email
     * @param  [type]  $user_id [description]
     * @param  [type]  $email   [description]
     * @return {[type]          [description]
     */
    public function reActivate($userID, $email)
    {
        // on vérifie qu'il y a bien une demande d'activation
        $results = $this->db_oauth
                            ->from("oauth_users")
                            ->where("user_id", $userID)
                            ->where("username", $email)
                            ->get()
                            ->result();

        if (empty($results)) {
            return false;
        }

        $user = $results[0];

        if ($user->email != $email) {
            return false;
        }

        // a link to validate the email
        $validationLink = $this->getActivationLink($user->id, $email);

        $params = [
            "user_id" => $userID,
            "email" => $email,
            "link" => $validationLink
        ];

        $this->oauth_lib->callCustomFunction("pre_activation", $params);

        return true;
    }

    /**
     * [setConnectionSession description]
     * @param [type] $user [description]
     */
    private function setConnectionSession($accessToken, $userID, $isVerified)
    {
        $this->session->set_userdata('access_token', $accessToken);
        $this->session->set_userdata('user_id', $userID);
        $this->session->set_userdata('is_verified', $isVerified);
    }

    /**
     * Prépare le lien de changement de mot de passe et envoi d'un email
     * @return {[type] [description]
     */
    public function prepareChangePassword($email)
    {

        // on vérifie qu'il y a bien une demande d'activation
        $results = $this->db_oauth
                            ->from("oauth_users")
                            ->where("username", $email)
                            ->get()
                            ->result();

        if (empty($results)) {
            return false;
        }

        $user = $results[0];

        // insertion d'un log

        $entry = [
            "oauth_users_id" => $user->id,
            "dateinsert" => date("Y-m-d H:i:s"),
        ];

        $insertResult = $this->db_oauth->insert("oauth_users_changepassword", $entry);
        $logID = $this->db_oauth->insert_id();

        // génération d'un lien de changement de mot de passe
        $token = $this->doubleHash($email . $this->oauthConfig["web"]["oauthSalt"]) . '/' . $logID;
        $link = site_url($this->oauthConfig["web"]["changePasswordPage"]."/". $token);

        $params = [
            "user_id" => $user->user_id,
            "email" => $email,
            "link" => $link
        ];

        if ($this->oauth_lib->callCustomFunction("email_changePassword", $params) === true) {
            return true;
        }

        return false;
    }

    /**
     * Validation of the changing password link (in the changing password email)
     * @return {[type]            [description]
     */
    public function validatePasswordLink()
    {
        $hashEmail = $this->uri->segment(3);
        $logID = $this->uri->segment(4);

        if (empty($hashEmail) || $logID == 0 || $logID != (int) $logID) {
            exit;
        }

        $results = $this->db_oauth
                            ->select("oauth_users_changepassword.id, oauth_users.username, oauth_users.user_id, oauth_users_changepassword.datedone, oauth_users_changepassword.dateinsert")
                            ->from("oauth_users_changepassword")
                            ->join("oauth_users", "oauth_users_changepassword.oauth_users_id = oauth_users.id", "INNER")
                            ->where("oauth_users_changepassword.id", $logID)
                            ->where("oauth_users_changepassword.datedone IS NULL")
                            ->get()
                            ->result();

        if (empty($results)) {
            return false;
        }

        $logEntry = $results[0];

        // si on a déjà utilisé le lien
        if (!empty($logEntry->datedone)) {
            return false;
        }

        $time = strtotime($logEntry->dateinsert);
        $date = mktime(date("H", $time), date("i", $time), date("s", $time), date("n", $time), date("d", $time) + 1, date("Y", $time));
        if (strtotime(date("Y-m-d")) > $date) { // 24 heures pour changer
            return false;
        }

        if ($this->doubleHash($logEntry->username . $this->oauthConfig["web"]["oauthSalt"]) == $hashEmail) {
            $logEntry->actionlink = site_url("oauth/web/changePasswordValidation/$hashEmail/$logID");

            return $logEntry;
        }
    }

    /**
     * Validation du mot de passe changé après un oubli
     */
    public function changePassword($logID, $newPassword)
    {
        $status = false;

        // vérification de la demande
        $results = $this->db_oauth
                            ->from("oauth_users_changepassword")
                            ->where("id", $logID)
                            ->get()
                            ->result();

        if (empty($results)) {
            return false;
        }

        $log = $results[0];

        if (!empty($log->datedone)) {
            return false;
        }

        // update de la date d'action
        $this->db_oauth->where("id", $logID)->update("oauth_users_changepassword", ["datedone" => date("Y-m-d H:i:s")]);

        $status = $this->db_oauth->where("id", $log->oauth_users_id)->update("oauth_users", ["password" => $this->hashPass($newPassword)]);

        return $status;
    }

    public function sendEmailSignUp($params)
    {
        $this->load->library('email');


        $body = $this->email->full_html('Email validation from Oauth Server', $params['link']);

        $result = $this->email
        ->from('oauth@rmrcom.com')
        ->to($params['email'])
        ->subject($subject)
        ->message($body)
        ->send();

        if (!$result) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $this->email->print_debugger();
            return false;
        }
        return true;
    }

    public function sendEmailValidation($params)
    {
        $this->load->library('email');


        $body = $this->email->full_html('Email validation from Oauth Server', $params['link']);

        $result = $this->email
        ->from('oauth@rmrcom.com')
        ->to($params['email'])
        ->subject($subject)
        ->message($body)
        ->send();

        if (!$result) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $this->email->print_debugger();
            return false;
        }
        return true;
    }

    private function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function hashPass($pass)
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    private function doubleHash($word)
    {
        return sha1(md5($word));
    }
}
