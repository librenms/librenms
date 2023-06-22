<?php

namespace LibreNMS\Authentication;

use App\Models\User;
use Dapphp\Radius\Radius;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\LegacyAuthLevel;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Util\Debug;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RadiusAuthorizer extends MysqlAuthorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = true;
    protected static $CAN_UPDATE_PASSWORDS = false;

    protected Radius $radius;

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
            $user = User::thisAuth()->firstOrNew(['username' => $credentials['username']], [
                'auth_type' => LegacyAuth::getType(),
                'can_modify_passwd' => 0,
            ]);
            $user->save();

            $roles = $this->getDefaultRoles();

            // assign a single role from the Filter-ID attribute
            $filter_id_attribute = $this->radius->getAttribute(11);
            if ($filter_id_attribute && Str::startsWith($filter_id_attribute, 'librenms_role_')) {
                $roles = [substr($filter_id_attribute, 14)];
            }

            $user->assign($roles);
            Bouncer::sync($user)->roles($roles);
            Bouncer::refresh($user);

            return true;
        }

        throw new AuthenticationException();
    }

    private function getDefaultRoles(): array
    {
        // return roles or translate from the old radius.default_level
        return Config::get('radius.default_roles')
            ?: Arr::wrap(LegacyAuthLevel::from(Config::get('radius.default_level') ?? 1)->getName());
    }
}
