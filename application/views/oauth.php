<html lang="en"><head>
        <title>Oauth</title>
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale = 1.0, user-scalable = no">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
        <link rel="icon" href="./favicon.ico" type="image/x-icon">
        <link href="<?php echo $this->config->item('plugin_directory'); ?>bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body cz-shortcut-listen="true">
        <div class="header" id="header-background">
            <div class="container">
                <header>
                    <div id="header-anchors">
                        <a href="#client">Client</a>
                        <a href="#server">Server</a>
                    </div>
                </header>
                <h1 id="header-title">Oauth</h1>
                <div class="typewriter">
                    <h2>An oauth2 Server & Client And a web & API Authentication for CodeIgniter III</h2>
                </div>
                <h3 id="header-p">Create your own oauth2 server or add button as "connect with facebook" on your website</h3>

            </div>
        </div>
        <div id="client">
            <div class="container">
                <h2>Oauth Client</h2>
                <h4>
                    Test your oauth2 client with some of these providers
                </h4>
                <ul class="list-inline">
                    <li>
                        <a href="<?php echo base_url("/oauth/client/login/Facebook"); ?>">
                            <button type="button" class="btn btn-block btn-facebook">Facebook</button>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url("/oauth/client/login/Twitter"); ?>">
                            <button type="button" class="btn btn-block btn-twitter">Twitter</button>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url("/oauth/client/login/Google"); ?>">
                            <button type="button" class="btn btn-block btn-google">Google</button>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url("/oauth/client/login/Oauth"); ?>">
                            <button type="button" class="btn btn-block btn-oauthosor">Oauth</button>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="section-container" id="web">
            <div class="container txt-centre">
                <h2>Oauth Web</h2>
                <h4>
                    Test your web authentification
                </h4>
                <form class="panel panel-default" id="form_login" name="basic" action='<?php echo site_url("oauth/web/login"); ?>' method="POST">
                    <div class="form-group message-container"></div>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="grant_type" value="password" />
                    <input type="hidden" name="client_id" value="MY_PRIVATE_APP_DEV" />
                    <input type="hidden" name="client_secret" value="password" />
                    <input type="hidden" name="scope" value="private" />
                    <input type="email" class="form-control input-lg" id="username" name="username" value="test@rmrcom.com">
                    <input type="password" class="form-control input-lg" id="password" name="password" value="password">
                    <button type="submit" class="btn btn-success">Login</button>
                </form>

            </div>
        </div>
        <div class="section-container" id="server">
            <div class="container txt-centre">
                <h2>Oauth Server</h2>
                <h4>
                    Test your Oauth2 server
                </h4>
                <input type="hidden"class="token" id="access_token" disabled>
                <input type="hidden" id="refresh_token" disabled>
                <input type="hidden" id="redirect_uri" value="<?php echo $providers['Oauth']['keys']['redirect_uri']; ?>" disabled>

                <h2>Documentation</h2>
                <table class="table">

                    <tr>
                        <td>
                            Access Token
                        </td>
                        <td>
                            The Access Token, commonly referred to as access_token in code samples, is a credential that can be used by a client to access an API. The access_token should be used as a Bearer credential and transmitted in an HTTP Authorization header to the API. Auth0 uses access tokens to protect access to the Auth0 Management API.
                        </td>
                        <td>
                            <a href="https://auth0.com/docs/tokens/access-token">Here</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Refresh Token
                        </td>
                        <td>
                            A Refresh Token is a special kind of token that is used to authenticate a user without them needing to re-authenticate. This is primarily useful for mobile applications that are installed on a device.
                            Usually, a user will need a new access token only after the previous one expires, or when gaining access to a new resource for the first time.

                        </td>
                        <td>
                            <a href="https://auth0.com/docs/tokens/refresh-token">Here</a>
                        </td>
                    </tr>
                </table>

                <div class="col-lg-6 panel panel-default">
                    <h3>Client Credential</h3>
                    <table class="table">
                        <tr>
                            <td>Description</td>
                            <td>
                                <p>
                                    The Client Credentials grant type is used when the client is requesting access to protected resources under its control (i.e. there is no third party).
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>Documentation</td>
                            <td><a target="_blank" href="https://bshaffer.github.io/oauth2-server-php-docs/grant-types/client-credentials/">Read here</a></td>
                        </tr>
                        <tr>
                            <td>Input</td>
                            <td><textarea class="code" id="cc_input" rows="10" cols="50" disabled></textarea></td>
                        </tr>
                        <tr>
                            <td>Output</td>
                            <td><textarea class="code" id="cc_output" rows="10" cols="50" disabled></textarea></td>
                        </tr>
                        <tr>
                            <td>Action</td>
                            <td>
                                <a id="btn-ClientCredentials" class="btn btn-success">Get Access Token</a>
                                <a id="btn-public-resource" class="btn btn-success" disabled>Get Public Resource</a>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-lg-6 panel panel-default">
                    <h3>User Credentials</h3>
                    <table class="table">
                        <tr>
                            <td>Description</td>
                            <td>
                                <p>
                                    The User Credentials grant type (a.k.a. Resource Owner Password Credentials) is used when the user has a trusted relationship with the client, and so can supply credentials directly.
                                </p>
                                <p>
                                    The Refresh Token grant type is used to obtain additional access tokens in order to prolong the client’s authorization of a user’s resources.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>Documentation</td>
                            <td>
                                <a target="_blank" href="https://bshaffer.github.io/oauth2-server-php-docs/grant-types/user-credentials/">User Credentials</a> |
                                <a target="_blank" href="https://bshaffer.github.io/oauth2-server-php-docs/grant-types/refresh-token/">Refresh Token</a>
                            </td>
                        </tr>
                        <tr>
                            <td>Input</td>
                            <td><textarea class="code" id="pc_input" rows="10" cols="50" disabled></textarea></td>
                        </tr>
                        <tr>
                            <td>Output</td>
                            <td><textarea class="code" id="pc_output" rows="10" cols="50" disabled></textarea></td>
                        </tr>
                        <tr>
                            <td>Action</td>
                            <td>
                                <a id="btn-PasswordCredentials" class="btn btn-success">Get Access Token</a>
                                <a id="btn-private-resource" class="btn btn-success" disabled>Get Private Resource</a>
                                <a id="btn-refresh" class="btn btn-success" disabled>Refresh Token</a>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-lg-12 panel panel-default">
                    <h3>Authorization</h3>
                    <table class="table">
                        <tr>
                            <td>Description</td>
                            <td>The Authorization Code grant type is used when the client wants to request access to protected resources on behalf of another user (i.e. a 3rd party). This is the grant type most often associated with OAuth.</td>
                        </tr>
                        <tr>
                            <td>Documentation</td>
                            <td><a target="_blank" href="https://bshaffer.github.io/oauth2-server-php-docs/grant-types/authorization-code/">Read Here</a></td>
                        </tr>

                        <tr>
                            <td>
                                Authorization Code
                            </td>
                            <td>
                                <a href="<?php echo base_url("/oauth/client/login/oauth"); ?>">
                                    <button type="button" class="btn btn-block btn-oauthosor">Try</button>
                                </a>
                            </td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>

        <div class="section-container secondary-background-colour txt-centre white-text-colour" id="download">
            <div class="container">
            </div>
        </div>
