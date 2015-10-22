<?php

// easier to rewrite for Active Directory than to bash it into existing LDAP implementation

$ds = @ldap_connect($config['auth_ad_url']);

// disable referrals and force ldap version to 3

ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

// disable certificate checking if required

if (isset($config['auth_ad_dont_check_certificates']) &&
          $config['auth_ad_dont_check_certificates'] > 0) {
    putenv('LDAPTLS_REQCERT=never');
};

function authenticate($username, $password) {
    global $config, $ds;

    if ($username && $ds) {
        // bind with sAMAccountName instead of full LDAP DN
        if (ldap_bind($ds, "{$username}@{$config['auth_ad_domain']}", $password)) {
            return 1;
        } else {
            return 0;
        }
    } else {
        echo ldap_error($ds);
    }

    return 0;
}

function reauthenticate($sess_id, $token) {
    // not supported so return 0
    return 0;
}


function passwordscanchange($username='') {
    // not supported so return 0
    return 0;
}


function changepassword($username, $newpassword) {
    // not supported so return 0
    return 0;
}


function auth_usermanagement() {
    // not supported so return 0
    return 0;
}


function adduser($username, $password, $level, $email='', $realname='', $can_modify_passwd='1') {
    // not supported so return 0
    return 0;
}


function user_exists($username) {
    global $config, $ds;

    $search = ldap_search($ds, $config['auth_ad_base_dn'],
                          "(samaccountname={$username})",array('samaccountname'));
    $entries = ldap_get_entries($ds, $search);


    if ($entries['count']) {
        return 1;
    }

    return 0;
}


function get_userlevel($username) {
    global $config, $ds;

    $userlevel = 0;

    // Find all defined groups $username is in
    $search = ldap_search($ds, $config['auth_ad_base_dn'],
                          "(samaccountname={$username})", array('memberOf'));
    $entries = ldap_get_entries($ds, $search);
    
    // Loop the list and find the highest level
    foreach ($entries[0]['memberof'] as $entry) {
        $group_cn = get_cn($entry);
        if ($config['auth_ad_groups'][$group_cn]['level'] > $userlevel) {
            $userlevel = $config['auth_ad_groups'][$group_cn]['level'];
        }
    }

    return $userlevel;
}


function get_userid($username) {
    global $config, $ds;

    $attributes = array('objectsid');
    $result = ldap_search($ds, $config['auth_ad_base_dn'],
                          "(samaccountname={$username})", $attributes);    
    $entries = ldap_get_entries($ds, $search);

    if ($entries['count']) {
        return preg_replace('/.*-(\d+)$/','$1',sid_from_ldap($entries[0]['objectsid'][0]));
    }

    return -1;
}


function deluser($username) {
    // not supported so return 0 
    return 0;
}


function get_userlist() {
    global $config, $ds;
    $userlist = array();

    $ldap_groups = get_group_list();

    foreach($ldap_groups as $ldap_group) {
        $group_cn = get_cn($ldap_group);
        $search = ldap_search($ds, $config['auth_ad_base_dn'], "(cn={$group_cn})", array('member'));
        $entries = ldap_get_entries($ds, $search);
        
        foreach($entries[0]['member'] as $member) {
            $member_cn = get_cn($member);
            $search = ldap_search($ds, $config['auth_ad_base_dn'], "(cn={$member_cn})",
                                  array('sAMAccountname', 'displayName', 'objectSID', 'mail'));
            $results = ldap_get_entries($ds, $search);
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
    return 0;
}


function update_user($user_id, $realname, $level, $can_modify_passwd, $email) {
    // not supported so return 0 
    return 0;

}


function get_fullname($username) {
    global $config, $ds;

    $attributes = array('name');
    $result = ldap_search($ds, $config['auth_ad_base_dn'],
                          "(samaccountname={$username})", $attributes);
    $entries = ldap_get_entries($ds, $result);
    if ($entries['count'] > 0) {
        $membername = $entries[0]['name'][0];
    } else {
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
    global $config, $ds;


    $attributes = array('dn');
    $result = ldap_search($ds, $config['auth_ad_base_dn'],
                          "(samaccountname={$samaccountname})", $attributes);
    $entries = ldap_get_entries($ds, $result);
    if ($entries['count'] > 0) {
        return $entries[0]['dn'];
    } else {
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
