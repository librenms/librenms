<?php
$ds = @ldap_connect($config['auth_ldap_server'],$config['auth_ldap_port']);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $config['auth_ldap_version']);
$base_dn = "uid=".$config['auth_ldap_bind_username'].",cn=admins,cn=zimbra";
if (!ldap_bind($ds,$base_dn,$config['auth_ldap_bind_password']))
{
 echo("<h2>Fatal error: Ldap connect error:" . ldap_error($ds) . "</h2>");
 exit();
}
if ($config['auth_ldap_starttls'] && ($config['auth_ldap_starttls'] == 'optional' || $config['auth_ldap_starttls'] == 'require'))
{
  $tls = ldap_start_tls($ds);
  if ($config['auth_ldap_starttls'] == 'require' && $tls == FALSE)
  {
    echo("<h2>Fatal error: LDAP TLS required but not successfully negotiated:" . ldap_error($ds) . "</h2>");
    exit;
  }
}
function authenticate($username,$password){
  global $config, $ds;
  if ($username && $ds && $password){
    if(user_exists($username)){
      $uid = get_userid($username);
      $user_dn = "uid=$uid,".$config['auth_ldap_base_dn'];
      if (ldap_bind($ds,$user_dn,$password))
      {
        return 1;
      }
    }
  }
  return 0;
}

function reauthenticate($sess_id,$token)
{
  return 0;
}

function passwordscanchange($username = "")
{
  return 0;
}

function changepassword($username,$newpassword)
{
# Not supported (for now)
}

function auth_usermanagement()
{
  return 0;
}

function adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd = '1')
{
# Not supported
  return 0;
}

function user_exists($username)
{
  global $config, $ds;
  $filter = "(".$config['auth_ldap_user_field']."=". $username . ")";
  $search = ldap_search($ds, trim($config['auth_ldap_base_dn'],','), $filter,array('uid'));
  $entries = ldap_get_entries($ds, $search);
  if ($entries['count'])
  {
    return 1;
  }

  return 0;
}

function get_userlevel($username)
{
  global $config, $ds;
  $filter = "(".$config['auth_ldap_user_field']."=noc@opcaonet.com.br)";
  echo $filter;
  $search = ldap_read($ds, trim($config['auth_ldap_base_dn'],','), $filter);
  $entries = ldap_get_entries($ds, $search);
  var_dump($entries);


  global $config, $ds;
  $userlevel = 0;
  $filter = "(".$config['auth_ldap_group_field'] ."=". $username . ")";
  $filter = "(".$config['auth_ldap_user_field']."=". $username . ")";
  $search = ldap_search($ds, trim($config['auth_ldap_base_dn'],',',$filter));
  $entries = ldap_get_entries($ds, $search);
  var_dump($entries);
  exit();
# Loop the list and find the highest level
  foreach ($entries as $entry)
  {
    $groupname = $entry['uid'][0];
    if ($config['auth_ldap_groups'][$groupname]['level'] > $userlevel)
    {
      $userlevel = $config['auth_ldap_groups'][$groupname]['level'];
    }
  }

  return $userlevel;
}

function get_userid($username)
{
  global $config, $ds;

  $filter = "(".$config['auth_ldap_user_field']."=". $username . ")";
  $search = ldap_search($ds, trim($config['auth_ldap_base_dn'],','), $filter,array('uid'));
  $entries = ldap_get_entries($ds, $search);

  if ($entries['count'])
  {
    return $entries[0]['uid'][0];
  }

  return -1;
}

function deluser($username)
{
# Not supported
  return 0;
}

function get_userlist()
{
  global $config, $ds;
  $userlist = array();

  $filter = '(' . $config['auth_ldap_prefix'] . '*)';

  $search = ldap_search($ds, trim($config['auth_ldap_suffix'],','), $filter);
  $entries = ldap_get_entries($ds, $search);

  if ($entries['count'])
  {
    foreach ($entries as $entry)
    {
      $username = $entry['uid'][0];
      $realname = $entry['cn'][0];
      $user_id  = $entry['uidnumber'][0];

      if (!isset($config['auth_ldap_group']) || ldap_compare($ds,$config['auth_ldap_group'],'memberUid',$username))
      {
        $userlist[] = array('username' => $username, 'realname' => $realname, 'user_id' => $user_id);
      }
    }
  }

  return $userlist;
}

function can_update_users()
{
# not supported so return 0
  return 0;
}

function get_user($user_id)
{
# not supported
  return 0;
}

function update_user($user_id,$realname,$level,$can_modify_passwd,$email)
{
# not supported
  return 0;
}

?>
