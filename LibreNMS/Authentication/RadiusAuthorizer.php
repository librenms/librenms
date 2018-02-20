<?php

namespace LibreNMS\Authentication;

use Dapphp\Radius\Radius;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class RadiusAuthorizer extends MysqlAuthorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = 1;
    protected static $CAN_UPDATE_USER = 1;
    protected static $CAN_UPDATE_PASSWORDS = 0;

    /** @var Radius $radius */
    protected $radius;

    public function __construct()
    {
        $this->radius = new Radius(Config::get('radius.hostname'), Config::get('radius.secret'), Config::get('radius.suffix'), Config::get('radius.timeout'), Config::get('radius.port'));
    }

    public function authenticate($username, $password)
    {
        global $debug;

        if (empty($username)) {
            throw new AuthenticationException('Username is required');
        }

        if ($debug) {
            $this->radius->setDebug(true);
        }

        if ($this->radius->accessRequest($username, $password) === true) {
            $this->addUser($username, $password, Config::get('radius.default_level', 1));
            return true;
        }

        throw new AuthenticationException();
    }
}
