<?php

use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;

/**
 * Simple PDO storage for all storage types
 *
 * NOTE: This class is meant to get users started
 * quickly. If your application requires further
 * customization, extend this class or create your own.
 *
 * NOTE: Passwords are stored in plaintext, which is never
 * a good idea.  Be sure to override this for your application
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class SqliteOauth_Model extends CI_Model implements
    OAuth2\Storage\AuthorizationCodeInterface,
    OAuth2\Storage\AccessTokenInterface,
    OAuth2\Storage\ClientCredentialsInterface,
    OAuth2\Storage\UserCredentialsInterface,
    OAuth2\Storage\RefreshTokenInterface,
    OAuth2\Storage\JwtBearerInterface,
    OAuth2\Storage\ScopeInterface,
    OAuth2\Storage\PublicKeyInterface,
    UserClaimsInterface,
    OpenIDAuthorizationCodeInterface
{
    protected $db;
    protected $config;

    public function __construct($connection = null, $config = array())
    {
        if ($connection !== null) {
            $this->db = $connection;
        }

        $this->config = array_merge(array(
            'client_table' => 'oauth_clients',
            'access_token_table' => 'oauth_access_tokens',
            'refresh_token_table' => 'oauth_refresh_tokens',
            'code_table' => 'oauth_authorization_codes',
            'user_table' => 'oauth_users',
            'jwt_table'  => 'oauth_jwt',
            'jti_table'  => 'oauth_jti',
            'scope_table'  => 'oauth_scopes',
            'public_key_table'  => 'oauth_public_keys',
        ), $config);
    }

    public function setDatabase($connection)
    {
        $this->db = $connection;
    }


    /* OAuth2\Storage\ClientCredentialsInterface */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $query = $this->db->select('*')
            ->from($this->config['client_table'])
            ->where('client_id', $client_id)
            ->get();
        $result = $query->row_array();
        
        return $result && $result['client_secret'] == $client_secret;
    }

    public function isPublicClient($client_id)
    {
        $query = $this->db->select('*')
            ->from($this->config['client_table'])
            ->where('client_id', $client_id)
            ->get();
        if (!$result = $query->row_array()) {
            return false;
        }

        return empty($result['client_secret']);
    }

    /* OAuth2\Storage\ClientInterface */
    public function getClientDetails($client_id)
    {
        $query = $this->db->select('*')
            ->from($this->config['client_table'])
            ->where('client_id', $client_id)
            ->get();
        return $query->row_array();
    }

    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null)
    {
        // if it exists, update it.
        if ($this->getClientDetails($client_id)) {
            $data = array(
            'client_secret' =>$client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_types' => $grant_types,
            'scope' => $scope,
            'user_id' => $user_id
            );
            $this->db->where('client_id', $client_id);
            $this->db->replace($this->config['client_table'], $data);
        } else {
            $data = array(
            'client_id' => $client_id,
            'client_secret' =>$client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_types' => $grant_types,
            'scope' => $scope,
            'user_id' => $user_id
            );
            $this->db->set($data);
            $this->db->insert($this->config['client_table']);
        }
        return true;
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /* OAuth2\Storage\AccessTokenInterface */
    public function getAccessToken($access_token)
    {
        $query = $this->db->select('*')
            ->from($this->config['access_token_table'])
            ->where('access_token', $access_token)
            ->get();

        if ($token = $query->row_array()) {
            // convert date string back to timestamp
            $token['expires'] = strtotime($token['expires']);
        }

        return $token;
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAccessToken($access_token)) {
            $data = array(
            'client_id' =>$client_id,
            'expire' => $expires,
            'user_id' => $user_id,
            'scope' => $scope
            );
            $this->db->where('access_token', $access_token);
            $this->db->replace($this->config['access_token_table'], $data);
        } else {
            $data = array(
            'access_token' => $access_token,
            'client_id' => $client_id,
            'expires' =>$expires,
            'user_id' => $user_id,
            'scope' => $scope
            );
            $this->db->set($data);
            $this->db->insert($this->config['access_token_table']);
        }
        return true;
    }

    public function unsetAccessToken($access_token)
    {
        $this->db->where('access_token', $access_token);
        $this->db->delete($this->config['access_token_table']);

        return $this->db->affected_rows() > 0;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    public function getAuthorizationCode($code)
    {
        $query = $this->db->select('*')
            ->from($this->config['code_table'])
            ->where('authorization_code', $code)
            ->get();

        if ($code = $query->row_array()) {
            // convert date string back to timestamp
            $code['expires'] = strtotime($code['expires']);
        }

        return $code;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        if (func_num_args() > 6) {
            // we are calling with an id token
            return call_user_func_array(array($this, 'setAuthorizationCodeWithIdToken'), func_get_args());
        }

        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $data = array(
            'client_id' =>$client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope
            );
            $this->db->where('authorization_code', $code);
            $this->db->replace($this->config['code_table'], $data);
        } else {
            $data = array(
            'authorization_code' => $code,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' =>$expires,
            'scope' => $scope
            );
            $this->db->set($data);
            $this->db->insert($this->config['code_table']);
        }
        return true;
    }

    private function setAuthorizationCodeWithIdToken($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $data = array(
            'client_id' =>$client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
            'id_token' => $id_token
            );
            $this->db->where('authorization_code', $code);
            $this->db->replace($this->config['code_table'], $data);
        } else {
            $data = array(
            'authorization_code' => $code,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' =>$expires,
            'scope' => $scope,
            'id_token' => $id_token
            );
            $this->db->set($data);
            $this->db->insert($this->config['code_table']);
        }
        return true;
    }

    public function expireAuthorizationCode($code)
    {
        $this->db->where('authorization_code', $code);
        $this->db->delete($this->config['code_table']);
        return true;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    public function checkUserCredentials($username, $password)
    {
        if ($user = $this->getUser($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    public function getUserDetails($username)
    {
        return $this->getUser($username);
    }

    /* UserClaimsInterface */
    public function getUserClaims($user_id, $claims)
    {
        if (!$userDetails = $this->getUserDetails($user_id)) {
            return false;
        }

        $claims = explode(' ', trim($claims));
        $userClaims = array();

        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    // address is an object with subfields
                    $userClaims['address'] = $this->getUserClaim($validClaim, $userDetails['address'] ?: $userDetails);
                } else {
                    $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
                }
            }
        }

        return $userClaims;
    }

    protected function getUserClaim($claim, $userDetails)
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);

        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        }

        return $userClaims;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    public function getRefreshToken($refresh_token)
    {
        $query = $this->db->select('*')
            ->from($this->config['refresh_token_table'])
            ->where('refresh_token', $refresh_token)
            ->get();

        if ($token = $query->row_array()) {
            // convert expires to epoch time
            $token['expires'] = strtotime($token['expires']);
        }
        
        return $token;
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        $data = array(
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' =>$expires,
            'scope' => $scope
        );
        $this->db->set($data);
        $this->db->insert($this->config['refresh_token_table']);

        return true;
    }

    public function unsetRefreshToken($refresh_token)
    {
        $this->db->where('refresh_token', $refresh_token);
        $this->db->delete($this->config['refresh_token_table']);

        return $this->db->affected_rows() > 0;
    }

    // plaintext passwords are bad!  Override this for your application
    protected function checkPassword($user, $password)
    {
        return $user['password'] == $this->hashPassword($password);
    }

    // use a secure hashing algorithm when storing passwords. Override this for your application
    protected function hashPassword($password)
    {
        return sha1($password);
    }

    public function getUser($username)
    {
        $query = $this->db->select('*')
            ->from($this->config['user_table'])
            ->where('username', $username)
            ->get();

        if (!$userInfo = $query->row_array()) {
            return false;
        }
        // the default behavior is to use "username" as the user_id
        return array_merge(array(
            'user_id' => $username
        ), $userInfo);
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        // do not store in plaintext
        $password = $this->hashPassword($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $data = array(
            'password' =>$password,
            'first_name' => $firstName,
            'last_name' => $lastName
            );
            $this->db->where('username', $username);
            $this->db->replace($this->config['user_table'], $data);
        } else {
            $data = array(
            'username' => $code,
            'password' =>$password,
            'first_name' => $firstName,
            'last_name' => $lastName
            );
            $this->db->set($data);
            $this->db->insert($this->config['user_table']);
        }

        return true;
    }

    /* ScopeInterface */
    public function scopeExists($scope)
    {
        $scope = explode(' ', $scope);
        $whereIn = implode(',', array_fill(0, count($scope), '?'));
        //$stmt = $this->db->prepare(sprintf('SELECT count(scope) as count FROM %s WHERE scope IN (%s)', $this->config['scope_table'], $whereIn));
        //$stmt->execute($scope);

        //if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        //    return $result['count'] == count($scope);
        //}
        $query = $this->db->select('count(scope) as count')
            ->from($this->config['scope_table'])
            ->where_in('scope', $whereIn)
            ->get();

        if ($result = $query->row_array()) {
            return $result['count'] == count($scope);
        }

        return false;
    }

    public function getDefaultScope($client_id = null)
    {
        $query = $this->db->select('scope')
            ->from($this->config['scope_table'])
            ->where('is_default', true)
            ->get();

        if ($result = $query->result_array()) {
            $defaultScope = array_map(function ($row) {
                return $row['scope'];
            }, $result);
            return implode(' ', $defaultScope);
        }

        return null;
    }

    /* JWTBearerInterface */
    public function getClientKey($client_id, $subject)
    {
        $query = $this->db->select('public_key')
            ->from($this->config['jwt_table'])
            ->where('client_id', $client_id)
            ->where('subject', $subject)
            ->get();

        return $query->row();
    }

    public function getClientScope($client_id)
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    public function getJti($client_id, $subject, $audience, $expires, $jti)
    {
        $query = $this->db->select('*')
            ->from($this->config['jti_table'])
            ->where('issuer', $client_id)
            ->where('subject', $subject)
            ->where('audience', $audience)
            ->where('expires', $expires)
            ->where('jti', $jti)
            ->get();

        if ($result = $query->row_array()) {
            return array(
                'issuer' => $result['issuer'],
                'subject' => $result['subject'],
                'audience' => $result['audience'],
                'expires' => $result['expires'],
                'jti' => $result['jti'],
            );
        }
        return null;
    }

    public function setJti($client_id, $subject, $audience, $expires, $jti)
    {
        $data = array(
            'issuer' => $client_id,
            'subject' =>$subject,
            'audience' => $audience,
            'expires' => $expires,
            'jti' => $jti
        );
        $this->db->set($data);
        $this->db->insert($this->config['jti_table']);

        return true;
    }

    /* PublicKeyInterface */
    public function getPublicKey($client_id = null)
    {
        $query = $this->db->select('public_key')
            ->from($this->config['public_key_table'])
            ->where('client_id', $client_id)
            ->or_where('client_id IS', null)
            ->order_by('client_id IS NOT NULL', 'DESC')
            ->get();
        if ($result = $query->row_array()) {
            return $result['public_key'];
        }
    }

    public function getPrivateKey($client_id = null)
    {
        $query = $this->db->select('private_key')
            ->from($this->config['public_key_table'])
            ->where('client_id', $client_id)
            ->or_where('client_id IS', null)
            ->order_by('client_id IS NOT NULL', 'DESC')
            ->get();
        if ($result = $query->row_array()) {
            return $result['public_key'];
        }
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        $query = $this->db->select('encryption_algorithm')
            ->from($this->config['public_key_table'])
            ->where('client_id', $client_id)
            ->or_where('client_id IS', null)
            ->order_by('client_id IS NOT NULL', 'DESC')
            ->get();
        if ($result = $query->row_array()) {
            return $result['encryption_algorithm'];
        }

        return 'RS256';
    }

    /**
     * DDL to create OAuth2 database and tables for PDO storage
     *
     * @see https://github.com/dsquier/oauth2-server-php-mysql
     */
    public function getBuildSql($dbName = 'oauth2_server_php')
    {
        $sql = "
        CREATE TABLE {$this->config['client_table']} (
          client_id             VARCHAR(80)   NOT NULL,
          client_secret         VARCHAR(80),
          redirect_uri          VARCHAR(2000),
          grant_types           VARCHAR(80),
          scope                 VARCHAR(4000),
          user_id               VARCHAR(80),
          PRIMARY KEY (client_id)
        );

        CREATE TABLE {$this->config['access_token_table']} (
          access_token         VARCHAR(40)    NOT NULL,
          client_id            VARCHAR(80)    NOT NULL,
          user_id              VARCHAR(80),
          expires              TIMESTAMP      NOT NULL,
          scope                VARCHAR(4000),
          PRIMARY KEY (access_token)
        );

        CREATE TABLE {$this->config['code_table']} (
          authorization_code  VARCHAR(40)    NOT NULL,
          client_id           VARCHAR(80)    NOT NULL,
          user_id             VARCHAR(80),
          redirect_uri        VARCHAR(2000),
          expires             TIMESTAMP      NOT NULL,
          scope               VARCHAR(4000),
          id_token            VARCHAR(1000),
          PRIMARY KEY (authorization_code)
        );

        CREATE TABLE {$this->config['refresh_token_table']} (
          refresh_token       VARCHAR(40)    NOT NULL,
          client_id           VARCHAR(80)    NOT NULL,
          user_id             VARCHAR(80),
          expires             TIMESTAMP      NOT NULL,
          scope               VARCHAR(4000),
          PRIMARY KEY (refresh_token)
        );

        CREATE TABLE {$this->config['user_table']} (
          username            VARCHAR(80),
          password            VARCHAR(80),
          first_name          VARCHAR(80),
          last_name           VARCHAR(80),
          email               VARCHAR(80),
          email_verified      BOOLEAN,
          scope               VARCHAR(4000)
        );

        CREATE TABLE {$this->config['scope_table']} (
          scope               VARCHAR(80)  NOT NULL,
          is_default          BOOLEAN,
          PRIMARY KEY (scope)
        );

        CREATE TABLE {$this->config['jwt_table']} (
          client_id           VARCHAR(80)   NOT NULL,
          subject             VARCHAR(80),
          public_key          VARCHAR(2000) NOT NULL
        );

        CREATE TABLE {$this->config['jti_table']} (
          issuer              VARCHAR(80)   NOT NULL,
          subject             VARCHAR(80),
          audience            VARCHAR(80),
          expires             TIMESTAMP     NOT NULL,
          jti                 VARCHAR(2000) NOT NULL
        );

        CREATE TABLE {$this->config['public_key_table']} (
          client_id            VARCHAR(80),
          public_key           VARCHAR(2000),
          private_key          VARCHAR(2000),
          encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
        )
";

        return $sql;
    }
}
