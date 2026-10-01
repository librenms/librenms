# Authentication Options

LibreNMS supports multiple authentication modules along with [Two Factor Auth](Two-Factor-Auth.md).
This document gives the configuration details of these modules.
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

⚠️ **A new authentication module makes the local users no
longer be available to log in.**

## Enable authentication module

To enable a particular authentication module you need to set this up
in `config.php`. Note: only ONE module can be
on. LibreNMS does not support more than one authentication mechanism at
the same time.

!!! setting "auth/general"
    ```bash
    lnms config:set auth_mechanism mysql
    ```

## User Roles

See [Authorization](Authorization.md) for more details on roles and permissions.

#### Built-in Roles

- **user**: you must assign the device permissions or the port
  permissions for users in this role.

- **global-read**: Read only Administrator.

- **admin**: This is a global read/write admin account.

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

This option is the LibreNMS default. Your configuration therefore
already holds these settings
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
this option ignores the certificate errors.

### Require actual membership of the configured groups

!!! setting "auth/ad"
    ```bash
    lnms config:set auth_ad_require_groupmembership 1
    ```

If you set `auth_ad_require_groupmembership` to 1, the
authenticated user has to be a member of the specific group.
Without this setting, all users can authenticate and get no default
role. You can also set `auth_ad_global_read` to 1. All users then
have the role 'global-read' and have read only access to all devices.

### Old account cleanup

The cleanup of the old accounts reads the authlog. Set the number of
days before the purge of an old account
AUTOMATICALLY by daily.sh.

Please ensure that you set the `authlog_purge` value to be
greater than `active_directory.users_purge` otherwise old
users stay.

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
    lnms config:set auth_ad_groups.ad-admingroup.roles ["admin"]
    lnms config:set auth_ad_groups.ad-usergroup.roles ["global-read"]
    ```

Replace `ad-admingroup` with your Active Directory admin-user group
and `ad-usergroup` with your standard user group. It is __highly
suggested__ to create a bind user, otherwise "remember me", alerting
users, and the API does not work.

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
    lnms config:set auth_ldap_groups.admin.roles ["admin"]
    lnms config:set auth_ldap_groups.pfy.roles ["global-read"]
    lnms config:set auth_ldap_groups.support.roles ["user"]
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
users, and the API does not work.

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
    lnms config:set auth_ldap_groups.{group}.roles ["admin"]
    lnms config:set auth_ldap_userdn true
    ```

Replace {id} with the unique ID provided by Jumpcloud.  Replace
{group} with the unique group name created in Jumpcloud.  This field
is case sensitive.