</body>
</html>

<script src="<?php echo $this->config->item('plugin_directory'); ?>jquery/dist/jquery.min.js"></script>
<script>

var formAjax = function(event) {
    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');

    event.preventDefault();
    $.ajax({
        type: $(this).attr('method'),
        url: $(this).attr('action'),
        dataType: 'json',
        data: $(this).serialize()
    }).done(function(data) {
        console.log(data);

        /* en cas d'erreur sur le formulaire*/
        if (data.status == false) {
            $form.find('.message-container').html('<span class="msg-error">' + data.errors["username"] + '</span>');
        }

        if (data.status == true) {
            var bsalert = '';
            bsalert += '<div class="alert alert-success animation animating flipInX">';
            bsalert += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
            bsalert += '<p class="nm">' + data.msg + '</p>';
            bsalert += '</div>';

            $form.find('.message-container').html(bsalert);
        }

    }).fail(function(data) {

        var bsalert = '',
            message;

        /* construct message base on status code */
        switch (data.status) {
            case 404:
                message = 'The requested file is not found!';
                break;
            case 500:
                message = 'Internal server / script error!';
                break;
        }
        /* construct bootstrap alert with some css animation */
        bsalert += '<div class="alert alert-danger animation animating flipInX">';
        bsalert += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
        bsalert += '<h4 class="semibold mb5">' + data.status + ' error!</h4>';
        bsalert += '<p class="nm">' + message + '</p>';
        bsalert += '</div>';

        $form.find('.message-container').html(bsalert);
    });
};

