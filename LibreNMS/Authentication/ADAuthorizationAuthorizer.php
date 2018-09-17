<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class ADAuthorizationAuthorizer extends MysqlAuthorizer
{
    use LdapSessionCache;

    protected static $AUTH_IS_EXTERNAL = 1;
    protected static $CAN_UPDATE_PASSWORDS = 0;

    protected $ldap_connection;

    public function __construct()
    {
        if (!function_exists('ldap_connect')) {
            throw new AuthenticationException("PHP does not support LDAP, please install or enable the PHP LDAP extension.");
        }

        // Disable certificate checking before connect if required
        if (Config::has('auth_ad_check_certificates') &&
            Config::get('auth_ad_check_certificates') == 0) {
            putenv('LDAPTLS_REQCERT=never');
        };

        // Set up connection to LDAP server
        $this->ldap_connection = @ldap_connect(Config::get('auth_ad_url'));
        if (! $this->ldap_connection) {
            throw new AuthenticationException('Fatal error while connecting to AD url ' . Config::get('auth_ad_url') . ': ' . ldap_error($this->ldap_connection));
        }

        // disable referrals and force ldap version to 3
        ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        // Bind to AD
        if (Config::has('auth_ad_binduser') && Config::has('auth_ad_bindpassword')) {
            // With specified bind user
            if (! ldap_bind($this->ldap_connection, Config::get('auth_ad_binduser') . '@' . Config::get('auth_ad_domain'), Config::get('auth_ad_bindpassword'))) {
                echo ldap_error($this->ldap_connection);
            }
        } else {
            // Anonymous
            if (! ldap_bind($this->ldap_connection)) {
                echo ldap_error($this->ldap_connection);
            }
        }
    }

    public function authenticate($username, $password)
    {
        if ($this->userExists($username)) {
            $this->addUser($username, null);
            return true;
        }

        if (Config::get('http_auth_guest')) {
            return true;
        }

        throw new AuthenticationException();
    }

    public function userExists($username, $throw_exception = false)
    {
        if ($this->authLdapSessionCacheGet('user_exists')) {
            return 1;
        }

        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            ActiveDirectoryAuthorizer::userFilter($username),
            array('samaccountname')
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            /*
             * Cache positiv result as this will result in more queries which we
             * want to speed up.
             */
            $this->authLdapSessionCacheSet('user_exists', 1);
            return 1;
        }

        return 0;
    }


    public function getUserlevel($username)
    {
        $userlevel = $this->authLdapSessionCacheGet('userlevel');
        if ($userlevel) {
            return $userlevel;
        } else {
            $userlevel = 0;
        }

        // Find all defined groups $username is in
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            ActiveDirectoryAuthorizer::userFilter($username),
            array('memberOf')
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        // Loop the list and find the highest level
        foreach ($entries[0]['memberof'] as $entry) {
            $group_cn = $this->getCn($entry);
            $auth_ad_groups = Config::get('auth_ad_groups');
            if ($auth_ad_groups[$group_cn]['level'] > $userlevel) {
                $userlevel = $auth_ad_groups[$group_cn]['level'];
            }
        }

        $this->authLdapSessionCacheSet('userlevel', $userlevel);
        return $userlevel;
    }


    public function getUserid($username)
    {
        $user_id = $this->authLdapSessionCacheGet('userid');
        if (isset($user_id)) {
            return $user_id;
        } else {
            $user_id = -1;
        }

        $attributes = array('objectsid');
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            ActiveDirectoryAuthorizer::userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            $user_id = preg_replace('/.*-(\d+)$/', '$1', $this->sidFromLdap($entries[0]['objectsid'][0]));
        }

        $this->authLdapSessionCacheSet('userid', $user_id);
        return $user_id;
    }

    public function getUserlist()
    {
        $userlist = array();
        $userhash = array();

        $ldap_groups = $this->getGroupList();

        foreach ($ldap_groups as $ldap_group) {
            $search_filter = "(&(memberOf:1.2.840.113556.1.4.1941:=$ldap_group)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
            if (Config::get('auth_ad_user_filter')) {
                $search_filter = "(&{" . Config::get('auth_ad_user_filter') . $search_filter . ")";
            }
            $search = ldap_search($this->ldap_connection, Config::get('auth_ad_base_dn'), $search_filter, array('samaccountname','displayname','objectsid','mail'));
            $results = ldap_get_entries($this->ldap_connection, $search);

            foreach ($results as $result) {
                if (isset($result['samaccountname'][0])) {
                    $userid = preg_replace(
                        '/.*-(\d+)$/',
                        '$1',
                        $this->sidFromLdap($result['objectsid'][0])
                    );

                    // don't make duplicates, user may be member of more than one group
                    $userhash[$result['samaccountname'][0]] = array(
                        'realname' => $result['displayName'][0],
                        'user_id'  => $userid,
                        'email'    => $result['mail'][0]
                    );
                }
            }
        }

        foreach (array_keys($userhash) as $key) {
            $userlist[] = array(
                'username' => $key,
                'realname' => $userhash[$key]['realname'],
                'user_id'  => $userhash[$key]['user_id'],
                'email'    => $userhash[$key]['email']
            );
        }

        return $userlist;
    }

    protected function getFullname($username)
    {
        $attributes = array('name');
        $result = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            ActiveDirectoryAuthorizer::userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $result);
        if ($entries['count'] > 0) {
            $membername = $entries[0]['name'][0];
        } else {
            $membername = $username;
        }

        return $membername;
    }


    public function getGroupList()
    {
        $ldap_groups   = array();

        // show all Active Directory Users by default
        $default_group = 'Users';

        if (Config::has('auth_ad_group')) {
            if (Config::get('auth_ad_group') !== $default_group) {
                $ldap_groups[] = Config::get('auth_ad_group');
            }
        }

        if (!Config::has('auth_ad_groups') && !Config::has('auth_ad_group')) {
            $ldap_groups[] = $this->getDn($default_group);
        }

        foreach (Config::get('auth_ad_groups') as $key => $value) {
            $ldap_groups[] = $this->getDn($key);
        }

        return $ldap_groups;
    }

    protected function getDn($samaccountname)
    {
        $attributes = array('dn');
        $result = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            ActiveDirectoryAuthorizer::groupFilter($samaccountname),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $result);
        if ($entries['count'] > 0) {
            return $entries[0]['dn'];
        } else {
            return '';
        }
    }

    protected function getCn($dn)
    {
        preg_match('/[^,]*/', $dn, $matches, PREG_OFFSET_CAPTURE, 3);
        return $matches[0][0];
    }

    protected function sidFromLdap($sid)
    {
        $sidHex = unpack('H*hex', $sid);
        $subAuths = unpack('H2/H2/n/N/V*', $sid);
        $revLevel = hexdec(substr($sidHex, 0, 2));
        $authIdent = hexdec(substr($sidHex, 4, 12));
        return 'S-'.$revLevel.'-'.$authIdent.'-'.implode('-', $subAuths);
    }
}
