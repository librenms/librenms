<?php

namespace LibreNMS\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use LibreNMS\Exceptions\AuthenticationException;

class MysqlAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = true;
    protected static $CAN_UPDATE_PASSWORDS = true;

    public function authenticate($credentials)
    {
        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        $user_data = User::whereNotNull('password')->firstWhere(['username' => $username]);
        $hash = $user_data->password;
        $enabled = $user_data->enabled;

        if (! $enabled) {
            throw new AuthenticationException();
        }

        if (Hash::check($password, $hash)) {
            // Check if hash algorithm is current and update it if it is not
            if (Hash::needsRehash($hash)) {
                $user_data->setPassword($password);
                $user_data->save();
            }

            return true;
        }

        throw new AuthenticationException();
    }

    public function canUpdatePasswords($username = '')
    {
        /*
         * By default allow the password to be modified, unless the existing
         * user is explicitly prohibited to do so.
         */

        if (! static::$CAN_UPDATE_PASSWORDS) {
            return false;
        } elseif (empty($username) || ! $this->userExists($username)) {
            return true;
        } else {
            return User::thisAuth()->where('username', $username)->value('can_modify_passwd');
        }
    }

    public function userExists($username, $throw_exception = false)
    {
        return User::thisAuth()->where('username', $username)->exists();
    }

    public function getUserid($username)
    {
        // for mysql user_id == auth_id
        return User::thisAuth()->where('username', $username)->value('user_id');
    }

    public function getUser($user_id)
    {
        $user = User::find($user_id);
        if ($user) {
            return $user->toArray();
        }

        return false;
    }
}
