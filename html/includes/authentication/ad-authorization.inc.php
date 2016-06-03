<?php

if (! isset ($_SESSION['username'])) {
    $_SESSION['username'] = '';
}

// Disable certificate checking before connect if required
if (isset($config['auth_ad_check_certificates']) &&
        $config['auth_ad_check_certificates'] == 0) {
    putenv('LDAPTLS_REQCERT=never');
};

// Set up connection to LDAP server
$ds = @ldap_connect($config['auth_ad_url']);
if (! $ds) {
    echo '<h2>Fatal error while connecting to AD url ' . $config['auth_ad_url'] . ': ' . ldap_error($ds) . '</h2>';
    exit;
}

// disable referrals and force ldap version to 3
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

// Bind to AD
if (isset($config['auth_ad_binduser']) && isset($config['auth_ad_bindpassword'])) {
    // With specified bind user
    if (! ldap_bind($ds, "${config['auth_ad_binduser']}@${config['auth_ad_domain']}", "${config['auth_ad_bindpassword']}")) {
        echo ldap_error($ds);
    }
}
else {
    // Anonymous
    if (! ldap_bind($ds)) {
        echo ldap_error($ds);
    }
}

function authenticate ($username, $password) {
    global $config;

    if (isset ($_SERVER['REMOTE_USER'])) {
        $_SESSION['username'] = mres ($_SERVER['REMOTE_USER']);

        if (user_exists ($_SESSION['username'])) {
            adduser($username);
            return 1;

        }

        $_SESSION['username'] = $config['http_auth_guest'];
        return 1;
    }

    return 0;
}


function reauthenticate() {
    // not supported so return 0
    return 0;
}


function passwordscanchange() {
    // not supported so return 0
    return 0;
}


function changepassword() {
    // not supported so return 0
    return 0;
}


function auth_usermanagement() {
    // not supported so return 0
    return 0;
}


function adduser($username, $level=0, $email='', $realname='', $can_modify_passwd=0, $description='', $twofactor=0) {
    // Check to see if user is already added in the database
    if (!user_exists_in_db($username)) {
        $userid = dbInsert(array('username' => $username, 'realname' => $realname, 'email' => $email, 'descr' => $description, 'level' => $level, 'can_modify_passwd' => $can_modify_passwd, 'twofactor' => $twofactor, 'user_id' => get_userid($username)), 'users');
        if ($userid == false) {
            return false;
        }
        else {
            foreach (dbFetchRows('select notifications.* from notifications where not exists( select 1 from notifications_attribs where notifications.notifications_id = notifications_attribs.notifications_id and notifications_attribs.user_id = ?) order by notifications.notifications_id desc',array($userid)) as $notif) {
                dbInsert(array('notifications_id'=>$notif['notifications_id'],'user_id'=>$userid,'key'=>'read','value'=>1),'notifications_attribs');
            }
        }
        return $userid;
    }
    else {
        return false;
    }
}

function user_exists_in_db($username) {
    $return = dbFetchCell('SELECT COUNT(*) FROM users WHERE username = ?', array($username), true);
    return $return;
}

function user_exists($username) {
    global $config, $ldap_connection;

    if (auth_ldap_session_cache_get ('user_exists'))
        return 1;

    $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'],
            "(samaccountname=${username})",array('samaccountname'));
    $entries = ldap_get_entries($ldap_connection, $search);

    if ($entries['count']) {
        /*
         * Cache positiv result as this will result in more queries which we
         * want to speed up.
         */
        auth_ldap_session_cache_set ('user_exists', 1);
        return 1;
    }

    return 0;
}


function get_userlevel($username) {
    global $config, $ldap_connection;

    $userlevel = auth_ldap_session_cache_get ('userlevel');
    if ($userlevel) {
        return $userlevel;
    } 
    else {
        $userlevel = 0;
    }

    // Find all defined groups $username is in
    $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'],
            "(samaccountname={$username})", array('memberOf'));
    $entries = ldap_get_entries($ldap_connection, $search);

    // Loop the list and find the highest level
    foreach ($entries[0]['memberof'] as $entry) {
        $group_cn = get_cn($entry);
        if ($config['auth_ad_groups'][$group_cn]['level'] > $userlevel) {
            $userlevel = $config['auth_ad_groups'][$group_cn]['level'];
        }
    }

    auth_ldap_session_cache_set ('userlevel', $userlevel);
    return $userlevel;
}


function get_userid($username) {
    global $config, $ldap_connection;

    $user_id = auth_ldap_session_cache_get ('userid');
    if (isset ($user_id)) {
        return $user_id;
    } 
    else {
        $user_id = -1;
    }

    $attributes = array('objectsid');
    $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'],
            "(samaccountname={$username})", $attributes);    
    $entries = ldap_get_entries($ldap_connection, $search);

    if ($entries['count']) {
        $user_id = preg_replace('/.*-(\d+)$/','$1',sid_from_ldap($entries[0]['objectsid'][0]));
    }

    auth_ldap_session_cache_set ('userid', $user_id);
    return $user_id;
}


