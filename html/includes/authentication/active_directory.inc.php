<?php

// easier to rewrite for Active Directory than to bash it into existing LDAP implementation

// disable certificate checking before connect if required
use LibreNMS\Exceptions\AuthenticationException;

function init_auth()
{
    global $ad_init, $ldap_connection, $config;

    if (isset($config['auth_ad_check_certificates']) &&
        !$config['auth_ad_check_certificates']) {
        putenv('LDAPTLS_REQCERT=never');
    };

    if (isset($config['auth_ad_debug']) && $config['auth_ad_debug']) {
        ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    $ad_init = false;  // this variable tracks if bind has been called so we don't call it multiple times
    $ldap_connection = @ldap_connect($config['auth_ad_url']);

    // disable referrals and force ldap version to 3
    ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
}

function authenticate($username, $password)
{
    global $config, $ldap_connection, $ad_init;

    if ($ldap_connection) {
        // bind with sAMAccountName instead of full LDAP DN
        if ($username && $password && ldap_bind($ldap_connection, "{$username}@{$config['auth_ad_domain']}", $password)) {
            $ad_init = true;
            // group membership in one of the configured groups is required
            if (isset($config['auth_ad_require_groupmembership']) &&
                $config['auth_ad_require_groupmembership']) {
                $search = ldap_search(
                    $ldap_connection,
                    $config['auth_ad_base_dn'],
                    get_auth_ad_user_filter($username),
                    array('memberOf')
                );
                $entries = ldap_get_entries($ldap_connection, $search);
                unset($entries[0]['memberof']['count']); //remove the annoying count

                foreach ($entries[0]['memberof'] as $entry) {
                    $group_cn = get_cn($entry);
                    if (isset($config['auth_ad_groups'][$group_cn]['level'])) {
                        // user is in one of the defined groups
                        return true;
                    }
                }

                // failed to find user
                if (isset($config['auth_ad_debug']) && $config['auth_ad_debug']) {
                    if ($entries['count'] == 0) {
                        throw new AuthenticationException('No groups found for user, check base dn');
                    } else {
                        throw new AuthenticationException('User is not in one of the required groups');
                    }
                }

                throw new AuthenticationException();
            } else {
                // group membership is not required and user is valid
                return true;
            }
        }
    }

    if (!isset($password) || $password == '') {
        throw new AuthenticationException('A password is required');
    } elseif (isset($config['auth_ad_debug']) && $config['auth_ad_debug']) {
        ldap_get_option($ldap_connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
        throw new AuthenticationException(ldap_error($ldap_connection).'<br />'.$extended_error);
    }

    throw new AuthenticationException(ldap_error($ldap_connection));
}

function reauthenticate($sess_id, $token)
{
    global $config, $ldap_connection;

    if (ad_bind($ldap_connection, false, true)) {
        $sess_id = clean($sess_id);
        $token = clean($token);
        list($username, $hash) = explode('|', $token);

        if (!user_exists($username)) {
            if (isset($config['auth_ad_debug']) && $config['auth_ad_debug']) {
                throw new AuthenticationException("$username is not a valid AD user");
            }
            throw new AuthenticationException();
        }

        return check_remember_me($sess_id, $token);
    }

    return false;
}


function passwordscanchange()
{
    // not supported so return 0
    return 0;
}


function changepassword()
{
    // not supported so return 0
    return 0;
}


function auth_usermanagement()
{
    // not supported so return 0
    return 0;
}


function adduser($username, $level = 0, $email = '', $realname = '', $can_modify_passwd = 0, $description = '')
{
    // not supported so return 0
    return 0;
}

function user_exists_in_db($username)
{
    $return = dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username), true);
    return $return;
}

function user_exists($username)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $search = ldap_search(
        $ldap_connection,
        $config['auth_ad_base_dn'],
        get_auth_ad_user_filter($username),
        array('samaccountname')
    );
    $entries = ldap_get_entries($ldap_connection, $search);


    if ($entries['count']) {
        return 1;
    }

    return 0;
}