$(document).ready(function(){

    $('form[name="basic"]').on('submit', formAjax);

    var dataCC = { client_id: 'MY_PUBLIC_APP_DEV', client_secret:'password', grant_type:'client_credentials', scope:'public'};
    $("#cc_input").html(JSON.stringify(dataCC,undefined, 4));

    var dataPC = {grant_type: "password", username: "test@rmrcom.com", password: "password", client_id: 'MY_PRIVATE_APP_DEV', client_secret:'password', scope:'private' };
    $("#pc_input").html(JSON.stringify(dataPC,undefined, 4 ));

    var dataRefresh = "";
    var dataRessource = "";

    function refresh() {
        dataRefresh = { refresh_token: $("#refresh_token").val(), client_id: 'MY_PRIVATE_APP_DEV', client_secret:'password', grant_type:'refresh_token', scope:'private'};
        $("#refresh_input").html(JSON.stringify(dataRefresh,undefined, 4));

        dataRessource = { access_token: $("#access_token").val(), id:1 };
        $("#resource_input").html(JSON.stringify(dataRessource,undefined, 4));
    }


    $("#btn-ClientCredentials").click(function (e){
        $.post('/oauth/server/clientCredentials', JSON.stringify(dataCC), function (d){
            $("#access_token").val(d.access_token);
            $('#cc_output').html(JSON.stringify(d,undefined, 4 ));
            $("#btn-public-resource").attr("disabled", false);
            refresh();
        }, "json");
    });

    $('#btn-PasswordCredentials').click(function (e){
        $.post('/oauth/server/userCredentials', JSON.stringify(dataPC), function (d){
            $("#refresh_token").val(d.refresh_token);
            $("#access_token").val(d.access_token);
            $("#btn-private-resource, #btn-refresh").attr("disabled", false);
            $('#pc_output').html(JSON.stringify(d,undefined, 4 ));
            refresh();
        },"json");
    });

    $("#btn-refresh").click(function (e){
        $.post('/oauth/server/refreshToken', JSON.stringify(dataRefresh), function (d){
            $("#refresh_token").val(d.refresh_token);
            $('#pc_output').html(JSON.stringify(d,undefined, 4 ));
            refresh();

        });
    });

    $('#btn-public-resource').click(function (e){
        $.get('/api/public/Test', dataRessource, function (d){
            $('#cc_output').html(JSON.stringify(d,undefined, 4 ));
            refresh();
        },"json");
    });

    $('#btn-private-resource').click(function (e){
        $.get('/api/private/Test', dataRessource, function (d){
            $('#pc_output').html(JSON.stringify(d,undefined, 4 ));
            refresh();
        },"json");
    });

});
</script>
<style>


.error {
    border:1px solid red;
}
.msg-error {
    color:red;
}
.white {
    color:white !important;
}
textarea {
    border: none !important;
    background-color: transparent;
    resize: none;
    outline: none;
}

.panel-default{
    padding:15px;
    background-color:#F8F8F8;
}

.panel-black{
    padding:15px;
    color:white;
    background-color:#333;
}



.btn-facebook {
    color: #ffffff;
    background-color: #3b5998;
    border-color: #37538d;
}
.btn-google {
    color: #ffffff;
    background-color: #d34836;
    border-color: #d34836;
}
.btn-twitter {
    color: #ffffff;
    background-color: #55acee;
    border-color: #47a5ed;
}
.btn-oauthosor {
    color: #ffffff;
    background-color: #5FB335;
    border-color: #5FB335;
}
.primary-background-colour {
    background-color: #3A1359
}

