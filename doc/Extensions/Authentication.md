# Authentication modules

LibreNMS supports multiple authentication modules along with [Two Factor Auth](http://docs.librenms.org/Extensions/Two-Factor-Auth/). 
Here we will provide configuration details for these modules.

#### Available authentication modules

- MySQL: mysql

- LDAP: ldap

- Active Directory: active_directory

- HTTP Auth: http-auth

#### User levels

- 1: Normal User. You will need to assign device / port permissions for users at this level.

- 5: Global Read.

- 10: This is a global read/write admin account

- 11: Demo Account. Provides full read/write with certain restrictions (i.e can't delete devices).

#### Enable authentication module

To enable a particular authentication module you need to set this up in config.php.

```php
$config['auth_mechanism'] = "mysql";
```

#### MySQL Authentication

Config option: `mysql`

This is default option with LibreNMS so you should have already got the configuration setup.

```php
$config['db_host'] = "HOSTNAME";
$config['db_user'] = "DBUSER";
$config['db_pass'] = "DBPASS";
$config['db_name'] = "DBNAME";
```

#### HTTP Authentication

Config option: `http-auth`

LibreNMS will expect the user to have authenticated via your webservice already. At this stage it will need to assign a 
userlevel for that user which is done in one of two ways:
 
- A user exists in MySQL still where the usernames match up.

- A global guest user (which still needs to be added into MySQL:
```php
$config['http_auth_guest'] = "guest";
```
This will then assign the userlevel for guest to all authenticated users.

#### LDAP Authentication

Config option: `ldap`

This one is a little more complicated :)

First of all, install ___php-ldap___ forCentOS/RHEL or ___php5-ldap___ for Ubuntu/Debian.

```php
$config['auth_ldap_version'] = 3; # v2 or v3
$config['auth_ldap_server'] = "ldap.example.com";
$config['auth_ldap_port']   = 389;
$config['auth_ldap_prefix'] = "uid=";
$config['auth_ldap_suffix'] = ",ou=People,dc=example,dc=com";
$config['auth_ldap_group']  = "cn=groupname,ou=groups,dc=example,dc=com";

$config['auth_ldap_groupbase'] = "ou=group,dc=example,dc=com";
$config['auth_ldap_groups']['admin']['level'] = 10;
$config['auth_ldap_groups']['pfy']['level'] = 7;
$config['auth_ldap_groups']['support']['level'] = 1;
$config['auth_ldap_groupmemberattr'] = "memberUid";
```

Typically auth_ldap_suffix, auth_ldap_group, auth_ldap_groupbase, auth_ldap_groups are what's required to be configured.

An example config setup for use with Jumpcloud LDAP as a service is:

```php
$config['auth_mechanism'] = "ldap"; # default, other options: ldap, http-auth
unset($config['auth_ldap_group']);
unset($config['auth_ldap_groups']);
$config['auth_ldap_groups']['librenms']['level'] = 10;
$config['auth_ldap_version'] = 3; # v2 or v3
$config['auth_ldap_server'] = "ldap.jumpcloud.com";
$config['auth_ldap_port'] = 389;
$config['auth_ldap_prefix'] = "uid=";
$config['auth_ldap_suffix'] = ",ou=Users,o={id},dc=jumpcloud,dc=com";
$config['auth_ldap_groupbase'] = "cn=librenms,ou=Users,o={id},dc=jumpcloud,dc=com";
$config['auth_ldap_groupmemberattr'] = "memberUid";
```

Replace {id} with the unique ID provided by Jumpcloud.

#### Active Directory Authentication

Config option: `active_directory`

This is similar to LDAP Authentication. Install __php_ldap__ for CentOS/RHEL or __php5-ldap__ for Debian/Ubuntu.

If you have issues with secure LDAP try setting `$config['auth_ad_dont_check_certificates']` to `1`.

##### Require actual membership of the configured groups

If you set ```$config['auth_ad_require_groupmembership']``` to 1, the authenticated user has to be a member of the specific group. Otherwise all users can authenticate, but are limited to user level 0 and only have access to shared dashboards. 

##### Sample configuration

```
$config['auth_ad_url'] = "ldaps://your-domain.controll.er";
$config['auth_ad_dont_check_certificates'] = 1; // or 0
$config['auth_ad_domain'] = "your-domain.com";
$config['auth_ad_base_dn'] = "dc=your-domain,dc=com";
$config['auth_ad_groups']['admin']['level'] = 10;
$config['auth_ad_groups']['pfy']['level'] = 7;
$config['auth_ad_require_groupmembership'] = 0;
```
