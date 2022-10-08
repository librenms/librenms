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
            if ($this->userExists($credentials['username'])) {

                //attribute 11 is "Filter-Id"
                //Always set password change to 0 - password resides in AAA, not LibreNMS
                //If attribute 11 is sent in reply after accept - update user
                //If user exists - update, not add.
                //If new user - add user with attribute value if present, or use default from config.
                if ($this->radius->getAttribute(11)) {
                    $this->updateUser($this->getUserid($credentials['username']), $credentials['username'], intval($this->radius->getAttribute(11)), 0, '');
                } else {
                    $this->updateUser($this->getUserid($credentials['username']), $credentials['username'], Config::get('radius.default_level', 1), 0, '');
                }
            }

            if (! $this->userExists($credentials['username'])) {
                if ($this->radius->getAttribute(11)) {
                    $this->addUser($credentials['username'], $password, intval($this->radius->getAttribute(11)), '', $credentials['username'], 0, '');
                } else {
                    $this->addUser($credentials['username'], $password, Config::get('radius.default_level', 1), '', $credentials['username'], 0, '');
                }
            }

            return true;
        }

        throw new AuthenticationException();
    }
}