.secondary-background-colour {
    background-color: #1187ED
}

.white-text-colour {
    color: #FFF
}

.blue-text-colour {
    color: #1187ED
}

.purple-text-colour {
    color: #470E75
}

#header-logo {
    display: block;
    float: left;
    margin: 10px 0 0;
    height: 28px;
    width: 90px
}

#header-background {
    background-image: linear-gradient(20deg, rgba(211, 126, 0, 0.97) 0%, orange 60%, #c24d00 92%, #572b00 100%);
}

#video-background {
    background-image: url(../images/video-background.png)
}

html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    font: inherit;
    vertical-align: top
}

article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
    display: block
}

body {
    line-height: 1
}

ol, ul {
    list-style: none
}

blockquote, q {
    quotes: none
}

blockquote:before, blockquote:after, q:before, q:after {
    content: '';
    content: none
}

table {
    border-collapse: collapse;
    border-spacing: 0
}

body {
    background-color: #FFF;
    color: #3D3D3D;
    font-family: 'Open Sans', sans-serif;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    height: 100%;
    width: 100%
}

* {
    -webkit-overflow-scrolling: touch
}

html {
    height: 100%
}

.container {
    box-sizing: border-box;
    display: block;
    margin: 0 auto;
    max-width: 1170px;
    position: relative;
    width: 100%
}

::-webkit-scrollbar {
    background: #EDEDED;
    border-radius: 0;
    box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.2);
    width: 10px
}

::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.24);
    border-radius: 0
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.26);
    border-radius: 0
}

::-webkit-scrollbar-thumb:active {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 0
}

.section-container {
    border-bottom: 1px solid #CCC;
    display: block;
    height: auto;
    margin: 0;
    padding: 80px 0;
    width: 100%
}

h1 {
    font-size: 36px;
    font-weight: 500;
    margin-bottom: 20px
}

h2 {
    font-size: 28px;
    font-weight: 500;
    margin-bottom: 28px
}

h3 {
    font-size: 22px;
    font-weight: 500;
    margin-bottom: 28px
}

h4 {
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 28px
}

h5 {
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 12px
}

p {
    font-size: 14px;
    line-height: 22px
}

.limited-h {
    line-height: 36px;
    margin: 0 auto 28px;
    width: 700px
}

a {
    color: inherit;
    text-decoration: underline
}

.overlay {
    color: inherit;
    text-decoration: none
}

.overlay:hover {
    color: inherit
}

.btn {
    border: none;
    border-radius: 2px;
    color: #FFF;
    display: inline-block;
    font-size: 15px;
    height: 52px;
    line-height: 52px;
    overflow: hidden;
    outline: none;
    padding: 0 28px;
    position: relative;
    text-decoration: none;
    width: auto
}

.btn:hover .btn-mask {
    background: rgba(0, 0, 0, 0.12);
    cursor: pointer;
    height: 100%
}

.btn-100 {
    width: 100%
}

.btn-mask {
    background: rgba(0, 0, 0, 0.12);
    bottom: 0;
    height: .1px;
    left: 0;
    position: absolute;
    right: 0;
    width: auto
}

.btn2 {
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 2px;
    color: #FFF;
    display: inline-block;
    font-size: 15px;
    height: 50px;
    line-height: 50px;
    padding: 0 22px;
    text-align: center;
    text-decoration: none;
    width: auto
}

.btn2:hover {
    border: 1px solid #1187ED;
    background-color: #1187ED
}

input, textarea, select, button {
    outline: 0;
    background-color: transparent;
    border: 0;
    border-radius: 0;
    margin: 0;
    padding: 0;
    font-family: 'Open Sans', sans-serif;
    font: 13px 'Open Sans', sans-serif;
    -moz-appearance: none
}

input {
    background-color: #FFF;
    border: 1px solid #D6D6D6;
    box-sizing: border-box;
    display: block;
    margin-bottom: 20px;
    padding: 16px 18px;
    width: 100%
}

::-webkit-input-placeholder {
    color: rgba(0, 0, 0, 0.55)
}

