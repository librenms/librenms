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

class RadiusAuthorizer extends MysqlAuthorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = true;
    protected static $CAN_UPDATE_PASSWORDS = false;

    protected Radius $radius;

    private array $roles = []; // temp cache of roles

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
            $user = User::thisAuth()->firstOrNew(['username' => $credentials['username']], [
                'auth_type' => LegacyAuth::getType(),
                'can_modify_passwd' => 0,
            ]);
            $new_user = ! $user->exists;
            $user->save();

            // cache a single role from the Filter-ID attribute now because attributes are cleared every accessRequest
            $filter_id_attribute = $this->radius->getAttribute(11);
            if ($filter_id_attribute && Str::startsWith($filter_id_attribute, 'librenms_role_')) {
                $this->roles[$credentials['username']] = [substr($filter_id_attribute, 14)];
            }

            if (Config::get('radius.enforce_roles') || $new_user) {
                $user->setRoles($this->roles[$credentials['username']] ?? $this->getDefaultRoles(), true);
            }

            return true;
        }

        throw new AuthenticationException();
    }

    public function getRoles(string $username): array|false
    {
        return $this->roles[$username] ?? false;
    }

    private function getDefaultRoles(): array
    {
        // return roles or translate from the old radius.default_level
        return Config::get('radius.default_roles')
            ?: Arr::wrap(LegacyAuthLevel::from(Config::get('radius.default_level') ?? 1)->getName());
    }
}
