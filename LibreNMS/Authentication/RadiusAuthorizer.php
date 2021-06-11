<?php

namespace LibreNMS\Authentication;

use Dapphp\Radius\Radius;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Util\Debug;

class RadiusAuthorizer extends MysqlAuthorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = true;
    protected static $CAN_UPDATE_PASSWORDS = false;

    /** @var Radius */
    protected $radius;

    public function __construct()
    {
        $this->radius = new Radius(Config::get('radius.hostname'), Config::get('radius.secret'), Config::get('radius.suffix'), Config::get('radius.timeout'), Config::get('radius.port'));
    }

    public function authenticate($credentials)
    {
        if (empty($credentials['username'])) {
            throw new AuthenticationException('Username is required');
        }

        if (Debug::isEnabled()) {
            $this->radius->setDebug(true);
        }

        $password = $credentials['password'] ?? null;
        if ($this->radius->accessRequest($credentials['username'], $password) === true) {
            $this->addUser($credentials['username'], $password, Config::get('radius.default_level', 1));

            return true;
        }

        throw new AuthenticationException();
    }
}