:-moz-placeholder {
    color: rgba(0, 0, 0, 0.55)
}

::-moz-placeholder {
    color: rgba(0, 0, 0, 0.55)
}

:-ms-input-placeholder {
    color: rgba(0, 0, 0, 0.55)
}

.left {
    float: left
}

.right {
    float: right
}

.clear {
    clear: both
}

.inline {
    display: inline-block
}

.block {
    display: block
}

.no-margin {
    margin: 0
}

.centre-margin {
    display: block;
    margin-left: auto;
    margin-right: auto
}

.txt-centre {
    text-align: center
}

.fade-in {
    opacity: 0;
    opacity: 1 \9;
    -webkit-animation: fadeIn ease-in 1;
    -moz-animation: fadeIn ease-in 1;
    animation: fadeIn ease-in 1;
    -webkit-animation-fill-mode: forwards;
    -moz-animation-fill-mode: forwards;
    animation-fill-mode: forwards;
    -webkit-animation-duration: 1s;
    -moz-animation-duration: 1s;
    animation-duration: 1s
}

.fade-in.one {
    -webkit-animation-delay: .5s;
    -moz-animation-delay: .5s;
    animation-delay: .5s
}

.fade-in.two {
    -webkit-animation-delay: 1s;
    -moz-animation-delay: 1s;
    animation-delay: 1s
}

.fade-in.three {
    -webkit-animation-delay: 1.5s;
    -moz-animation-delay: 1.5s;
    animation-delay: 1.5s
}

.fade-in.four {
    -webkit-animation-delay: 2s;
    -moz-animation-delay: 2s;
    animation-delay: 2s
}

.smooth {
    -webkit-transition: .2s;
    -moz-transition: .2s;
    -ms-transition: .2s;
    -o-transition: .2s;
    transition: .2s
}

.header {
    display: block;
    height: 400px;
    overflow: hidden;
    padding: 0;
    width: 100%
}

header {
    box-sizing: border-box;
    display: block;
    padding: 70px 0 40px;
    width: 100%
}

#header-anchors {
    display: inline-block;
    float: right
}

.popup-nav-btn {
    display: none
}

header a {
    color: #FFF;
    font-size: 14px;
    display: inline-block;
    margin: 0 0 0 35px;
    padding: 18px 0;
    text-decoration: none;
    text-align: center
}

#get-app-a {
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 2px;
    padding: 18px
}

#get-app-a:hover {
    background-color: #1187ED;
    border: 1px solid #1187ED
}

#header-title {
    color: #FFF;
    display: block;
    font-size: 70px;
    font-weight: 700
}

.typewriter {
    display: inline-block;
    margin: 5px 0 0;
    width: auto
}

.typewriter h2 {
    animation: typing 3.5s steps(40, end), blink-caret .75s step-end infinite;
    border-right: 3px solid #FFF;
    color: #FFF;
    font-size: 28px;
    line-height: 34px;
    margin: 0;

    padding-right: 5px;
    overflow: hidden;
    text-transform: none;
    white-space: nowrap
}

@keyframes typing {
    from {
        width: 0
    }
    to {
        width: 100%
    }
}

@keyframes blink-caret {
    from, to {
        border-color: transparent
    }
    50% {
        border-color: #FFF
    }
}

#header-p {
    color: rgba(255, 255, 255, 0.86);
    display: block;
    margin: 60px 0 25px;
    width: 500px
}

#header-about-btn {
    color: rgba(255, 255, 255, 0.86);
    display: inline-block;
    font-size: 15px;
    height: 50px;
    line-height: 50px;
    padding: 0 0 0 30px;
    text-decoration: none;
    width: auto
}

#header-subscribe-container {
    background-color: #F6F8Fa;
    border-radius: 3px;
    box-shadow: 0 15px 22px 0 rgba(0, 0, 0, 0.16), 0 19px 48px 0 rgba(0, 0, 0, 0.16);
    height: auto;
    position: absolute;
    margin: 0 auto;
    padding: 25px;
    right: 0;
    text-align: center;
    top: 210px;
    width: 300px
}

