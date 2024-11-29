# Authentication Options

LibreNMS supports multiple authentication modules along with [Two Factor Auth](Two-Factor-Auth.md).
Here we will provide configuration details for these modules. Alternatively,
you can use [Socialite Providers](OAuth-SAML.md) which supports a wide variety
of social/OAuth/SAML authentication methods.

## Available authentication modules

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

## Enable authentication module

To enable a particular authentication module you need to set this up
in config.php. Please note that only ONE module can be
enabled. LibreNMS doesn't support multiple authentication mechanisms at
the same time.

!!! setting "auth/general"
    ```bash
    lnms config:set auth_mechanism mysql
    ```

## User levels and User account type

- 1: **Normal User**: You will need to assign device / port
  permissions for users at this level.

- 5: **Global Read**: Read only Administrator.

- 10: **Administrator**: This is a global read/write admin account.

- 11: **Demo Account**: Provides full read/write with certain
  restrictions (i.e can't delete devices).

**Note** Oxidized configs can often contain sensitive data. Because of
that only Administrator account type can see configs.

## Note for SELinux users

When using SELinux on the LibreNMS server, you need to allow Apache
(httpd) to connect LDAP/Active Directory server, this is disabled by
default. You can use SELinux Booleans to allow network access to LDAP
resources with this command:

```bash
setsebool -P httpd_can_connect_ldap=1
```

## Testing authentication

You can test authentication with this script:

```bash
./scripts/auth_test.php
```

Enable debug output to troubleshoot issues

## MySQL Authentication

!!! setting "auth/general"
    ```bash
    lnms config:set auth_mechanism mysql
    ```

This is default option with LibreNMS so you should have already have the following configuration setup
in your environment file (.env).

```dotenv
DB_HOST=HOSTNAME
DB_DATABASE=DBNAME
DB_USERNAME=DBUSER
DB_PASSWORD="DBPASS"
```

## Active Directory Authentication

!!! setting "auth/general"
    ```bash
    lnms config:set auth_mechanism active_directory
    ```

Install __php-ldap__  or __php8.1-ldap__, making sure to install the
same version as PHP.

If you have issues with secure LDAP try setting
!!! setting "auth/ad"
    ```bash
    lnms config:set auth_ad_check_certificates 0
    ```
this will ignore certificate errors.

### Require actual membership of the configured groups

!!! setting "auth/ad"
    ```bash
    lnms config:set auth_ad_require_groupmembership 1
    ```

If you set `auth_ad_require_groupmembership` to 1, the
authenticated user has to be a member of the specific group.
Otherwise all users can authenticate, and will be either level 0 or
you may set `auth_ad_global_read` to 1 and all users will
have read only access unless otherwise specified.

### Old account cleanup

Cleanup of old accounts is done by checking the authlog. You will need
to set the number of days when old accounts will be purged
AUTOMATICALLY by daily.sh.

Please ensure that you set the `authlog_purge` value to be
greater than `active_directory.users_purge` otherwise old
users won't be removed.

### Sample configuration

!!! setting "auth/general"
    ```bash
    lnms config:set auth_mechanism active_directory
    lnms config:set auth_ad_url ldaps://server.example.com
    lnms config:set auth_ad_domain
    lnms config:set auth_ad_base_dn dc=example,dc=com
    lnms config:set auth_ad_check_certificates true
    lnms config:set auth_ad_binduser examplebinduser
    lnms config:set auth_ad_bindpassword examplepassword
    lnms config:set auth_ad_timeout 5
    lnms config:set auth_ad_debug false
    lnms config:set active_directory.users_purge 30
    lnms config:set auth_ad_require_groupmembership true
    lnms config:set auth_ad_groups.ad-admingroup.level 10
    lnms config:set auth_ad_groups.ad-usergroup.level 5
    ```

Replace `ad-admingroup` with your Active Directory admin-user group
and `ad-usergroup` with your standard user group. It is __highly
suggested__ to create a bind user, otherwise "remember me", alerting
users, and the API will not work.

### Active Directory redundancy

You can set two Active Directory servers by editing the
`auth_ad_url` setting like this example:

!!! setting "auth/ad"
    ```bash
    lnms config:set auth_ad_url "ldaps://dc1.example.com ldaps://dc2.example.com"
    ```

### Active Directory LDAP filters

You can add an LDAP filter to be ANDed with the builtin user filter (`(sAMAccountName=$username)`).

The defaults are:

!!! setting "auth/ad"
    ```
    lnms config:set auth_ad_user_filter "(objectclass=user)"
    lnms config:set auth_ad_group_filter "(objectclass=group)"
    ```

This yields `(&(objectclass=user)(sAMAccountName=$username))` for the
user filter and `(&(objectclass=group)(sAMAccountName=$group))` for
the group filter.

### SELinux configuration

On RHEL / CentOS / Fedora, in order for LibreNMS to reach Active Directory, you need to allow LDAP requests in SELinux:
```
setsebool -P httpd_can_connect_ldap 1
```

## LDAP Authentication

!!! setting "auth/general"
    ```bash
    lnms config:set auth_mechanism ldap
    ```

Install __php_ldap__ or __php7.0-ldap__, making sure to install the
same version as PHP.

For the below, keep in mind the auth DN is composed using a string
join of `auth_ldap_prefix`, the username, and `auth_ldap_suffix`. This
means it needs to include `=` in the prefix and `,` in the suffix. So
lets say we have a prefix of `uid=`, the user `derp`, and the suffix of
`,ou=users,dc=foo,dc=bar`, then the result is
`uid=derp,ou=users,dc=foo,dc=bar`.

### Standard config

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_server ldap.example.com
    lnms config:set auth_ldap_suffix ',ou=People,dc=example,dc=com'
    lnms config:set auth_ldap_groupbase 'ou=groups,dc=example,dc=com'
    lnms config:set auth_ldap_groups.admin.level 10
    lnms config:set auth_ldap_groups.pfy.level 5
    lnms config:set auth_ldap_groups.support.level 1
    ```

### Additional options (usually not needed)

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_version 3
    lnms config:set auth_ldap_port 389
    lnms config:set auth_ldap_starttls true
    lnms config:set auth_ldap_prefix 'uid='
    lnms config:set auth_ldap_group 'cn=groupname,ou=groups,dc=example,dc=com'
    lnms config:set auth_ldap_groupmemberattr memberUid
    lnms config:set auth_ldap_groupmembertype username
    lnms config:set auth_ldap_uid_attribute uidnumber
    lnms config:set auth_ldap_timeout 5
    lnms config:set auth_ldap_emailattr mail
    lnms config:set auth_ldap_attr.uid uid
    lnms config:set auth_ldap_debug false
    lnms config:set auth_ldap_userdn true
    lnms config:set auth_ldap_userlist_filter service=informatique
    lnms config:set auth_ldap_wildcard_ou false
    lnms config:set auth_ldap_cacertfile /opt/librenms/ldap-ca-cert
    lnms config:set auth_ldap_ignorecert false
    ```

### LDAP bind user (optional)

If your ldap server does not allow anonymous bind, it is highly
suggested to create a bind user, otherwise "remember me", alerting
users, and the API will not work.

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_binduser ldapbind
    lnms config:set auth_ldap_binddn 'CN=John.Smith,CN=Users,DC=MyDomain,DC=com'
    lnms config:set auth_ldap_bindpassword password
    ```

### LDAP server redundancy

You can set two LDAP servers by editing the
`auth_ldap_server` like this example:

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_server ldaps://dir1.example.com ldaps://dir2.example.com
    ```

An example config setup for use with Jumpcloud LDAP as a service is:

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_mechanism ldap
    lnms config:set auth_ldap_version 3
    lnms config:set auth_ldap_server ldap.jumpcloud.com
    lnms config:set auth_ldap_port 389
    lnms config:set auth_ldap_prefix 'uid=';
    lnms config:set auth_ldap_suffix ',ou=Users,o={id},dc=jumpcloud,dc=com'
    lnms config:set auth_ldap_groupbase 'ou=Users,o={id},dc=jumpcloud,dc=com'
    lnms config:set auth_ldap_groupmemberattr member
    lnms config:set auth_ldap_groups.{group}.level 10
    lnms config:set auth_ldap_userdn true
    ```

Replace {id} with the unique ID provided by Jumpcloud.  Replace
{group} with the unique group name created in Jumpcloud.  This field
is case sensitive.

Note: If you have multiple user groups to define individual access
levels replace the `auth_ldap_groups` line with the following:

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_groups.{admin_group}.level 10]
    lnms config:set auth_ldap_groups.global_readonly_group.level 5
    ```

### SELinux configuration

On RHEL / CentOS / Fedora, in order for LibreNMS to reach LDAP, you need to allow LDAP requests in SELinux:
```
setsebool -P httpd_can_connect_ldap 1
```

## Radius Authentication

Please note that a mysql user is created for each user the logs in
successfully. Users are assigned the `user` role by default,
unless radius sends a reply attribute with a role. 

You can change the default role(s) by setting
!!! setting "auth/radius"
```bash
lnms config:set radius.default_roles '["csr"]'
```

The attribute `Filter-ID` is a standard Radius-Reply-Attribute (string) that
can be assigned a specially formatted string to assign a single role to the user. 

The string to send in `Filter-ID` reply attribute must start with `librenms_role_` followed by the role name.
For example to set the admin role send `librenms_role_admin`.

The following strings correspond to the built-in roles, but any defined role can be used:
- `librenms_role_normal` - Sets the normal user level.
- `librenms_role_admin` - Sets the administrator level.
- `librenms_role_global-read` - Sets the global read level

LibreNMS will ignore any other strings sent in `Filter-ID` and revert to default role that is set in your config.

```php
$config['radius']['hostname']      = 'localhost';
$config['radius']['port']          = '1812';
$config['radius']['secret']        = 'testing123';
$config['radius']['timeout']       = 3;
$config['radius']['users_purge']   = 14;  // Purge users who haven't logged in for 14 days.
$config['radius']['default_level'] = 1;  // Set the default user level when automatically creating a user.
```

### Radius Huntgroup

Freeradius has a function called `Radius Huntgroup` which allows to send different attributes based on NAS.
This may be utilized if you already use `Filter-ID` in your environment and also want to use radius with LibreNMS.

### Old account cleanup

Cleanup of old accounts is done by checking the authlog. You will need
to set the number of days when old accounts will be purged
AUTOMATICALLY by daily.sh.

Please ensure that you set the `$config['authlog_purge']` value to be
greater than `$config['radius']['users_purge']` otherwise old users
won't be removed.

## HTTP Authentication

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

### HTTP Authentication / AD Authorization

Config option: `ad-authorization`

This module is a combination of ___http-auth___ and ___active\_directory___

LibreNMS will expect the user to have authenticated via your
webservice already (e.g. using Kerberos Authentication in Apache) but
will use Active Directory lookups to determine and assign the
userlevel of a user. The userlevel will be calculated by using AD
group membership information as the ___active\_directory___ module
does.

The configuration is the same as for the ___active\_directory___ module
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

### HTTP Authentication / LDAP Authorization

Config option: `ldap-authorization`

This module is a combination of ___http-auth___ and ___ldap___

LibreNMS will expect the user to have authenticated via your
webservice already (e.g. using Kerberos Authentication in Apache) but
will use LDAP to determine and assign the userlevel of a user. The
userlevel will be calculated by using LDAP group membership
information as the ___ldap___ module does.

The configuration is similar to the ___ldap___ module with one extra option: auth_ldap_cache_ttl.
This option allows to control how long user information (user_exists, userid, userlevel) are cached within the PHP Session.
The default value is 300 seconds.
To disabled this caching (highly discourage) set this option to 0.

#### Standard config

```php
$config['auth_mechanism'] = 'ldap-authorization';
$config['auth_ldap_server'] = 'ldap.example.com';               // Set server(s), space separated. Prefix with ldaps:// for ssl
$config['auth_ldap_suffix'] = ',ou=People,dc=example,dc=com';   // appended to usernames
$config['auth_ldap_groupbase'] = 'ou=groups,dc=example,dc=com'; // all groups must be inside this
$config['auth_ldap_groups']['admin']['roles'] = ['admin'];             // set admin group to admin role
$config['auth_ldap_groups']['pfy']['roles'] = ['global-read'];                // set pfy group to global read only role
$config['auth_ldap_groups']['support']['roles'] = ['user'];            // set support group as a normal user
```

#### Additional options (usually not needed)

```php
$config['auth_ldap_version'] = 3; # v2 or v3
$config['auth_ldap_port'] = 389;                    // 389 or 636 for ssl
$config['auth_ldap_starttls'] = True;               // Enable TLS on port 389
$config['auth_ldap_prefix'] = 'uid=';               // prepended to usernames
$config['auth_ldap_group']  = 'cn=groupname,ou=groups,dc=example,dc=com'; // generic group with level 0
$config['auth_ldap_groupmemberattr'] = 'memberUid'; // attribute to use to see if a user is a member of a group
$config['auth_ldap_groupmembertype'] = 'username';  // username type to find group members by, either username (default), fulldn or puredn
$config['auth_ldap_emailattr'] = 'mail';            // attribute for email address
$config['auth_ldap_attr.uid'] = 'uid';              // attribute to check username against
$config['auth_ldap_userlist_filter'] = 'service=informatique'; // Replace 'service=informatique' by your ldap filter to limit the number of responses if you have an ldap directory with thousand of users
$config['auth_ldap_cache_ttl'] = 300;
```

#### LDAP bind user (optional)

If your ldap server does not allow anonymous bind, it is highly
suggested to create a bind user, otherwise "remember me", alerting
users, and the API will not work.

```php
$config['auth_ldap_binduser'] = 'ldapbind'; // will use auth_ldap_prefix and auth_ldap_suffix
#$config['auth_ldap_binddn'] = 'CN=John.Smith,CN=Users,DC=MyDomain,DC=com'; // overrides binduser
$config['auth_ldap_bindpassword'] = 'password';
```

## View/embedded graphs without being logged into LibreNMS

!!! setting "webui/graph"
    ```bash
    lnms config:set allow_unauth_graphs_cidr ['127.0.0.1/32']
    lnms config:set allow_unauth_graphs true
```

## Single Sign-on

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

### Basic Configuration

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
- Automatically updates users with new values
- Gives everyone privilege level 10

This happens to mimic the behaviour of [http-auth](#http-auth), so if
this is the kind of setup you want, you're probably better of just
going and using that mechanism.

### Security

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

### Advanced Configuration Options

#### User Attribute

If for some reason your relying party doesn't store the username in
___REMOTE\_USER___, you can override this choice.

```php
$config['sso']['user_attr'] = 'HTTP_UID';
```

Note that the user lookup is a little special - normally headers are
prefixed with ___HTTP\____, however this is not the case for remote
user - it's a special case. If you're using something different you
need to figure out of the ___HTTP\____ prefix is required or not
yourself.

#### Automatic User Create/Update

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

#### Group Strategies

##### Static

As used above, ___static___ gives every single user the same privilege
level. If you're working with a small team, or don't need access
control, this is probably suitable.

##### Attribute

```php
$config['sso']['group_strategy'] = "attribute";
$config['sso']['level_attr']     = "entitlement";
```

If your Relying Party is capable of calculating the necessary
privilege level, you can configure the module to read the privilege
number straight from an attribute. ___sso\_level\_attr___ should contain
the name of the attribute that the Relying Party exposes to LibreNMS -
as long as ___sso\_mode___ is correctly set, the mechanism should find
the value.

##### Group Map

This is the most flexible (and complex) way of assigning privileges.

```php
$config['sso']['group_strategy']  = "map";
$config['sso']['group_attr']      = "member";
$config['sso']['group_level_map'] = ['librenms-admins' => 10, 'librenms-readers' => 1, 'librenms-billingcontacts' => 5];
$config['sso']['group_delimiter'] = ';';
```

This mechanism expects to find a delimited list of groups within the
attribute that ___sso\_group\_attr___ points to. This should be an
associative array of group name keys, with privilege levels as
values. The mechanism will scan the list and find the ___highest___
privilege level that the user is entitled to, and assign that value to
the user.

If there are no matches between the user's groups and the
___sso\_group\_level\_map___, the user will be assigned the privilege level
specified in the ___sso\_static\_level___ variable, with a default of 0 (no access).
This feature can be used to provide a default access level (such as read-only)
to all authenticated users.

Additionally, this format may be specific to Shibboleth; other relying party
software may need changes to the mechanism (e.g. ___mod\_auth\_mellon___
may create pseudo arrays).

There is an optional value for sites with large numbers of groups:

```php
$config['sso']['group_filter']  = "/librenms-(.*)/i";
```

This filter causes the mechanism to only consider groups matching a regular expression.

#### Logout Behaviour

LibreNMS has no capability to log out a user authenticated via Single
Sign-On - that responsibility falls to the Relying Party.

If your Relying Party has a magic URL that needs to be called to end a
session, you can configure LibreNMS to direct the user to it:

```php
# Example for Shibboleth
$config['auth_logout_handler'] = '/Shibboleth.sso/Logout';

# Example for oauth2-proxy
$config['auth_logout_handler'] = '/oauth2/sign_out';
```

This option functions independently of the Single Sign-on mechanism.

### Complete Configuration

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
