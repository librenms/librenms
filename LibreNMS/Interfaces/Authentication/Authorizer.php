<?php

namespace LibreNMS\Interfaces\Authentication;

use LibreNMS\Exceptions\AuthenticationException;

interface Authorizer
{
    /**
     * Authenticate the user and password.
     * Some Authorizer methods may only check username.
     *
     * @param array $credentials
     * @return true throws an Exception on failure
     * @throws AuthenticationException thrown if the username or password is invalid
     */
    public function authenticate($credentials);

    /**
     * Check if a $username exists.
     *
     * @param string $username
     * @param bool $throw_exception Allows for a message to be sent to callers in case the user does not exist
     * @return bool
     */
    public function userExists($username, $throw_exception = false);

    /**
     * Get the userlevel of $username
     *
     * @param string $username The username to check
     * @return int
     */
    public function getUserlevel($username);

    /**
     * Get the user_id of $username
     *
     * @param string $username
     * @return int
     */
    public function getUserid($username);

    /**
     * Get an array describing this $user_id.
     *
     * It should contain the fields:
     * user_id
     * username
     * realname
     * email
     * descr
     * level
     * can_modify_passwd
     *
     * @param int $user_id
     * @return array|false
     */
    public function getUser($user_id);

    /**
     * Add a new user.
     *
     * @param string $username
     * @param string $password
     * @param int $level
     * @param string $email
     * @param string $realname
     * @param int $can_modify_passwd If this user is allowed to edit their password
     * @param string $description
     * @return int|false Returns the added user_id or false if adding failed
     */
    public function addUser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 0, $description = '');

    /**
     * Update the some of the fields of a user
     *
     * @param int $user_id The user_id to update
     * @param string $realname
     * @param int $level
     * @param int $can_modify_passwd
     * @param string $email
     * @return bool If the update was successful
     */
    public function updateUser($user_id, $realname, $level, $can_modify_passwd, $email);

    /**
     * @param string $username The $username to update
     * @param string $newpassword
     * @return bool If the update was successful
     */
    public function changePassword($username, $newpassword);

    /**
     * Delete a user.
     *
     * @param int $user_id
     * @return bool If the deletion was successful
     */
    public function deleteUser($user_id);

    /**
     * Get a list of all users in this Authorizer
     * !Warning! this could be very slow for some Authorizer types or configurations
     *
     * @return array
     */
    public function getUserlist();

    /**
     * Check if this Authorizer can add or remove users.
     * You must also check canUpdateUsers() to see if it can edit users.
     * You must check canUpdatePasswords() to see if it can set passwords.
     *
     * @return bool
     */
    public function canManageUsers();

    /**
     * Check if this Authorizer can modify users.
     *
     * @return bool
     */
    public function canUpdateUsers();

    /**
     * Check if this Authorizer can set new passwords.
     *
     * @param string $username Optionally, check if $username can set their own password
     * @return bool
     */
    public function canUpdatePasswords($username = '');

    /**
     * Indicates if the authentication happens within the LibreNMS process, or external to it.
     * If the former, LibreNMS provides a login form, and the user must supply the username. If the latter, the authenticator supplies it via getExternalUsername() without user interaction.
     * This is an important distinction, because at the point this is called if the authentication happens out of process, the user is already authenticated and LibreNMS must not display a login form - even if something fails.
     *
     * @return bool
     */
    public function authIsExternal();

    /**
     * The username provided by an external authenticator.
     *
     * @return string|null
     */
    public function getExternalUsername();
}