function deluser($username) {
    dbDelete('bill_perms', '`user_name` =  ?', array($username));
    dbDelete('devices_perms', '`user_name` =  ?', array($username));
    dbDelete('ports_perms', '`user_name` =  ?', array($username));
    dbDelete('users_prefs', '`user_name` =  ?', array($username));
    dbDelete('users', '`user_name` =  ?', array($username));
    return dbDelete('users', '`username` =  ?', array($username));
}


function get_userlist() {
    global $config, $ldap_connection;
    $userlist = array();
    $userhash = array();

    $ldap_groups = get_group_list();

    foreach($ldap_groups as $ldap_group) {
        $group_cn = get_cn($ldap_group);
        $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'], "(cn={$group_cn})", array('member'));
        $entries = ldap_get_entries($ldap_connection, $search);

        foreach($entries[0]['member'] as $member) {
            $member_cn = get_cn($member);
            $search = ldap_search($ldap_connection, $config['auth_ad_base_dn'], "(cn={$member_cn})",
                    array('sAMAccountname', 'displayName', 'objectSID', 'mail'));
            $results = ldap_get_entries($ldap_connection, $search);
            foreach($results as $result) {
                if(isset($result['samaccountname'][0])) {
                    $userid = preg_replace('/.*-(\d+)$/','$1',
                            sid_from_ldap($result['objectsid'][0]));

                    // don't make duplicates, user may be member of more than one group
                    $userhash[$result['samaccountname'][0]] = array(
                            'realname' => $result['displayName'][0],
                            'user_id'  => $userid,
                            'email'    => $result['mail'][0]
                            );
                }
            }
        }
    }

    foreach(array_keys($userhash) as $key) {
        $userlist[] = array(
                'username' => $key,
                'realname' => $userhash[$key]['realname'],
                'user_id'  => $userhash[$key]['user_id'],
                'email'    => $userhash[$key]['email']
                );
    }

    return $userlist;
}


function can_update_users() {
    // not supported so return 0
    return 0;
}


function get_user($user_id) {
    // not supported so return 0
    return dbFetchRow('SELECT * FROM `users` WHERE `user_id` = ?', array($user_id), true);
}


function update_user($user_id, $realname, $level, $can_modify_passwd, $email) {
    dbUpdate(array('realname' => $realname, 'can_modify_passwd' => $can_modify_passwd, 'email' => $email), 'users', '`user_id` = ?', array($user_id));
}


function get_fullname($username) {
    global $config, $ldap_connection;

    $attributes = array('name');
    $result = ldap_search($ldap_connection, $config['auth_ad_base_dn'],
            "(samaccountname={$username})", $attributes);
    $entries = ldap_get_entries($ldap_connection, $result);
    if ($entries['count'] > 0) {
        $membername = $entries[0]['name'][0];
    }
    else {
        $membername = $username;
    }

    return $membername;
}


function get_group_list() {
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

function get_dn($samaccountname) {
    global $config, $ldap_connection;


    $attributes = array('dn');
    $result = ldap_search($ldap_connection, $config['auth_ad_base_dn'],
            "(samaccountname={$samaccountname})", $attributes);
    $entries = ldap_get_entries($ldap_connection, $result);
    if ($entries['count'] > 0) {
        return $entries[0]['dn'];
    }
    else {
        return '';
    }
}

function get_cn($dn) {
    preg_match('/[^,]*/', $dn, $matches, PREG_OFFSET_CAPTURE, 3);
    return $matches[0][0];
}

function sid_from_ldap($sid)
{
    $sidHex = unpack('H*hex', $sid);
    $subAuths = unpack('H2/H2/n/N/V*', $sid);        
    $revLevel = hexdec(substr($sidHex, 0, 2));
    $authIdent = hexdec(substr($sidHex, 4, 12));      
    return 'S-'.$revLevel.'-'.$authIdent.'-'.implode('-', $subAuths);
}

function auth_ldap_session_cache_get ($attr) {
    global $config;

    $ttl = 300;
    if ($config['auth_ldap_cache_ttl'])
        $ttl = $config['auth_ldap_cache_ttl'];

    // auth_ldap cache present in this session?
    if (! isset ($_SESSION['auth_ldap']))
        return Null;

    $cache = $_SESSION['auth_ldap'];

    // $attr present in cache?
    if (! isset ($cache[$attr]))
        return Null;

    // Value still valid?
    if (time () - $cache[$attr]['last_updated'] >= $ttl)
        return Null;

    return $cache[$attr]['value'];
}


function auth_ldap_session_cache_set ($attr, $value) {
    $_SESSION['auth_ldap'][$attr]['value'] = $value;
    $_SESSION['auth_ldap'][$attr]['last_updated'] = time ();
}