Note: If you have multiple user groups to define individual access
roles replace the `auth_ldap_groups` line with the following:

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_groups.{admin_group}.roles ["admin"]
    lnms config:set auth_ldap_groups.{global_readonly_group}.roles ["global-read"]
    ```

### SELinux configuration

On RHEL / CentOS / Fedora, in order for LibreNMS to reach LDAP, you need to allow LDAP requests in SELinux:
```
setsebool -P httpd_can_connect_ldap 1
```

## Radius Authentication

Note: LibreNMS creates a MySQL user for each user that logs in
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
- `librenms_role_normal` - Sets the normal user .
- `librenms_role_admin` - Sets the administrator role.
- `librenms_role_global-read` - Sets the global-read role

LibreNMS ignores any other string in `Filter-ID`. It then uses the
default role of your configuration.

!!! setting "auth/radius"
    ```bash
    lnms config:set radius.hostname localhost
    lnms config:set radius.port 1812
    lnms config:set radius.secret testing123
    lnms config:set radius.timeout 3
    lnms config:set radius.users_purge 14
    lnms config:set radius.default_roles '["Admin"]'
    ```

### Radius Huntgroup

Freeradius has a function called `Radius Huntgroup` which allows to send different attributes based on NAS.
Use this option when your environment already uses `Filter-ID` and you
also want radius with LibreNMS.

### Old account cleanup

The cleanup of the old accounts reads the authlog. Set the number of
days before the purge of an old account
AUTOMATICALLY by daily.sh.

Please ensure that you set the `authlog_purge` value to be
greater than `radius.users_purge` otherwise old users
stay.

## <a name="http-auth"> HTTP Authentication</a>


Config option: `http-auth`

LibreNMS expects an authenticated user from your web service. It then
assigns a local user
for that user which is done in one of two ways:

- A user exists in MySQL still where the usernames match up.

- A global guest user (which still needs to be added into MySQL:

!!! setting "auth/http"
    ```bash
    lnms config:set http_auth_guest guest
    ```

This setting assigns the guest user to all authenticated users.

### HTTP Authentication / AD Authorization

Config option: `ad-authorization`

This module is a combination of ___http-auth___ and ___active\_directory___

LibreNMS expects an authenticated user from your
webservice already, for example with Kerberos Authentication in Apache. It
uses Active Directory lookups to find and assign the roles of a user.
The roles come from the AD
group membership information as the ___active\_directory___ module
does.

The configuration is the same as for the ___active\_directory___ module
with two extra, optional options: auth_ad_binduser and
auth_ad_bindpassword. Set them to an AD user with read
capability in your AD domain. This user then does the
searches. Without these options, the module tries an
anonymous bind (which then of course must be allowed by your Active
Directory server(s)).

There is also one extra option for controlling user information caching: auth_ldap_cache_ttl.
This option allows to control how long user information (user_exists,
userid, roles) are cached within the PHP Session.
The default value is 300 seconds.
To disable this caching (highly discourage) set this option to 0.

!!! setting "auth/ad"
    ```bash
    lnms config:set auth_ad_binduser ad_binduser
    lnms config:set auth_ad_bindpassword ad_bindpassword
    lnms config:set auth_ldap_cache_ttl 300
    ```

### HTTP Authentication / LDAP Authorization

Config option: `ldap-authorization`

This module is a combination of ___http-auth___ and ___ldap___

LibreNMS expects an authenticated user from your
webservice already, for example with Kerberos Authentication in Apache. It
uses LDAP to find and assign the roles of a user. The roles come from
the LDAP group membership
information as the ___ldap___ module does.

The configuration is similar to the ___ldap___ module with one extra option: auth_ldap_cache_ttl.
This option allows to control how long user information (user_exists, userid, roles) are cached within the PHP Session.
The default value is 300 seconds.
To disabled this caching (highly discourage) set this option to 0.

#### Standard config

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_mechanism authorization
    lnms config:set auth_ldap_server ldap.example.com
    lnms config:set auth_ldap_suffix ,ou=People,dc=example,dc=com
    lnms config:set auth_ldap_groupbase ou=groups,dc=example,dc=com
    lnms config:set auth_ldap_groups.admin.roles ["admin"]
    lnms config:set auth_ldap_groups.pfy.roles ["global-read"]
    lnms config:set auth_ldap_groups.support.roles ["user"]
    ```

auth_ldap_server: set server(s), space separated. Prefix with ldaps:// for ssl
auth_ldap_suffix: appended to usernames
auth_ldap_groupbase: all groups must be inside this
auth_ldap_groups: set roles by group name

