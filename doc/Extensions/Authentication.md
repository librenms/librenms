source: Extensions/Authentication.md
path: blob/master/doc/

# Authentication modules

LibreNMS supports multiple authentication modules along with [Two Factor Auth](http://docs.librenms.org/Extensions/Two-Factor-Auth/).
Here we will provide configuration details for these modules.

# Available authentication modules

- MySQL: [mysql](#mysql-authentication)

- Active Directory: [active_directory](#active-directory-authentication)

- LDAP: [ldap](#ldap-authentication)

- Radius: [radius](#radius-authentication)

- HTTP Auth: [http-auth](#http-authentication),
  [ad_authorization](#http-authentication-ad-authorization),
  [ldap_authorization](#http-authentication-ldap-authorization)

- Single Sign-on: [sso](#single-sign-on)

⚠️ **When enabling a new authentication module, the local users will no
longer be available to log in.**

# Enable authentication module

To enable a particular authentication module you need to set this up
in config.php. Please note that only ONE module can be
enabled. LibreNMS doesn't support multiple authentication mechanism at
the same time.

```php
$config['auth_mechanism'] = "mysql";
```

# User levels and User account type

- 1: **Normal User**: You will need to assign device / port
  permissions for users at this level.

- 5: **Global Read**: Read only Administrator.

- 10: **Administrator**: This is a global read/write admin account.

- 11: **Demo Account**: Provides full read/write with certain
  restrictions (i.e can't delete devices).

**Note** Oxidized configs can often contain sensitive data. Because of
that only Administrator account type can see configs.

# Note for SELinux users

When using SELinux on the LibreNMS server, you need to allow Apache
(httpd) to connect LDAP/Active Directory server, this is disabled by
default. You can use SELinux Booleans to allow network access to LDAP
resources with this command:

```shell
setsebool -P httpd_can_connect_ldap=1
```

# Testing authentication

You can test authentication with this script:

```shell
./scripts/auth_test.php
```

Enable debug output to troubleshoot issues

# MySQL Authentication

Config option: `mysql`

This is default option with LibreNMS so you should have already have
the configuration setup.

```php
$config['db_host'] = "HOSTNAME";
$config['db_user'] = "DBUSER";
$config['db_pass'] = "DBPASS";
$config['db_name'] = "DBNAME";
```

# Active Directory Authentication

Config option: `active_directory`

Install __php_ldap__  or __php7.0-ldap__, making sure to install the
same version as PHP.

If you have issues with secure LDAP try setting
`$config['auth_ad_check_certificates']` to `0`, this will ignore
certificate errors.

## Require actual membership of the configured groups

If you set `$config['auth_ad_require_groupmembership']` to 1, the
authenticated user has to be a member of the specific group.
Otherwise all users can authenticate, and will be either level 0 or
you may set `$config['auth_ad_global_read']` to 1 and all users will
have read only access unless otherwise specified.

## Old account cleanup

Cleanup of old accounts is done by checking the authlog. You will need
to set the number of days when old accounts will be purged
AUTOMATICALLY by daily.sh.

Please ensure that you set the $config['authlog_purge'] value to be
greater than $config['active_directory']['users_purge'] otherwise old
users won't be removed.

## Sample configuration

```php
$config['auth_mechanism'] = 'active_directory';
$config['auth_ad_url'] = 'ldaps://server.example.com';    // Set server(s), space separated. Prefix with ldaps:// for ssl
$config['auth_ad_domain'] = 'example.com';
$config['auth_ad_base_dn'] = 'dc=example,dc=com';         // groups and users must be under this dn
$config['auth_ad_check_certificates'] = true;             // require a valid ssl certificate
$config['auth_ad_binduser'] = 'examplebinduser';          // bind user (non-admin)
$config['auth_ad_bindpassword'] = 'examplepassword';      // bind password
$config['auth_ad_timeout'] = 5;                           // time to wait before giving up (or trying the next server)
$config['auth_ad_debug'] = false;                         // enable for verbose debug messages
$config['active_directory']['users_purge'] = 30;          // purge users who haven't logged in for 30 days.
$config['auth_ad_require_groupmembership'] = true;        // false: allow all users to auth level 0
$config['auth_ad_groups']['ad-admingroup']['level'] = 10; // set the "AD AdminGroup" group to admin level
$config['auth_ad_groups']['ad-usergroup']['level'] = 5;   // set the "AD UserGroup" group to global read only level

```

Replace `ad-admingroup` with your Active Directory admin-user group
and `ad-usergroup` with your standard user group. It is __highly
suggested__ to create a bind user, otherwise "remember me", alerting
users, and the API will not work.

## Active Directory redundancy

You can set two Active Directory servers by editing the
`$config['auth_ad_url']` like this example:

```
$config['auth_ad_url'] = "ldaps://dc1.example.com ldaps://dc2.example.com";
```

## Active Directory LDAP filters

You can add an LDAP filter to be ANDed with the builtin user filter (`(sAMAccountName=$username)`).

The defaults are:

```
$config['auth_ad_user_filter'] = "(objectclass=user)";
$config['auth_ad_group_filter'] = "(objectclass=group)";
```

This yields `(&(objectclass=user)(sAMAccountName=$username))` for the
user filter and `(&(objectclass=group)(sAMAccountName=$group))` for
the group filter.

# LDAP Authentication

Config option: `ldap`

Install __php_ldap__ or __php7.0-ldap__, making sure to install the
same version as PHP.

## Standard config

```php
$config['auth_mechanism'] = 'ldap';
$config['auth_ldap_server'] = 'ldap.example.com';               // Set server(s), space separated. Prefix with ldaps:// for ssl
$config['auth_ldap_suffix'] = ',ou=People,dc=example,dc=com';   // appended to usernames
$config['auth_ldap_groupbase'] = 'ou=groups,dc=example,dc=com'; // all groups must be inside this
$config['auth_ldap_groups']['admin']['level'] = 10;             // set admin group to admin level
$config['auth_ldap_groups']['pfy']['level'] = 5;                // set pfy group to global read only level
$config['auth_ldap_groups']['support']['level'] = 1;            // set support group as a normal user
```

## Additional options (usually not needed)

```php
$config['auth_ldap_version'] = 3; # v2 or v3
$config['auth_ldap_port'] = 389;                    // 389 or 636 for ssl
$config['auth_ldap_starttls'] = True;               // Enable TLS on port 389
$config['auth_ldap_prefix'] = 'uid=';               // prepended to usernames
$config['auth_ldap_group']  = 'cn=groupname,ou=groups,dc=example,dc=com'; // generic group with level 0
$config['auth_ldap_groupmemberattr'] = 'memberUid'; // attribute to use to see if a user is a member of a group
$config['auth_ldap_uid_attribute'] = 'uidnumber';   // attribute for unique id
$config['auth_ldap_debug'] = false;                 // enable for verbose debug messages
$config['auth_ldap_userdn'] = true;                 // Uses a users full DN as the value of the member attribute in a group instead of member: username. (it’s member: uid=username,ou=groups,dc=domain,dc=com)
$config['auth_ldap_userlist_filter'] = 'service=informatique'; // Replace 'service=informatique' by your ldap filter to limit the number of responses if you have an ldap directory with thousand of users
```

## LDAP bind user (optional)

If your ldap server does not allow anonymous bind, it is highly
suggested to create a bind user, otherwise "remember me", alerting
users, and the API will not work.

```php
$config['auth_ldap_binduser'] = 'ldapbind'; // will use auth_ldap_prefix and auth_ldap_suffix
#$config['auth_ldap_binddn'] = 'CN=John.Smith,CN=Users,DC=MyDomain,DC=com'; // overrides binduser
$config['auth_ldap_bindpassword'] = 'password';
```

## LDAP server redundancy

You can set two LDAP servers by editing the
`$config['auth_ldap_server']` like this example:

```
$config['auth_ldap_server'] = "ldaps://dir1.example.com ldaps://dir2.example.com";
```

An example config setup for use with Jumpcloud LDAP as a service is:

```php
$config['auth_mechanism'] = "ldap";
$config['auth_ldap_version'] = 3;
$config['auth_ldap_server'] = "ldap.jumpcloud.com"; #Set to ldaps://ldap.jumpcloud.com to enable LDAPS
$config['auth_ldap_port'] = 389; #Set to 636 if using LDAPS
$config['auth_ldap_prefix'] = "uid=";
$config['auth_ldap_suffix'] = ",ou=Users,o={id},dc=jumpcloud,dc=com";
$config['auth_ldap_groupbase'] = "ou=Users,o={id},dc=jumpcloud,dc=com";
$config['auth_ldap_groupmemberattr'] = "member";
$config['auth_ldap_groups'] = ['{group}' => ['level' => 10],];
$config['auth_ldap_userdn'] = true;
```

Replace {id} with the unique ID provided by Jumpcloud.  Replace
{group} with the unique group name created in Jumpcloud.  This field
is case sensitive.

Note: If you have multiple user groups to define individual access
levels replace the `$config['auth_ldap_groups']` line with the
following:

```php
$config['auth_ldap_groups'] = [
    '{admin_group}' => ['level' => 10],
    '{global_readonly_group}' => ['level' => 5],
];
```

# Radius Authentication

Please note that a mysql user is created for each user the logs in
successfully. User level 1 is assigned to those accounts so you will
then need to assign the relevant permissions unless you set
`$config['radius']['userlevel']` to be something other than 1.

```php
$config['radius']['hostname']   = 'localhost';
$config['radius']['port']       = '1812';
$config['radius']['secret']     = 'testing123';
$config['radius']['timeout']    = 3;
$config['radius']['users_purge'] = 14;//Purge users who haven't logged in for 14 days.
$config['radius']['default_level'] = 1;//Set the default user level when automatically creating a user.
```

## Old account cleanup

Cleanup of old accounts is done by checking the authlog. You will need
to set the number of days when old accounts will be purged
AUTOMATICALLY by daily.sh.

Please ensure that you set the $config['authlog_purge'] value to be
greater than $config['radius']['users_purge'] otherwise old users
won't be removed.

# HTTP Authentication

Config option: `http-auth`

LibreNMS will expect the user to have authenticated via your
webservice already. At this stage it will need to assign a userlevel
for that user which is done in one of two ways:

- A user exists in MySQL still where the usernames match up.

- A global guest user (which still needs to be added into MySQL:

```php
$config['http_auth_guest'] = "guest";
```

This will then assign the userlevel for guest to all authenticated users.

## HTTP Authentication / AD Authorization

Config option: `ad-authorization`

This module is a combination of ___http-auth___ and ___active_directory___

LibreNMS will expect the user to have authenticated via your
webservice already (e.g. using Kerberos Authentication in Apache) but
will use Active Directory lookups to determine and assign the
userlevel of a user. The userlevel will be calculated by using AD
group membership information as the ___active_directory___ module
does.

The configuration is the same as for the ___active_directory___ module
with two extra, optional options: auth_ad_binduser and
auth_ad_bindpassword. These should be set to a AD user with read
capabilities in your AD Domain in order to be able to perform
searches. If these options are omitted, the module will attempt an
anonymous bind (which then of course must be allowed by your Active
Directory server(s)).

There is also one extra option for controlling user information caching: auth_ldap_cache_ttl.
This option allows to control how long user information (user_exists,
userid, userlevel) are cached within the PHP Session.
The default value is 300 seconds.
To disable this caching (highly discourage) set this option to 0.

```php
$config['auth_ad_binduser']     = "ad_binduser";
$config['auth_ad_bindpassword'] = "ad_bindpassword";
$config['auth_ldap_cache_ttl']  = 300;
```

## HTTP Authentication / LDAP Authorization

Config option: `ldap-authorization`

This module is a combination of ___http-auth___ and ___ldap___

LibreNMS will expect the user to have authenticated via your
webservice already (e.g. using Kerberos Authentication in Apache) but
will use LDAP to determine and assign the userlevel of a user. The
userlevel will be calculated by using LDAP group membership
information as the ___ldap___ module does.

The configuration is the same as for the ___ldap___ module with one extra option: auth_ldap_cache_ttl.
This option allows to control how long user information (user_exists, userid, userlevel) are cached within the PHP Session.
The default value is 300 seconds.
To disabled this caching (highly discourage) set this option to 0.

```php
$config['auth_ldap_cache_ttl'] = 300;
```

# View/embedded graphs without being logged into LibreNMS

```php
$config['allow_unauth_graphs_cidr'] = array('127.0.0.1/32');
$config['allow_unauth_graphs'] = true;
```

# Single Sign-on

The single sign-on mechanism is used to integrate with third party
authentication providers that are managed outside of LibreNMS - such
as ADFS, Shibboleth, EZProxy, BeyondCorp, and others. A large number
of these methods use
[SAML](https://en.wikipedia.org/wiki/Security_Assertion_Markup_Language)
the module has been written assuming the use of SAML, and therefore
these instructions contain some SAML terminology, but it should be
possible to use any software that works in a similar way.

In order to make use of the single sign-on module, you need to have an
Identity Provider up and running, and know how to configure your
Relying Party to pass attributes to LibreNMS via header injection or
environment variables. Setting these up is outside of the scope of
this documentation.

As this module deals with authentication, it is extremely careful
about validating the configuration - if it finds that certain values
in the configuration are not set, it will reject access rather than
try and guess.

## Basic Configuration

To get up and running, all you need to do is configure the following values:

```php
$config['auth_mechanism']        = "sso";
$config['sso']['mode']           = "env";
$config['sso']['group_strategy'] = "static";
$config['sso']['static_level']   = 10;
```

This, along with the defaults, sets up a basic Single Sign-on setup that:

- Reads values from environment variables
- Automatically creates users when they're first seen
- Authomatically updates users with new values
- Gives everyone privilege level 10

This happens to mimic the behaviour of [http-auth](#http-auth), so if
this is the kind of setup you want, you're probably better of just
going and using that mechanism.

## Security

If there is a proxy involved (e.g. EZProxy, Azure AD Application
Proxy, NGINX, mod_proxy) it's ___essential___ that you have some means
in place to prevent headers being injected between the proxy and the
end user, and also prevent end users from contacting LibreNMS
directly.

This should also apply to user connections to the proxy itself - the
proxy ___must not___ be allowed to blindly pass through HTTP
headers. ___mod_security___ should be considered a minimum, with a
full [WAF](https://en.wikipedia.org/wiki/Web_application_firewall)
being strongly recommended. This advice applies to the IDP too.

The mechanism includes very basic protection, in the form of an IP
whitelist with should contain the source addresses of your proxies:

```php
$config['sso']['trusted_proxies'] = ['127.0.0.1/8', '::1/128', '192.0.2.0', '2001:DB8::'];
```

This configuration item should contain an array with a list of IP
addresses or CIDR prefixes that are allowed to connect to LibreNMS and
supply environment variables or headers.

## Advanced Configuration Options

### User Attribute

If for some reason your relying party doesn't store the username in
___REMOTE_USER___, you can override this choice.

```php
$config['sso']['user_attr'] = 'HTTP_UID';
```

Note that the user lookup is a little special - normally headers are
prefixed with ___HTTP\____, however this is not the case for remote
user - it's a special case. If you're using something different you
need to figure out of the ___HTTP\____ prefix is required or not
yourself.

### Automatic User Create/Update

These are enabled by default:

```php
$config['sso']['create_users'] = true;
$config['sso']['update_users'] = true;
```

If these are not enabled, user logins will be (somewhat silently)
rejected unless an administrator has created the account in
advance. Note that in the case of SAML federations, unless release of
the users true identity has been negotiated with the IDP, the username
(probably ePTID) is not likely to be predicable.

### Personalisation

If the attributes are being populated, you can instruct the mechanism
to add additional information to the user's database entry:

```php
$config['sso']['email_attr']    = "mail";
$config['sso']['realname_attr'] = "displayName";
$config['sso']['descr_attr']    = "unscoped-affiliation
```

### Group Strategies

#### Static

As used above, ___static___ gives every single user the same privilege
level. If you're working with a small team, or don't need access
control, this is probably suitable.

#### Attribute

```php
$config['sso']['group_strategy'] = "attribute";
$config['sso']['level_attr']     = "entitlement";
```

If your Relying Party is capable of calculating the necessary
privilege level, you can configure the module to read the privilege
number straight from an attribute. ___sso_level_attr___ should contain
the name of the attribute that the Relying Party exposes to LibreNMS -
as long as ___sso_mode___ is correctly set, the mechanism should find
the value.

#### Group Map

This is the most flexible (and complex) way of assigning privileges.

```php
$config['sso']['group_strategy']  = "map";
$config['sso']['group_attr']      = "member";
$config['sso']['group_level_map'] = ['librenms-admins' => 10, 'librenms-readers' => 1, 'librenms-billingcontacts' => 5];
$config['sso']['group_delimiter'] = ';';
```

The mechanism expects to find a delimited list of groups within the
attribute that ___sso_group_attr___ points to. This should be an
associative array of group name keys, with  privilege levels as
values. The mechanism will scan the list and find the ___highest___
privilege level that the user is entitled to, and assign that value to
the user.

This format may be specific to Shibboleth; other relying party
software may need changes to the mechanism (e.g. ___mod_auth_mellon___
may create pseudo arrays).

There is an optional value for sites with large numbers of groups:

```php
$config['sso']['group_filter']  = "/librenms-(.*)/i";
```

This filter causes the mechanism to only consider groups matching a regular expression.

### Logout Behaviour

LibreNMS has no capability to log out a user authenticated via Single
Sign-On - that responsability falls to the Relying Party.

If your Relying Party has a magic URL that needs to be called to end a
session, you can configure LibreNMS to direct the user to it:

```php
$config['post_logout_action'] = '/Shibboleth.sso/Logout';
```

This option functions independantly of the Single Sign-on mechanism.

## Complete Configuration

This configuration works on my deployment with a Shibboleth relying
party, injecting environment variables, with the IDP supplying a list
of groups.

```php
$config['auth_mechanism'] = 'sso';
$config['auth_logout_handler'] = '/Shibboleth.sso/Logout';
$config['sso']['mode'] = 'env';
$config['sso']['create_users'] = true;
$config['sso']['update_users'] = true;
$config['sso']['realname_attr'] = 'displayName';
$config['sso']['email_attr'] = 'mail';
$config['sso']['group_strategy'] = 'map';
$config['sso']['group_attr'] = 'member';
$config['sso']['group_filter'] = '/(librenms-.*)/i';
$config['sso']['group_delimiter'] = ';';
$config['sso']['group_level_map'] = ['librenms-demo' => 11, 'librenms-globaladmin' => 10, 'librenms-globalread' => 5, 'librenms-lowpriv'=> 1];
```
