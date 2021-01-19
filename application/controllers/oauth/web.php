<?php
/**
* @package  oauth
* @author   arnouxor
**/
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Web extends CI_Controller
{
    /** les variable de config */
    private $oauthConfig;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper("url");
        $this->load->model('OauthWeb_model', 'oauth_web');
        $this->load->model('OauthServer_model', 'oauth_server');
        $this->load->config("oauth2");
        $this->oauthConfig = $this->config->item("oauth");
    }
    /**
     * Validate the user connection form
     * @return [type] [description]
     */
    public function login()
    {
        $response = $this->response();
        $config = [
            ['field' => 'username', 'label' => '', 'rules' => 'trim|required|min_length[4]|max_length[100]|valid_email'],
            ['field' => 'password', 'label' => '', 'rules' => 'trim|required|min_length[8]|max_length[50]'],
            ['field' => 'keepmeconnected', 'label' => '', 'rules' => 'trim|integer'],
        ];
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() === false) {
            $response["errors"] = $this->form_validation->error_array();
            $this->output->set_output(json_encode($response));
            return false;
        }
        $redirect = false;
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $keepmeconnected = $this->input->post('keepmeconnected');

        $oaut2auth = $this->oauth_server->user_credentials(true);
        if ($oaut2auth->getStatusCode() == 200 && $this->oauth_web->login($username, $password, $oaut2auth->getParameters(), $keepmeconnected == true)) {
            $response = array_merge($response, $oaut2auth->getParameters());
            // if success
            $response["msg"] = "Successful";
            $response["status"] = true;
            $response["redirect"] = site_url();
        } else {
            $response["errors"]["username"] = $this->lang->line("bad_user_pass_combinaison");
        }
        $this->output->set_output(json_encode($response));
    }

    public function createAccount()
    {
        // false si on est à l'authentification du user, true s'il a donné la permission
        $authorized = $this->input->post("authorized");
        // si il n'a pas d'autorisation encore
        if (empty($authorized)) {
            // $this->oauth_server->validateAuthorizeRequest();
            // Affiche le formulaire de connexion & d'autorisation sur le site de ressource
            $data = array(
                "scope" => $this->input->get("scope"),
                "state" => $this->input->get("state"),
                "client_id" => $this->input->get("client_id"),
                "redirect_uri" => $this->input->get("redirect_uri"),
                "response_type" => "code",
            );
            $this->load->view("signup", $data);
        } else {
            // si il a déjà autorisé le client (l'appli) à se connecter à sa place on va chercher le code
            if ($authorized != true) {
                return false;
            }
            $this->oauth_server->authorize_getcode(true);
        }
    }

    /**
     * Validate user registration
     * @return [type] [description]
     */
    public function signUp()
    {
        if (!$this->oauth_web->isConnected()) {
            $response = $this->response();
            $config = [
                ['field' => 'signup_username', 'label' => 'username', 'rules' => 'trim|required|min_length[2]|max_length[50]|callback__alpha_dash_extra'],
                ['field' => 'signup_email', 'label' => 'email', 'rules' => 'trim|required|min_length[4]|max_length[100]|valid_email|callback_checkSignUpEmail'],
                ['field' => 'signup_password', 'label' => 'password', 'rules' => 'trim|required|min_length[8]|max_length[50]'],
                ['field' => 'signup_repassword', 'label' => 'password', 'rules' => 'trim|required|matches[signup_password]|min_length[8]|max_length[50]']
            ];
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() === false) {
                $response["errors"] = $this->form_validation->error_array();
                $this->output->set_output(json_encode($response));
                return false;
            }
            $username = $this->input->post('signup_username');
            $email = $this->input->post('signup_email');
            $password = $this->input->post('signup_password');
            if ($this->oauth_web->signUp($username, $email, $password) === true) {
                $response["status"] = true;
                $response["redirect"] = site_url();
            }
        }
        $this->output->set_output(json_encode($response));
    }
    /**
     * Logout the user
     * @return [type] [description]
     */
    public function logout()
    {
        $this->oauth_web->logout();
    }
    /**
     * Validate user email (account)
     * @return [type] [description]
     */
    public function activate()
    {
        $response = $this->response();
        /* le hash de l'email */
        $emailHash = $this->uri->segment(3);
        /* le log d'activation */
        $logID = $this->uri->segment(4);
        if ($this->oauth_web->activate($emailHash, $logID)) {
            redirect();
            // $response["status"] = true;
        }
        // $this->output->set_output(json_encode($response));
    }
    /**
     * Pour renvoyer l'email d'activation
     * @return [type] [description]
     */
    public function reActivate()
    {
        $response = $this->response();
        $config = [
            ['field' => 'userid', 'label' => '', 'rules' => 'trim|required|integer'],
            ['field' => 'activationemail', 'label' => '', 'rules' => 'trim|required|min_length[4]|max_length[100]|valid_email']
        ];
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() === false) {
            $response["errors"] = $this->form_validation->error_array();
            $this->output->set_output(json_encode($response));
            return false;
        }
        $userID = $this->input->post('userid');
        $email = $this->input->post('activationemail');
        if ($this->oauth_web->reActivate($userID, $email) === true) {
            $response["status"] = true;
            $response["msg"] = $this->lang->line("reActivate_ok");
        }
        $this->output->set_output(json_encode($response));
    }
    /**
     * validation du formulaire de mot de passe oublié
     * cela envoie un email de changement de mot de passe à l'utilisateur
     */
    public function prepareChangePassword()
    {
        $response = $this->response();
        $config = array(
            array('field' => 'email', 'label' => '', 'rules' => 'trim|required|min_length[4]|max_length[100]|valid_email')
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() === false) {
            $response["errors"] = $this->form_validation->error_array();
            $this->output->set_output(json_encode($response));
            return false;
        }
        $email = $this->input->post('email');
        // envoi d'un email contenant un lien pour changer de mot de passe
        if ($this->oauth_web->prepareChangePassword($email)) {
            $response["status"] = true;
            $response["msg"] = $this->lang->line("prepare_change_password_ok");
        }
        $this->output->set_output(json_encode($response));
    }
    /**
     * Validation du changement de mot de passe
     * @return {[type] [description]
     */
    public function changePasswordValidation()
    {
        $response = $this->response();
        // on valide de nouveau le token au cas où
        if (! ($result = $this->oauth_web->validatePasswordLink())) {
            $response["errors"] = "Oauth Error : Bad Token";
            $this->output->set_output(json_encode($response));
            return false;
        }
        $config = [
            ['field' => 'newpassword', 'label' => '', 'rules' => ['trim', 'required', 'min_length[8]', 'max_length[24]']],
            ['field' => 'newpassword_re', 'label' => '', 'rules' => ['trim', 'required', 'min_length[8]', 'max_length[24]', 'matches[newpassword]']],
            ['field' => 'logid', 'label' => '', 'rules' => 'trim|required|integer']
        ];
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() === false) {
            $response["errors"] = $this->form_validation->error_array();
            $this->output->set_output(json_encode($response));
            return false;
        }
        $newPassword = $this->input->post('newpassword');
        $logID = $this->input->post('logid');
        if ($this->oauth_web->changePassword($logID, $newPassword) === true) {
            $response["status"] = true;
            $response["msg"] = $this->lang->line("password_changed");
        }
        $this->output->set_output(json_encode($response));
    }
    /**
     * Permet d'avoir les caractères accentué en plus
     *
     * @param  type $str
     * @return type
     */
    public function _alpha_dash_extra($str)
    {
        $this->form_validation->set_message('_alpha_dash_extra', $this->lang->line('form_validation_alpha_dash'));
        if (empty($str)) {
            return true;
        }
        // pL : toutes les lettres même accentuées
        // pN : tous les nombre
        return preg_match("/^[\pL\pN_ -']+$/u", $str) ? true : false;
    }
    /**
     * Pour checker si l'email n'est pas déjà en base, utilisé par form_valdiation
     *
     * @param  type $email
     * @return boolean TRUE si l'utilisateur n'existe pas
     */
    public function checkSignUpEmail($username)
    {
        $dbOauth = $this->load->database("oauth", true);
        $results = $dbOauth
                            ->from("oauth_users")
                            ->where("username", $username)
                            ->get()
                            ->result();
        if (!empty($results)) {  // si l'utilisateur existe déjà
            $this->form_validation->set_message('checkSignUpEmail', $this->lang->line('user_already_exists'));
            return false;
        }
        return true;
    }
    /**
       * Initialise le tableau de retour des formulaires afin qu'il soit interprétable aussi bien en php
       * qu'en ajax
       *
       * @return string
       */
    private function response()
    {
        return $data = [
            "status" => false,
            "errors" => [],
            "formid" => (!empty($this->input->post("form_id"))) ? $this->input->post("form_id") : "",
            "callback" => (!empty($this->input->post("callback"))) ? $this->input->post("callback") : "",
            "redirect" => false,
            "msg" => ""
        ];
    }
    /**
     * Pour tester le Oauth2 server créé avec Oauth
     * @return [type] [description]
     */
    public function testing()
    {
        $this->load->view("oauth", $this->oauthConfig);
    }
}
