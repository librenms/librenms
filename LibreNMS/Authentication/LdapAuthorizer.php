<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class LdapAuthorizer extends AuthorizerBase
{
    protected $ldap_connection;

    public function __construct()
    {
        $this->ldap_connection = @ldap_connect(Config::get('auth_ldap_server'), Config::get('auth_ldap_port'));

        if (Config::get('auth_ldap_starttls') && (Config::get('auth_ldap_starttls') == 'optional' || Config::get('auth_ldap_starttls') == 'require')) {
            $tls = ldap_start_tls($this->ldap_connection);
            if (Config::get('auth_ldap_starttls') == 'require' && $tls === false) {
                echo '<h2>Fatal error: LDAP TLS required but not successfully negotiated:'.ldap_error($this->ldap_connection).'</h2>';
                exit;
            }
        }
    }

    public function authenticate($username, $password)
    {
        if (!$this->ldap_connection) {
            throw new AuthenticationException('Unable to connect to ldap server');
        }

        if ($username) {
            if (Config::get('auth_ldap_version')) {
                ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, Config::get('auth_ldap_version'));
            }

            if ($password && ldap_bind($this->ldap_connection, Config::get('auth_ldap_prefix').$username.Config::get('auth_ldap_suffix'), $password)) {
                if (!Config::get('auth_ldap_group')) {
                    return true;
                } else {
                    $ldap_groups = $this->getGroupList();
                    foreach ($ldap_groups as $ldap_group) {
                        $ldap_comparison = ldap_compare(
                            $this->ldap_connection,
                            $ldap_group,
                            Config::get('auth_ldap_groupmemberattr'),
                            $this->getMembername($username)
                        );
                        if ($ldap_comparison === true) {
                            return true;
                        }
                    }
                }
            }

            if (!isset($password) || $password == '') {
                throw new AuthenticationException('A password is required');
            }

            throw new AuthenticationException(ldap_error($this->ldap_connection));
        }

        throw new AuthenticationException();
    }


    public function userExists($username)
    {
        $filter  = '('.Config::get('auth_ldap_prefix').$username.')';
        $search  = ldap_search($this->ldap_connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);
        if ($entries['count']) {
            return 1;
        }

        return 0;
    }


    public function getUserlevel($username)
    {
        $userlevel = 0;

        // Find all defined groups $username is in
        $filter  = '(&(|(cn='.join(')(cn=', array_keys(Config::get('auth_ldap_groups'))).'))('.Config::get('auth_ldap_groupmemberattr').'='.$this->getMembername($username).'))';
        $search  = ldap_search($this->ldap_connection, Config::get('auth_ldap_groupbase'), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);

        // Loop the list and find the highest level
        foreach ($entries as $entry) {
            $groupname = $entry['cn'][0];

            $authLdapGroups = Config::get('auth_ldap_groups');
            if ($authLdapGroups[$groupname]['level'] > $userlevel) {
                $userlevel = $authLdapGroups[$groupname]['level'];
            }
        }

        return $userlevel;
    }


    public function getUserid($username)
    {
        $filter  = '('.Config::get('auth_ldap_prefix').$username.')';
        $search  = ldap_search($this->ldap_connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            return $entries[0][Config::get('auth_ldap_uid_attribute')][0];
        }

        return -1;
    }

    public function getUserlist()
    {
        $userlist = array();

        $filter = '('.Config::get('auth_ldap_prefix').'*)';

        $search  = ldap_search($this->ldap_connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            foreach ($entries as $entry) {
                $username    = $entry['uid'][0];
                $realname    = $entry['cn'][0];
                $user_id     = $entry[Config::get('auth_ldap_uid_attribute')][0];
                $email       = $entry[Config::get('auth_ldap_emailattr')][0];
                $ldap_groups = $this->getGroupList();
                foreach ($ldap_groups as $ldap_group) {
                    $ldap_comparison = ldap_compare(
                        $this->ldap_connection,
                        $ldap_group,
                        Config::get('auth_ldap_groupmemberattr'),
                        $this->getMembername($username)
                    );
                    if (!Config::has('auth_ldap_group') || $ldap_comparison === true) {
                        $userlist[] = array(
                                       'username' => $username,
                                       'realname' => $realname,
                                       'user_id'  => $user_id,
                                       'email'    => $email,
                                      );
                    }
                }
            }
        }
        return $userlist;
    }


    public function getUser($user_id)
    {
        foreach ($this->getUserlist() as $users) {
            if ($users['user_id'] === $user_id) {
                return $users;
            }
        }
        return 0;
    }


    protected function getMembername($username)
    {
        if (Config::get('auth_ldap_groupmembertype') == 'fulldn') {
            $membername = Config::get('auth_ldap_prefix').$username.Config::get('auth_ldap_suffix');
        } elseif (Config::get('auth_ldap_groupmembertype') == 'puredn') {
            $filter  = '('.Config::get('auth_ldap_attr')['uid'].'='.$username.')';
            $search  = ldap_search($this->ldap_connection, Config::get('auth_ldap_groupbase'), $filter);
            $entries = ldap_get_entries($this->ldap_connection, $search);
            $membername = $entries[0]['dn'];
        } else {
            $membername = $username;
        }

        return $membername;
    }


    public function getGroupList()
    {
        $ldap_groups   = array();
        $default_group = 'cn=groupname,ou=groups,dc=example,dc=com';
        if (Config::has('auth_ldap_group')) {
            if (Config::get('auth_ldap_group') !== $default_group) {
                $ldap_groups[] = Config::get('auth_ldap_group');
            }
        }

        foreach (Config::get('auth_ldap_groups') as $key => $value) {
            $dn            = "cn=$key,".Config::get('auth_ldap_groupbase');
            $ldap_groups[] = $dn;
        }

        return $ldap_groups;
    }
}
