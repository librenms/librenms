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
    
?>