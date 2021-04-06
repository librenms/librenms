<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class HttpAuthAuthorizer extends MysqlAuthorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = true;
    protected static $CAN_UPDATE_PASSWORDS = false;
    protected static $AUTH_IS_EXTERNAL = true;

    public function authenticate($credentials)
    {
        if (isset($credentials['username']) && $this->userExists($credentials['username'])) {
            return true;
        }

        throw new AuthenticationException('No matching user found and http_auth_guest is not set');
    }

    public function userExists($username, $throw_exception = false)
    {
        if (parent::userExists($username)) {
            return true;
        }

        if (Config::has('http_auth_guest') && parent::userExists(Config::get('http_auth_guest'))) {
            return true;
        }

        return false;
    }

    public function getUserlevel($username)
    {
        $user_level = parent::getUserlevel($username);

        if ($user_level) {
            return $user_level;
        }

        if (Config::has('http_auth_guest')) {
            return parent::getUserlevel(Config::get('http_auth_guest'));
        }

        return 0;
    }

    public function getUserid($username)
    {
        $user_id = parent::getUserid($username);

        if ($user_id) {
            return $user_id;
        }

        if (Config::has('http_auth_guest')) {
            return parent::getUserid(Config::get('http_auth_guest'));
        }

        return -1;
    }
}
