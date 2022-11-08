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
            // attribute 11 is "Filter-Id", apply and enforce user role (level) if set

            $filter_id_attribute = $this->radius->getAttribute(11);
            $level = match ($filter_id_attribute) {
                'librenms_role_admin' => 10,
                'librenms_role_normal' => 1,
                'librenms_role_global-read' => 5,
                default => Config::get('radius.default_level', 1)
            };

            // if Filter-Id was given and the user exists, update the level
            if ($filter_id_attribute && $this->userExists($credentials['username'])) {
                $user = \App\Models\User::find($this->getUserid($credentials['username']));
                $user->level = $level;
                $user->save();

                return true;
            }

            $this->addUser($credentials['username'], $password, $level, '', $credentials['username'], 0);

            return true;
        }

        throw new AuthenticationException();
    }
}