#header-subscribe-desc {
    display: block;
    font-size: 12px;
    margin: 14px 0 0
}

#client {
    box-sizing: border-box;
    background-color: #F8F8F8;
    display: block;
    font-size: 0;
    height: auto;
    padding: 80px 0;
    text-align: center;
    width: 100%
}

.overview-strip-feature {
    display: inline-block;
    font-size: 14px;
    height: auto;
    text-align: center;
    width: 350px
}

#overview-strip-feature-centre {
    margin: 0 40px
}

.overview-strip-feature img {
    display: block;
    height: 80px;
    margin: 0 auto 36px;
    width: 80px
}

.overview-strip-feature h3 {
    display: block;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 22px
}

#overview-mockup1 {
    display: block;
    height: auto;
    margin: 60px auto;
    width: 90%
}

#overview-split-container {
    display: block;
    font-size: 0
}

#overview-left-split {
    box-sizing: border-box;
    display: inline-block;
    font-size: 14px;
    height: auto;
    padding: 0 40px 0 0;
    width: 50%
}

#overview-left-split p {
    margin: 0 0 30px
}

#overview-right-split {
    box-sizing: border-box;
    display: inline-block;
    font-size: 14px;
    height: auto;
    padding: 0 0 0 40px;
    width: 50%
}

#overview-mockup2 {
    display: block;
    height: auto;
    margin: 0 auto;
    width: 100%
}

#features-container {
    display: block;
    font-size: 0;
    margin: 50px 0 0;
    text-align: center
}

.feature {
    box-sizing: border-box;
    display: inline-block;
    font-size: 14px;
    padding: 0 20px 40px;
    text-align: left;
    width: 33%
}

.feature-number {
    display: inline-block;
    float: left;
    font-size: 30px;
    font-weight: 700;
    margin: 0 16px 10px 0
}

.feature-h {
    display: inline-block;
    font-size: 16px;
    font-weight: 700;
    line-height: 20px;
    margin: 0 0 10px
}

#features-a {
    margin: 40px 0 0
}

.video-container {
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover;
    position: relative
}

#video-filter {
    background: rgba(0, 0, 0, 0.65);
    bottom: 0;
    left: 0;
    position: absolute;
    top: 0;
    right: 0
}

#video-icon {
    background: url(../images/video-icon.png) center center / 70px 70px no-repeat;
    display: block;
    height: 300px;
    margin: 40px 0 0;
    width: 100%
}

#video-icon:hover {
    cursor: pointer
}

.popup-con iframe {
    border: 3px solid #FFF;
    box-sizing: border-box;
    display: block;
    height: 450px;
    width: 800px
}

#contact-form {
    margin: 40px 0 0
}

.download-btn {
    background-position: 22px center;
    background-repeat: no-repeat;
    background-size: 20px 20px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 2px;
    color: #FFF;
    display: inline-block;
    font-size: 15px;
    height: 50px;
    line-height: 50px;
    margin: 40px 30px 0;
    padding: 0 22px 0 55px;
    text-align: center;
    text-decoration: none;
    width: auto
}

.download-btn:hover {
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.5)
}

#apple-download {
    background-image: url(../images/apple-logo.png)
}

#play-download {
    background-image: url(../images/play-logo.png)
}

#windows-download {
    background-image: url(../images/windows-logo.png)
}

#testimonial-container {
    background: rgba(255, 255, 255, 0.13) url(../images/dots-pattern.png) repeat;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.015);
    box-sizing: border-box;
    display: block;
    margin: 50px auto 0;
    padding: 50px;
    width: 800px
}

.testimonial {
    display: none
}

.testimonial img {
    border: 10px solid rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    display: inline-block;
    float: left;
    height: 120px;
    margin: 0 40px 0 0;
    width: 120px
}

.testimonial-name {
    display: block;
    font-size: 26px;
    font-weight: 700;
    margin: 0 0 20px
}

.testimonial-review {
    display: block;
    font-size: 15px;
    font-weight: 300;
    line-height: 24px
}

.popup {
    background: rgba(0, 0, 0, 0.9);
    bottom: 0;
    display: none;
    height: auto;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    z-index: 9999999999
}

