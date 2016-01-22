<?php
/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * libreNMS HTTP-Authentication and LDAP Authorization Library
 * @author Maximilian Wilhelm <max@rfc2324.org>
 * @copyright 2016 LibreNMS, Barbarossa
 * @license GPL
 * @package LibreNMS
 * @subpackage Authentication
 *
 * This Authentitation / Authorization module provides the ability to let
 * the webserver (e.g. Apache) do the user Authentication (using Kerberos
 * f.e.) and let libreNMS do the Authorization of the already known user.
 * Authorization and setting of libreNMS user level is done by LDAP group
 * names specified in the configuration file. The group configuration is
 * basicly copied from the existing ldap Authentication module.
 *
 * Most of the code is copied from the http-auth and ldap Authentication
 * modules already existing.
 *
 * To save lots of redundant queries to the LDAP server and speed up the
 * libreNMS WebUI, all information is cached within the PHP $_SESSION as
 * long as specified in $config['auth_ldap_cache_ttl'] (Default: 300s).
 */


if (! isset ($_SESSION['username'])) {
    $_SESSION['username'] = '';
}

/**
 * Set up connection to LDAP server
 */
$ds = @ldap_connect ($config['auth_ldap_server'], $config['auth_ldap_port']);
if (! $ds) {
    echo '<h2>Fatal error while connecting to LDAP server ' . $config['auth_ldap_server'] . ':' . $config['auth_ldap_port'] . ': ' . ldap_error($ds) . '</h2>';
    exit;
}
if ($config['auth_ldap_version']) {
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $config['auth_ldap_version']);
}

if ($config['auth_ldap_starttls'] && ($config['auth_ldap_starttls'] == 'optional' || $config['auth_ldap_starttls'] == 'require')) {
    $tls = ldap_start_tls($ds);
    if ($config['auth_ldap_starttls'] == 'require' && $tls === false) {
        echo '<h2>Fatal error: LDAP TLS required but not successfully negotiated:' . ldap_error($ds) . '</h2>';
        exit;
    }
}


function authenticate ($username, $password) {
    global $config;

    if (isset ($_SERVER['REMOTE_USER'])) {
        $_SESSION['username'] = mres ($_SERVER['REMOTE_USER']);

        if (user_exists ($_SESSION['username'])) {
            return 1;
        }

        $_SESSION['username'] = $config['http_auth_guest'];
        return 1;
    }

    return 0;
}


function reauthenticate ($sess_id='', $token='') {
    // Not supported
    return 0;
}


function passwordscanchange ($username='') {
    // Not supported
    return 0;
}


function changepassword ($username, $newpassword) {
    // Not supported
    return 0;
}


function auth_usermanagement () {
    // Not supported
    return 0;
}


function adduser ($username, $password, $level, $email = '', $realname = '', $can_modify_passwd = 1, $description = '', $twofactor = 0) {
    // Not supported
    return false;
}


function user_exists ($username) {
    global $config, $ds;

    if (auth_ldap_session_cache_get ('user_exists'))
        return 1;

    $filter  = '(' . $config['auth_ldap_prefix'] . $username . ')';
    $search  = ldap_search ($ds, trim ($config['auth_ldap_suffix'], ','), $filter);
    $entries = ldap_get_entries ($ds, $search);
    if ($entries['count']) {
        /*
	 * Cache positiv result as this will result in more queries which we
	 * want to speed up.
	 */
        auth_ldap_session_cache_set ('user_exists', 1);
        return 1;
    }

    /*
     * Don't cache that user doesn't exists as this might be a misconfiguration
     * on some end and the user will be happy if it "just works" after the user
     * has been added to LDAP.
     */
    return 0;
}


function get_userlevel ($username) {
    global $config, $ds;

    $userlevel = auth_ldap_session_cache_get ('userlevel');
    if ($userlevel) {
        return $userlevel;
    } else {
        $userlevel = 0;
    }

    // Find all defined groups $username is in
    $filter  = '(&(|(cn=' . join (')(cn=', array_keys ($config['auth_ldap_groups'])) . '))(' . $config['auth_ldap_groupmemberattr'] .'=' . get_membername ($username) . '))';
    $search  = ldap_search ($ds, $config['auth_ldap_groupbase'], $filter);
    $entries = ldap_get_entries($ds, $search);

    // Loop the list and find the highest level
    foreach ($entries as $entry) {
        $groupname = $entry['cn'][0];
        if ($config['auth_ldap_groups'][$groupname]['level'] > $userlevel) {
            $userlevel = $config['auth_ldap_groups'][$groupname]['level'];
        }
    }

    auth_ldap_session_cache_set ('userlevel', $userlevel);
    return $userlevel;
}



function get_userid ($username) {
    global $config, $ds;

    $user_id = auth_ldap_session_cache_get ('userid');
    if (isset ($user_id)) {
        return $user_id;
    } else {
        $user_id = -1;
    }

    $filter  = '(' . $config['auth_ldap_prefix'] . $username . ')';
    $search  = ldap_search ($ds, trim ($config['auth_ldap_suffix'], ','), $filter);
    $entries = ldap_get_entries ($ds, $search);

    if ($entries['count']) {
        $user_id = $entries[0]['uidnumber'][0];
    }

    auth_ldap_session_cache_set ('userid', $user_id);
    return $user_id;
}


function deluser ($username) {
    // Not supported
    return 0;
}


function get_userlist () {
    global $config, $ds;
    $userlist = array ();

    $filter = '(' . $config['auth_ldap_prefix'] . '*)';

    $search  = ldap_search ($ds, trim ($config['auth_ldap_suffix'], ','), $filter);
    $entries = ldap_get_entries ($ds, $search);

    if ($entries['count']) {
        foreach ($entries as $entry) {
            $username    = $entry['uid'][0];
            $realname    = $entry['cn'][0];
            $user_id     = $entry['uidnumber'][0];
            $email       = $entry[$config['auth_ldap_emailattr']][0];
            $ldap_groups = get_group_list ();
            foreach ($ldap_groups as $ldap_group) {
                $ldap_comparison = ldap_compare(
                    $ds,
                    $ldap_group,
                    $config['auth_ldap_groupmemberattr'],
                    get_membername($username)
                );
                if (! isset ($config['auth_ldap_group']) || $ldap_comparison === true) {
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


function can_update_users () {
    // not supported
    return 0;
}


function get_user ($user_id) {
    // Not supported
    return 0;
}


function update_user ($user_id, $realname, $level, $can_modify_passwd, $email) {
    // Not supported
    return 0;
}


function get_membername ($username) {
    global $config, $ds;
    if ($config['auth_ldap_groupmembertype'] == 'fulldn') {
        $membername = $config['auth_ldap_prefix'] . $username . $config['auth_ldap_suffix'];
    }
    elseif ($config['auth_ldap_groupmembertype'] == 'puredn') {
        $filter  = '(' . $config['auth_ldap_attr']['uid'] . '=' . $username . ')';
        $search  = ldap_search($ds, $config['auth_ldap_groupbase'], $filter);
        $entries = ldap_get_entries($ds, $search);
        $membername = $entries[0]['dn'];
    }
    else {
        $membername = $username;
    }

    return $membername;
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

    $cache[$attr]['value'];
}


function auth_ldap_session_cache_set ($attr, $value) {
    $_SESSION['auth_ldap'][$attr]['value'] = $value;
    $_SESSION['auth_ldap'][$attr]['last_updated'] = time ();
}
