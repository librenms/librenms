<?php

namespace LibreNMS\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\Exceptions\AuthenticationException;

class NssPamAuthorizer extends AuthorizerBase
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = false;
    protected static $CAN_UPDATE_PASSWORDS = false;
    protected static $AUTH_IS_EXTERNAL = true;

    public function authenticate($credentials)
    {
        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;
        $service = 'librenms'; // default to librenms if not set
        if (Config::has('nss_pam_auth_service')) {
            $service = Config::get('nss_pam_auth_service');
        }

        $error;

        if (pam_auth($username, $password, $error, true, $service)) {
            return true;
        }

        throw new AuthenticationException();
    }

    public function canUpdatePasswords($username = '')
    {
         return false;
    }

    public function userExists($username, $throw_exception = false)
    {
        if(posix_getpwnam($username)) {
            return true;
        }
        return false;
    }

    public function getUserlevel($username)
    {
        if (Config::has('nss_pam_admin_group')){
            $group = Config::get('nss_pam_admin_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    if ($member == $username) {
                      return 10;
                    }
                }
            }
        }

        if (Config::has('nss_pam_normal_group')){
            $group = Config::get('nss_pam_normal_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    if ($member == $username) {
                      return 1;
                    }
                }
            }
        }
    }

    public function getUserid($username)
    {
        $userinfo = posix_getpwnam($username);
        if ($userinfo) {
            return $userinfo['uid'];
        }
        return;
    }

    public function getUserlist()
    {
        $userlist = array();

        if (Config::has('nss_pam_admin_group')){
            $group = Config::get('nss_pam_admin_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    $userinfo = posix_getpwnam($member);
                    if ($userinfo){
                        $userlist[$member]=array(
                            user_id => $userinfo['uid'],
                            username => $userinfo['name'],
                            auth_type => 'nss_pam',
                            realname => $userinfo['gecos'],
                            email => '',
                            can_modify_passwd => 0,
                            updated_at=>'',
                            created_at=>'',
                            enabled => 1,
                        );
                    }
                }
            }
        }

       if (Config::has('nss_pam_normal_group')){
            $group = Config::get('nss_pam_normal_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    $userinfo = posix_getpwnam($member);
                    if ($userinfo && !isset($userlist[$member]) ){
                        $userlist[$member]=array(
                            user_id => $userinfo['uid'],
                            username => $userinfo['name'],
                            auth_type => 'nss_pam',
                            realname => $userinfo['gecos'],
                            email => '',
                            can_modify_passwd => 0,
                            updated_at=>'',
                            created_at=>'',
                            enabled => 1,
                        );
                    }
                }
            }
        }

       $user_array=array();
       foreach ($userlist as $user) {
           $user_array[]=$user;
       }
       return $user_array;
    }

    public function getUser($user_id)
    {
        $userinfo = posix_getpwuid($user_id);
        if ($userinfo) {
            return $userinfo['name'];
        }

        return false;
    }
}
