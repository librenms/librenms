<?php

namespace LibreNMS\Authentication;

use App\Models\Notification;
use App\Models\NotificationAttrib;
use App\Models\User;
use LibreNMS\DB\Eloquent;
use LibreNMS\Exceptions\AuthenticationException;
use Phpass\PasswordHash;

class MysqlAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = 1;
    protected static $CAN_UPDATE_USER = 1;
    protected static $CAN_UPDATE_PASSWORDS = 1;

    public function authenticate($username, $password)
    {
        $hash = User::thisAuth()->where('username', $username)->value('password');

        // check for old passwords
        if (strlen($hash) == 32) {
            // md5
            if (md5($password) === $hash) {
                $this->changePassword($username, $password);
                return true;
            }
        } elseif (starts_with($hash, '$1$')) {
            // old md5 crypt
            if (crypt($password, $hash) == $hash) {
                $this->changePassword($username, $password);
                return true;
            }
        } elseif (starts_with($hash, '$P$')) {
            // Phpass
            $hasher = new PasswordHash();
            if ($hasher->CheckPassword($password, $hash)) {
                $this->changePassword($username, $password);
                return true;
            }
        }

        if (password_verify($password, $hash)) {
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

        if (!static::$CAN_UPDATE_PASSWORDS) {
            return 0;
        } elseif (empty($username) || !$this->userExists($username)) {
            return 1;
        } else {
            return User::thisAuth()->where('username', $username)->value('can_modify_passwd');
        }
    }

    public function changePassword($username, $password)
    {
        // check if updating passwords is allowed (mostly for classes that extend this)
        if (!static::$CAN_UPDATE_PASSWORDS) {
            return 0;
        }

        /** @var User $user */
        $user = User::thisAuth()->where('username', $username)->first();

        if ($user) {
            $user->password = password_hash($password, PASSWORD_DEFAULT);

            return $user->save();
        }

        return false;
    }

    public function addUser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 1, $descr = '')
    {
        $user_array = get_defined_vars();

        // no nulls
        $user_array = array_filter($user_array, function ($field) {
            return !is_null($field);
        });

        $new_user = User::thisAuth()->firstOrNew(['username' => $username], $user_array);

        // only update new users
        if (!$new_user->user_id) {
            $new_user->auth_type = LegacyAuth::getType();
            $new_user->password = password_hash($password, PASSWORD_DEFAULT);
            $new_user->email = (string)$new_user->email;

            $new_user->save();
            $user_id = $new_user->user_id;

            // set auth_id
            $new_user->auth_id = $this->getUserid($username);
            $new_user->save();

            if ($user_id) {
                // mark pre-existing notifications as read
                Notification::whereNotExists(function ($query) use ($user_id) {
                    return $query->select(Eloquent::DB()->raw(1))
                        ->from('notifications_attribs')
                        ->whereRaw('notifications.notifications_id = notifications_attribs.notifications_id')
                        ->where('notifications_attribs.user_id', $user_id);
                })->get()->each(function ($notif) use ($user_id) {
                    NotificationAttrib::create([
                        'notifications_id' => $notif->notifications_id,
                        'user_id' => $user_id,
                        'key' => 'read',
                        'value' => 1
                    ]);
                });

                return $user_id;
            }
        }

        return false;
    }

    public function userExists($username, $throw_exception = false)
    {
        return User::thisAuth()->where('username', $username)->exists();
    }

    public function getUserlevel($username)
    {
        return User::thisAuth()->where('username', $username)->value('level');
    }

    public function getUserid($username)
    {
        // for mysql user_id == auth_id
        return User::thisAuth()->where('username', $username)->value('user_id');
    }

    public function deleteUser($user_id)
    {
        // could be used on cli, use Eloquent helper
        Eloquent::DB()->table('bill_perms')->where('user_id', $user_id)->delete();
        Eloquent::DB()->table('devices_perms')->where('user_id', $user_id)->delete();
        Eloquent::DB()->table('ports_perms')->where('user_id', $user_id)->delete();
        Eloquent::DB()->table('users_prefs')->where('user_id', $user_id)->delete();

        return User::destroy($user_id);
    }

    public function getUserlist()
    {
        return User::thisAuth()->orderBy('username')->get()->toArray();
    }

    public function getUser($user_id)
    {
        $user = User::find($user_id);
        if ($user) {
            return $user->toArray();
        }
        return null;
    }

    public function updateUser($user_id, $realname, $level, $can_modify_passwd, $email)
    {
        $user = User::find($user_id);

        $user->realname = $realname;
        $user->level = (int)$level;
        $user->can_modify_passwd = (int)$can_modify_passwd;
        $user->email = $email;

        $user->save();
    }
}