#### Additional options (usually not needed)

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_version 3
    lnms config:set auth_ldap_port 389
    lnms config:set auth_ldap_starttls true
    lnms config:set auth_ldap_prefix uid=
    lnms config:set auth_ldap_group cn=groupname,ou=groups,dc=example,dc=com
    lnms config:set auth_ldap_groupmemberattr memberUid
    lnms config:set auth_ldap_groupmembertype username
    lnms config:set auth_ldap_userlist_filter service=informatique
    lnms config:set auth_ldap_cache_ttl 300
    ```

auth_ldap_port: 389 or 636 for ssl
auth_ldap_prefix: prepended to usernames
auth_ldap_group: generic group with no roles
auth_ldap_groupmemberattr: attribute to use to see if a user is a member of a group
auth_ldap_groupmembertype: username type to find group members by, either username (default), fulldn or puredn
auth_ldap_userlist_filter: Replace 'service=informatique' by your ldap filter to limit the number of responses if you have an ldap directory with thousand of users

#### LDAP bind user (optional)

If your ldap server does not allow anonymous bind, it is highly
suggested to create a bind user, otherwise "remember me", alerting
users, and the API does not work.

!!! setting "auth/ldap"
    ```bash
    lnms config:set auth_ldap_binduser ldapbind
    lnms config:set auth_ldap_binddn CN=John.Smith,CN=Users,DC=MyDomain,DC=com
    lnms config:set auth_ldap_bindpassword password
    ```

auth_ldap_binddn: overrides auth_ldap_binduser with a dn

## View/embedded graphs without being logged into LibreNMS

!!! setting "webui/graph"
    ```bash
    lnms config:set allow_unauth_graphs_cidr '["127.0.0.1/32"]'
    lnms config:set allow_unauth_graphs true
    ```

## Single Sign-on

The single sign-on mechanism is used to integrate with third party
authentication providers that are managed outside of LibreNMS - such
as ADFS, Shibboleth, EZProxy, BeyondCorp, and others. A large number
of these methods use
[SAML](https://en.wikipedia.org/wiki/Security_Assertion_Markup_Language)
the module assumes SAML, and therefore
these instructions hold some SAML terms. They are
possible to use any software that works in a similar way.

The single sign-on module needs an
Identity Provider up and running, and know how to configure your
Relying Party to pass attributes to LibreNMS via header injection or
environment variables. Setting these up is outside of the scope of
this documentation.

As this module deals with authentication, it is extremely careful
about validating the configuration - if it finds that certain values
in the configuration are absent, it blocks the access and does not
try and guess.

### Basic Configuration

To get up and running, all you need to do is configure the following values:

```bash
lnms config:set auth_mechanism mysql
lnms config:set sso.mode env
lnms config:set sso.group_strategy static
lnms config:set sso.static_level 10
```

This, along with the defaults, sets up a basic Single Sign-on setup that:

- Reads values from environment variables
- It creates a user at their first login
- Automatically updates users with new values
- Gives everyone privilege level 10

This happens to mimic the behaviour of [http-auth](#http-auth), so if
you want this type of setup, it is usually better to
going and using that mechanism.

### Security

With a proxy, for example EZProxy, Azure AD Application
Proxy, NGINX, mod_proxy), you ___must___ have a method
in place to prevent headers being injected between the proxy and the
end user, and also prevent end users from contacting LibreNMS
directly.

This rule also applies to the user connections to the proxy. The
proxy ___must not___ be allowed to blindly pass through HTTP
headers. ___mod_security___ is the minimum. Add a
full [WAF](https://en.wikipedia.org/wiki/Web_application_firewall)
being strongly recommended. This advice applies to the IDP too.

The mechanism includes very basic protection, in the form of an IP
allow list holds the source addresses of your proxies:

```bash
lnms config:set sso.trusted_proxies '["127.0.0.1/8", "::1/128", "192.0.2.0", "2001:DB8::"]'
```

This configuration item holds an array with a list of IP
addresses or CIDR prefixes that are allowed to connect to LibreNMS and
supply environment variables or headers.

### Advanced Configuration Options

#### User Attribute

If your relying party does not store the username in
___REMOTE\_USER___, you can override this choice.

```bash
lnms config:set sso.trusted_proxies HTTP_UID
```

Note that the user lookup is a little special - normally headers are
prefixed with ___HTTP\____, however this is not the case for remote
user. It is a special case. With a different setting, you
need to figure out of the ___HTTP\____ prefix is required or not
yourself.

#### Automatic User Create/Update

These are enabled by default:

```bash
lnms config:set sso.create_users true
lnms config:set sso.update_users true
```

Without these settings, LibreNMS blocks the user logins almost
rejected unless an administrator has created the account in
advance. Note that in the case of SAML federations, unless release of
the IDP confirms the true identity of the user. The username
(probably ePTID) is not likely to be predicable.

### Personalisation

If the attributes are being populated, you can instruct the mechanism
to add additional information to the user's database entry:

```bash
lnms config:set sso.email_attr mail
lnms config:set sso.realname_attr displayName
lnms config:set sso.descr_attr unscoped-affiliation
```

#### Group Strategies

SSO currently uses legacy levels instead of roles. Here is a map:
1. user
5. global-read
10. admin
11. demo

##### Static

As used above, ___static___ gives every single user the same privilege
level. With a small team, or without an access
control, this is probably suitable.

##### Attribute

```bash
lnms config:set sso.group_strategy attribute
lnms config:set sso.level_attr entitlement
```

If your Relying Party is capable of calculating the necessary
privilege level, you can configure the module to read the privilege
number from an attribute. ___sso\_level\_attr___ holds
the name of the attribute that the Relying Party exposes to LibreNMS -
With a correct ___sso\_mode___, the mechanism finds
the value.

##### Group Map

This is the most flexible (and complex) way of assigning privileges.

```bash
lnms config:set sso.group_strategy map
lnms config:set sso.group_attr member
lnms config:set sso.group_level_map '{"librenms-admins": 10, "librenms-readers": 1, "librenms-billingcontacts": 5}'
lnms config:set sso.group_delimiter ';'
```

This mechanism expects to find a delimited list of groups within the
attribute of ___sso\_group\_attr___. This attribute is an
associative array of group name keys, with privilege levels as
values. The mechanism reads the list and finds the ___highest___
privilege level that the user is entitled to, and assign that value to
the user.

If there are no matches between the user's groups and the
___sso\_group\_level\_map___. The user then gets the privilege level
specified in the ___sso\_static\_level___ variable, with a default of 0 (no access).
This feature can be used to provide a default access level (such as read-only)
to all authenticated users.

This format can be specific to Shibboleth. Other relying party software
needs a change to the mechanism. For example,
___mod\_auth\_mellon___ creates pseudo arrays.

There is an optional value for sites with large numbers of groups:

```bash
lnms config:set sso.group_filter "/librenms-(.*)/i"
```

This filter causes the mechanism to only consider groups matching a regular expression.

#### Logout Behaviour

LibreNMS has no capability to log out a user authenticated via Single
Sign-On - that responsibility falls to the Relying Party.

If your Relying Party has a magic URL that needs to be called to end a
session, you can configure LibreNMS to direct the user to it:

```bash
# Example for Shibboleth
lnms config:set sso.auth_logout_handler '/Shibboleth.sso/Logout'

# Example for oauth2-proxy
lnms config:set sso.auth_logout_handler '/oauth2/sign_out'
```

This option functions independently of the Single Sign-on mechanism.

### Complete Configuration

This configuration works on my deployment with a Shibboleth relying
party, injecting environment variables, with the IDP supplying a list
of groups.

```php
lnms config:set auth_mechanism sso
lnms config:set sso.auth_logout_handler '/Shibboleth.sso/Logout'
lnms config:set sso.mode env
lnms config:set sso.create_users true
lnms config:set sso.update_users true
lnms config:set sso.realname_attr displayName
lnms config:set sso.email_attr mail
lnms config:set sso.group_strategy map
lnms config:set sso.group_attr member
lnms config:set sso.group_filter '/(librenms-.*)/i'
lnms config:set sso.group_delimiter ';'
lnms config:set sso.group_level_map '{"librenms-admins": 10, "librenms-readers": 1, "librenms-billingcontacts": 5}'
```
