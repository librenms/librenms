<?php

function authenticate($username,$password)
{
  global $config;

  $ds=@ldap_connect($config['auth_ldap_server'],$config['auth_ldap_port']);
  if ($ds)
  {
    if ($config['auth_ldap_version'])
    {
      ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $config['auth_ldap_version']);
    }
    if (ldap_bind($ds, $config['auth_ldap_prefix'] . $username . $config['auth_ldap_suffix'], $password))
    {
      if (!$config['auth_ldap_group'])
      {
        return 1;
      }
      else
      {
        if (ldap_compare($ds,$config['auth_ldap_group'],'memberUid',$username))
        {
          return 1;
        }
      }
    }
    else
    {
      echo ldap_error($ds);
    }
  }
  else
  {
    # FIXME return a warning that LDAP couldn't connect?
  }

  return 0;
}

function passwordscanchange()
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

function adduser($username, $password, $level, $email = "", $realname = "")
{
  # Not supported
  return 0;
}
  
function user_exists($username)
{
  return 0; # FIXME to be implemented
}
  
function get_userlevel($username)
{
  # FIXME should come from LDAP
  $sql = "SELECT level FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_array(mysql_query($sql));
  return $row['level'];
}

function get_userid($username)
{
  # FIXME should come from LDAP
  $sql = "SELECT user_id FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_array(mysql_query($sql));
  return $row['user_id'];
}

?>