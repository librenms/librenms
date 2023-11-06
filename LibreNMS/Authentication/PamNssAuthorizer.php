<?php

namespace LibreNMS\Authentication;

use LibreNMS\Exceptions\AuthenticationException;

class PamNssAuthorizer extends AuthorizerBase
{
    public function authenticate($credentials)
    {
        if (empty($credentials['password'])) {
            throw new AuthenticationException('A password is required');
        }

        if (empty($credentials['username'])) {
            throw new AuthenticationException('A username is required');
        }

        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        $service = Config::get('auth_pamnss_service');

        if (pam_auth($username, $password, $error, true, $service)) {
            return true;
        }

        throw new AuthenticationException();
    }

    public function userExists($username, $throw_exception = false)
    {
        if (posix_getpwnam($username)) {
            return true;
        }

        return false;
    }

    public function getRoles($username)
    {
        // start with most perms and work lower, given users are likely to be a member of more
        // than one group.

        if (Config::has('auth_pamnss_admin_group')) {
            $group = Config::get('auth_pamnss_admin_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    if ($member == $username) {
                        return 'admin';
                    }
                }
            }
        }

        if (Config::has('auth_pamnss_user_group')) {
            $group = Config::get('auth_pamnss_user_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    if ($member == $username) {
                        return 'user';
                    }
                }
            }
        }

        if (Config::has('auth_pamnss_global_read_group')) {
            $group = Config::get('auth_pamnss_global_read_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    if ($member == $username) {
                        return 'global-read';
                    }
                }
            }
        }

        if (Config::has('auth_pamnss_global_read_group')) {
            $group = Config::get('auth_pamnss_demo_group');
            $groupinfo = posix_getgrnam($group);
            if ($groupinfo) {
                foreach ($groupinfo['members'] as $member) {
                    if ($member == $username) {
                        return 'demo';
                    }
                }
            }
        }

        return false;
    }

    public function getUserid($username)
    {
        if (is_null($username)) {
            return -1;
        }
        $userinfo = posix_getpwnam($username);
        if ($userinfo) {
            return $userinfo['uid'];
        }

        return -1;
    }

    public function getUser($user_id)
    {
        if (is_null($user_id)) {
            return false;
        }

        $userinfo = posix_getpwuid($user_id);
        if ($userinfo) {
            return $userinfo['name'];
        }

        return false;
    }
}
