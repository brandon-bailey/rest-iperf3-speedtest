<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class OauthLib_Model extends CI_Model
{
    /** oauth config file variables **/
    private $oauthConfig;

    public function __construct()
    {
        parent::__construct();
        $this->load->config("oauth");
        $this->oauthConfig = $this->config->item("oauth");
        $this->load->model('OauthWeb_model', 'oauth_web');
        $this->load->model('OauthServer_model', 'oauth_server');
    }

    /**
     * Call one of your function defined in the config file
     * @param  [type]  $type [description]
     * @return {[type]       [description]
     */
    public function callCustomFunction($type, $params = [])
    {
        $result = false;
        $file = $this->oauthConfig["callback"][$type]["lib"];
        $method = $this->oauthConfig["callback"][$type]["method"];

        // call your pre-login function if defined
        if (!empty($file) && !empty($method)) {
            $this->load->library(strval($file));
            if (empty($params)) {
                $result = $this->{$file}->{$method}();
            } else {
                $result = call_user_func_array(array(new $file(), $method), $params);
            }
        }
        return $result;
    }
}