.popup-con {
    font-size: 14px;
    height: auto;
    left: 50%;
    max-height: 90%;
    max-width: 800px;
    overflow: auto;
    position: absolute;
    top: 50%;
    transform: translateX(-50%) translateY(-50%);
    width: calc(90% - 15px);
    -webkit-overflow-scrolling: touch
}

.popup-w-con {
    background: #FFF;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.9);
    box-sizing: border-box;
    display: block;
    height: auto;
    padding: 30px;
    text-align: left;
    width: 100%
}

.popup-con::-webkit-scrollbar {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 0;
    box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.2);
    width: 6px
}

.popup-con::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 0
}

.popup-con::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 0
}

.popup-con::-webkit-scrollbar-thumb:active {
    background: rgba(255, 255, 255, 0.63);
    border-radius: 0
}

.popup-close {
    background: #FFF;
    color: #1C1C1C;
    display: table;
    font-size: 12px;
    font-weight: 700;
    margin: 0 0 10px;
    padding: 10px 18px
}

.popup-close:hover {
    cursor: pointer
}

@media screen and (max-width:1200px) {
    .container {
        padding: 0 50px;
    }
    #header-subscribe-container {
        right: 50px;
    }
}

@media screen and (max-width:1100px) {
    .overview-strip-feature {
        width: 250px;
    }
    .overview-strip-feature img {
        height: 70px;
        width: 70px;
    }
}

@media screen and (max-width:985px) {
    .limited-h {
        width: auto;
    }
    #header-anchors {
        display: none;
    }
    .popup-nav-btn {
        border: 3px solid #FFF;
        color: #FFF;
        display: inline-block;
        float: right;
        font-size: 12px;
        font-weight: 700;
        margin: 8px 0 0 0;
        padding: 5px 12px;
    }
    .popup-nav-btn:hover {
        cursor: pointer;
    }
    .popup-nav {
        background: #FFF;
        bottom: 0;
        height: auto;
        left: 0;
        overflow-y: auto;
        position: absolute;
        right: 0;
        top: 0;
        width: auto;
        -webkit-overflow-scrolling: touch;
    }
    .popup-nav a {
        border-bottom: 1px solid #1C1C1C;
        color: #1C1C1C;
        display: block;
        font-size: 14px;
        height: auto;
        margin: 0;
        padding: 20px 0;
        text-align: center;
        text-decoration: none;
        text-transform: uppercase;
        width: 100%;
    }
    .header {
        height: auto;
    }
    .typewriter h2 {
        font-size: 22px;
    }
    #header-subscribe-container {
        margin: 100px 0 70px 0;
        position: initial;
        width: auto;
    }
    .overview-strip-feature {
        display: block;
        margin: 0 0 60px 0;
        width: 100%;
    }
    #overview-strip-feature-centre {
        margin: 0 0 60px 0;
    }
    #overview-strip-feature-bottom {
        margin: 0;
    }
    #overview-left-split {
        padding: 0;
        width: 100%;
    }
    #overview-right-split {
        display: none;
    }
    #overview-split-container {
        text-align: center;
    }
    .overview-strip-feature h3 {
        font-display: 16px;
        margin: 0 0 18px 0;
    }
    .overview-strip-feature img {
        height: 60px;
        margin: 0 auto 30px auto;
        width: 60px;
    }
    #testimonial-container {
        padding: 20px;
        width: auto;
    }
    .testimonial img {
        display: block;
        float: none;
        margin: 0 auto 30px auto;
    }
    .testimonial-name {
        font-size: 22px;
        text-align: center;
    }
    .testimonial-review {
        font-size: 15px;
        text-align: center;
    }
    .feature {
        padding: 0 25px 50px 25px;
        width: 50%;
    }
    .download-btn {
        display: block;
        margin: 30px auto 0 auto;
        width: 140px;
    }
}

@media screen and (max-width:925px) {
    .popup-con iframe {
        margin: 0 auto;
        height: 338px;
        width: 600px;
    }
}

