<?php

namespace LibreNMS\Interfaces\Authentication;

interface Authorizer
{
    public function authenticate($username, $password);

    public function reauthenticate($sess_id, $token);

    public function passwordscanchange($username = '');

    public function changepassword($username, $newpassword);

    public function authUsermanagement();

    public function adduser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 0, $description = '');

    public function userExists($username, $throw_exception = false);

    public function getUserlevel($username);

    public function getUserid($username);

    public function getUser($user_id);

    public function deluser($userid);

    public function getUserlist();

    public function canUpdateUsers();

    public function updateUser($user_id, $realname, $level, $can_modify_passwd, $email);

    public function logOutUser($message = 'Logged Out');

    public function logInUser();

    public function sessionAuthenticated();
}
