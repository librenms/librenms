<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class ADAuthorizationAuthorizer extends MysqlAuthorizer
{
    protected static $AUTH_IS_EXTERNAL = 1;
    protected static $CAN_UPDATE_PASSWORDS = 0;

    protected $ldap_connection;

    public function __construct()
    {
        if (! isset($_SESSION['username'])) {
            $_SESSION['username'] = '';
        }

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
        if (isset($_SERVER['REMOTE_USER'])) {
            $_SESSION['username'] = mres($_SERVER['REMOTE_USER']);

            if ($this->userExists($_SESSION['username'])) {
                $this->addUser($username, null);
                return true;
            }

            $_SESSION['username'] = Config::get('http_auth_guest');
            return true;
        }

        throw new AuthenticationException();
    }

    public function addUser($username, $password, $level = 0, $email = '', $realname = '', $can_modify_passwd = 0, $description = '')
    {
        // Check to see if user is already added in the database
        if (!$this->userExists($username)) {
            $userid = dbInsert(array('username' => $username, 'realname' => $realname, 'email' => $email, 'descr' => $description, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'user_id' => $this->getUserid($username)), 'users');
            if ($userid == false) {
                return false;
            } else {
                foreach (dbFetchRows('select notifications.* from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?) order by notifications.notifications_id desc', array($userid)) as $notif) {
                    dbInsert(array('notifications_id'=>$notif['notifications_id'],'user_id'=>$userid,'key'=>'read','value'=>1), 'notifications_attribs');
                }
            }
            return $userid;
        } else {
            return false;
        }
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

    protected function authLdapSessionCacheGet($attr)
    {
        $ttl = 300;
        if (Config::get('auth_ldap_cache_ttl')) {
            $ttl = Config::get('auth_ldap_cache_ttl');
        }

        // auth_ldap cache present in this session?
        if (! isset($_SESSION['auth_ldap'])) {
            return null;
        }

        $cache = $_SESSION['auth_ldap'];

        // $attr present in cache?
        if (! isset($cache[$attr])) {
            return null;
        }

        // Value still valid?
        if (time() - $cache[$attr]['last_updated'] >= $ttl) {
            return null;
        }

        return $cache[$attr]['value'];
    }


    protected function authLdapSessionCacheSet($attr, $value)
    {
        $_SESSION['auth_ldap'][$attr]['value'] = $value;
        $_SESSION['auth_ldap'][$attr]['last_updated'] = time();
    }
}