@media screen and (max-width:750px) {
    .feature {
        display: block;
        text-align: center;
        width: 100%;
    }
    .feature-number {
        display: block;
        float: none;
        margin: 0 0 16px 0;
    }
}

@media screen and (max-width:690px) {
    .popup-con iframe {
        margin: 0 auto;
        height: 253px;
        width: 450px;
    }
}

@media screen and (max-width:650px) {
    h2 {
        font-size: 24px;
    }
    .limited-h {
        line-height: 30px;
    }
    #overview-mockup1 {
        width: 100%;
    }
    #testimonial-container {
        width: 90%;
    }
    .testimonial-name {
        font-size: 20px;
    }
    .testimonial-review {
        font-size: 14px;
    }
    .download-btn {
        display: block;
        margin: 30px auto 0 auto;
        width: 140px;
    }
}

@media screen and (max-width:690px) {
    #video-play-btn {
        height: 250px;
    }
    .popup-con iframe {
        height: 169px;
        width: 300px;
    }
}

@media screen and (max-width:550px) {
    h1 {
        font-size: 32px;
    }
    .container {
        padding: 0 20px;
    }
    .header {
        text-align: center;
    }
    header {
        padding: 26px 0 40px 0;
    }
    #header-title {
        font-size: 50px;
        text-align: center;
    }

    #header-p {
        text-align: center;
        margin: 60px 0px 32px;
        width: auto;
    }
    #header-download-btn {
        margin: 0 0 12px 0;
    }
    #header-about-btn {
        padding: 0 30px 0 30px;
    }
    .feature {
        text-align: center;
    }
    .feature-number {
        display: block;
        margin: 0 0 16px 0;
    }
    #testimonial-container {
        padding: 20px;
        width: auto;
    }
    #video-icon {
        height: 250px;
    }
}

@media screen and (max-width:360px) {
    h2 {
        font-size: 22px;
    }
    .limited-h {
        line-height: 28px;
    }
    .popup-con iframe {
        height: 141px;
        width: 250px;
    }
}


/* ----------------------------------- */


/* -------------- Dock --------------- */


/* ----------------------------------- */

.dock {
    background: #2E2E2E;
    bottom: -1px;
    box-sizing: border-box;
    display: block;
    font-family: 'Ubuntu', sans-serif;
    height: auto;
    left: 0;
    padding: 12px 22px;
    position: fixed;
    right: 0;
    vertical-align: top;
    width: 100%;
    z-index: 9999;
}

.logo-black {
    background: url('/bin/media/brand/logo_black.png') center center / contain no-repeat;
}

.logo-white {
    background: url('/bin/media/brand/logo_white.png') center center / contain no-repeat;
}

.logo-short-black {
    background: url('/bin/media/brand/logo-short_black.png') center center / contain no-repeat;
}

.logo-short-white {
    background: url('/bin/media/brand/logo-short_white.png') center center / contain no-repeat;
}

.icon {
    background: url('/bin/media/brand/icon.png') center center / contain no-repeat;
}

.left {
    float: left;
}

.right {
    float: right;
}

.clear {
    clear: both;
}

.btn-dock {
    color: #FFF;
    display: table;
    font-size: 12px;
    height: auto;
    padding: 10px 16px;
    text-decoration: none;
    text-shadow: 1px 1px rgba(0, 0, 0, 0.06);
}

#dock-view-btn {
    background: #4F4F4F;
}

#dock-view-btn:hover {
    background: #404040;
}

#dock-buy-btn {
    background: #0A92DE;
}

#dock-buy-btn:hover {
    background: #1075AD;
}

#dock-logo {
    display: inline-block;
    height: 13px;
    left: 50%;
    position: absolute;
    top: 50%;
    transform: translateX(-50%) translateY(-50%);
    width: 100px;
}


/* ----------------------------------- */


/* ----------- Responsive ------------ */

/* ----------------------------------- */
@media screen and (max-width:550px) {
    #dock-view-btn {
        display: none;
    }
    /*Hide view btn*/
    #dock-logo {
        left: 22px;
        transform: translateX(0) translateY(-50%)
    }
    /*Move logo to left*/
}

code, .code {
    color: brown !important;
    background-color: transparent !important;
}



</style>