function get_userlevel($username)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $userlevel = 0;
    if (isset($config['auth_ad_require_groupmembership']) && $config['auth_ad_require_groupmembership'] == 0) {
        if (isset($config['auth_ad_global_read']) && $config['auth_ad_global_read'] === 1) {
            $userlevel = 5;
        }
    }

    // Find all defined groups $username is in
    $search = ldap_search(
        $ldap_connection,
        $config['auth_ad_base_dn'],
        get_auth_ad_user_filter($username),
        array('memberOf')
    );
    $entries = ldap_get_entries($ldap_connection, $search);
    unset($entries[0]['memberof']['count']);

    // Loop the list and find the highest level
    foreach ($entries[0]['memberof'] as $entry) {
        $group_cn = get_cn($entry);
        if (isset($config['auth_ad_groups'][$group_cn]['level']) &&
             $config['auth_ad_groups'][$group_cn]['level'] > $userlevel) {
            $userlevel = $config['auth_ad_groups'][$group_cn]['level'];
        }
    }

    return $userlevel;
}


function get_userid($username)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $attributes = array('objectsid');
    $search = ldap_search(
        $ldap_connection,
        $config['auth_ad_base_dn'],
        get_auth_ad_user_filter($username),
        $attributes
    );
    $entries = ldap_get_entries($ldap_connection, $search);

    if ($entries['count']) {
        return get_userid_from_sid(sid_from_ldap($entries[0]['objectsid'][0]));
    }

    return -1;
}

function get_domain_sid()
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    // Extract only the domain components
    $dn_candidate = preg_replace('/^.*?DC=/i', 'DC=', $config['auth_ad_base_dn']);

    $search = ldap_read(
        $ldap_connection,
        $dn_candidate,
        '(objectClass=*)',
        array('objectsid')
    );
    $entry = ldap_get_entries($ldap_connection, $search);
    return substr(sid_from_ldap($entry[0]['objectsid'][0]), 0, 41);
}

function get_user($user_id)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $domain_sid = get_domain_sid();

    $search_filter = "(&(objectcategory=person)(objectclass=user)(objectsid=$domain_sid-$user_id))";
    $attributes = array('samaccountname', 'displayname', 'objectsid', 'mail');
    $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'], $search_filter, $attributes);
    $entry = ldap_get_entries($ldap_connection, $search);

    if (isset($entry[0]['samaccountname'][0])) {
        return user_from_ad($entry[0]);
    }

    return array();
}

function deluser($userid)
{
    dbDelete('bill_perms', '`user_id` =  ?', array($userid));
    dbDelete('devices_perms', '`user_id` =  ?', array($userid));
    dbDelete('ports_perms', '`user_id` =  ?', array($userid));
    dbDelete('users_prefs', '`user_id` =  ?', array($userid));
    return 0;
}


function get_userlist()
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $userlist = array();
    $ldap_groups = get_group_list();

    foreach ($ldap_groups as $ldap_group) {
        $search_filter = "(memberOf=$ldap_group)";
        if ($config['auth_ad_user_filter']) {
            $search_filter = "(&{$config['auth_ad_user_filter']}$search_filter)";
        }
        $attributes = array('samaccountname', 'displayname', 'objectsid', 'mail');
        $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'], $search_filter, $attributes);
        $results = ldap_get_entries($ldap_connection, $search);

        foreach ($results as $result) {
            if (isset($result['samaccountname'][0])) {
                $userlist[$result['samaccountname'][0]] = user_from_ad($result);
            }
        }
    }

    return array_values($userlist);
}

/**
 * Generate a user array from an AD LDAP entry
 * Must have the attributes: objectsid, samaccountname, displayname, mail
 * @internal
 *
 * @param $entry
 * @return array
 */
function user_from_ad($entry)
{
    return array(
        'user_id' => get_userid_from_sid(sid_from_ldap($entry['objectsid'][0])),
        'username' => $entry['samaccountname'][0],
        'realname' => $entry['displayname'][0],
        'email' => $entry['mail'][0],
        'descr' => '',
        'level' => get_userlevel($entry['samaccountname'][0]),
        'can_modify_passwd' => 0,
        'twofactor' => 0,
        // 'dashboard' => 'broken!',
    );
}


function can_update_users()
{
    // not supported so return 0
    return 0;
}


