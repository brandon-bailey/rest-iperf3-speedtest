<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
$config["oauth"] =
    [
        "web" => [
            "loadLanguage" => ["lib" => "app_lib", "method" => "loadLanguage"],
            "client_id" => "MY_PRIVATE_APP_DEV",
            "client_secret" => "password",
            "login_cookie_name" => "SITE_autoConnect",
            "oauthSalt" => "salt",
            "changePasswordPage" => "home/changePassword"
        ],
        "base_url" => "https://speedtest.rmrcom.com/oauth/client/endpoint",
        "callback" => [
            "createUser" => ["lib" => "user_lib", "method" => "createFromSocial"],
            "pre_login" => ["lib" => "", "method" => ""],
            "post_login" => ["lib" => "app_lib", "method" => "logConnexion"],
            "pre_signup" => ["lib" => "user_lib", "method" => "signUp"],
            "post_signup" => ["lib" => "oauth_web", "method" => "sendEmailSignUp"],
            "pre_activation" => ["lib" => "oauth_web", "method" => "sendEmailValidation"],
            "post_activation" => ["lib" => "user_lib", "method" => "activate"],
            "email_changePassword" => ["lib" => "app_lib", "method" => "sendEmailChangePassword"],
            "form_changePassword" => ["lib" => "app_lib", "method" => "changePassword"]
        ],
        "providers" => [
            // oauth server
            "Oauth" => [
                "enabled" => true,
                "keys"    => ["endpoint" => "https://speedtest.rmrcom.com/oauth/server/authorize",
                "id" => "MY_PUBLIC_APP_DEV",
                "secret" => "password",
                "state" => "mysalt",
                "redirect_uri" => "https://speedtest.rmrcom.com/oauth/client/authorize"
                ],
            ],
            "Google" => [
                "enabled" => false,
                "keys"    => ["id" => "", "secret" => ""],
            ],
            "Twitter" => [
                "enabled" => false,
                "keys"    => ["key" => "", "secret" => ""]
            ],
            "Facebook" => [
                "enabled" => false,
                "keys"    => ["id" => "", "secret" => ""],
            ],
            "Yahoo" => [
                "enabled" => false,
                "keys"    => ["id" => "", "secret" => ""]
            ],
            // windows live
            "Live" => [
                "enabled" => false,
                "keys"    => ["id" => "", "secret" => ""]
            ],
            "MySpace" => [
                "enabled" => false,
                "keys"    => ["key" => "", "secret" => ""]
            ],
            "LinkedIn" => [
                "enabled" => false,
                "keys"    => ["key" => "", "secret" => ""]
            ]
        ],
        // if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
        "debug_mode" => (ENVIRONMENT == 'development'),
        "debug_file" => APPPATH.'/logs/hybridauth.log'
    ];