function update_user($user_id, $realname, $level, $can_modify_passwd, $email)
{
    // not supported so return 0
    return 0;
}

function get_email($username)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $attributes = array('mail');
    $search = ldap_search(
        $ldap_connection,
        $config['auth_ad_base_dn'],
        get_auth_ad_user_filter($username),
        $attributes
    );
    $result = ldap_get_entries($ldap_connection, $search);
    unset($result[0]['mail']['count']);
    return current($result[0]['mail']);
}

function get_fullname($username)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $attributes = array('name');
    $result = ldap_search(
        $ldap_connection,
        $config['auth_ad_base_dn'],
        get_auth_ad_user_filter($username),
        $attributes
    );
    $entries = ldap_get_entries($ldap_connection, $result);
    if ($entries['count'] > 0) {
        $membername = $entries[0]['name'][0];
    } else {
        $membername = $username;
    }

    return $membername;
}


function get_group_list()
{
    global $config;

    $ldap_groups   = array();

    // show all Active Directory Users by default
    $default_group = 'Users';

    if (isset($config['auth_ad_group'])) {
        if ($config['auth_ad_group'] !== $default_group) {
            $ldap_groups[] = $config['auth_ad_group'];
        }
    }

    if (!isset($config['auth_ad_groups']) && !isset($config['auth_ad_group'])) {
        $ldap_groups[] = get_dn($default_group);
    }

    foreach ($config['auth_ad_groups'] as $key => $value) {
        $ldap_groups[] = get_dn($key);
    }

    return $ldap_groups;
}

function get_dn($samaccountname)
{
    global $config, $ldap_connection;
    ad_bind($ldap_connection); // make sure we called bind

    $attributes = array('dn');
    $result = ldap_search(
        $ldap_connection,
        $config['auth_ad_base_dn'],
        get_auth_ad_group_filter($samaccountname),
        $attributes
    );
    $entries = ldap_get_entries($ldap_connection, $result);
    if ($entries['count'] > 0) {
        return $entries[0]['dn'];
    } else {
        return '';
    }
}

function get_cn($dn)
{
    $dn = str_replace('\\,', '~C0mmA~', $dn);
    preg_match('/[^,]*/', $dn, $matches, PREG_OFFSET_CAPTURE, 3);
    return str_replace('~C0mmA~', ',', $matches[0][0]);
}

function get_userid_from_sid($sid)
{
    return preg_replace('/.*-(\d+)$/', '$1', $sid);
}

function sid_from_ldap($sid)
{
        $sidUnpacked = unpack('H*hex', $sid);
        $sidHex = array_shift($sidUnpacked);
        $subAuths = unpack('H2/H2/n/N/V*', $sid);
        $revLevel = hexdec(substr($sidHex, 0, 2));
        $authIdent = hexdec(substr($sidHex, 4, 12));
        return 'S-'.$revLevel.'-'.$authIdent.'-'.implode('-', $subAuths);
}

/**
 * Bind to AD with the bind user if available, otherwise anonymous bind
 * @internal
 *
 * @param resource $connection the ldap connection resource
 * @param bool $allow_anonymous attempt anonymous bind if bind user isn't available
 * @param bool $force force rebind
 * @return bool success or failure
 */
function ad_bind($connection, $allow_anonymous = true, $force = false)
{
    global $config, $ad_init;

    if ($ad_init && !$force) {
        return true; // bind already attempted
    }

    // set timeout
    ldap_set_option(
        $connection,
        LDAP_OPT_NETWORK_TIMEOUT,
        isset($config['auth_ad_timeout']) ? isset($config['auth_ad_timeout']) : 5
    );

    // With specified bind user
    if (isset($config['auth_ad_binduser'], $config['auth_ad_bindpassword'])) {
        $ad_init = true;
        $bind = ldap_bind(
            $connection,
            "${config['auth_ad_binduser']}@${config['auth_ad_domain']}",
            "${config['auth_ad_bindpassword']}"
        );
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
        return $bind;
    }

    $bind = false;

    // Anonymous
    if ($allow_anonymous) {
        $ad_init = true;
        $bind = ldap_bind($connection);
    }

    ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
    return $bind;
}
