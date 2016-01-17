# Oxidized integration

You can integrate LibreNMS with [Oxidized](https://github.com/ytti/oxidized-web) in two ways:

### Config viewing

This is a straight forward use of Oxidized, it relies on you having a working Oxidized setup which is already taking config snapshots for your devices. 
When you have that, you only need the following config to enable the display of device configs within the device page itself:

```php
$config['oxidized']['enabled']         = TRUE;
$config['oxidized']['url']             = 'http://127.0.0.1:8888';
```

We also support config versioning within Oxidized, this will allow you to see the old configs stored.

```php
$config['oxidized']['features']['versioning'] = true;
```

Oxidized supports various ways to utilise credentials to login to devices, you can specify global username/password within Oxidized, Group level username/password or per device.
We currently support sending groups back to Oxidized so that you can then define group credentials within Oxidized. To enable this support please switch on 'Enable the return of groups to Oxidized':

```php
$config['oxidized']['group_support'] = true;
```

You can set a default group that devices will fall back to with:

```php
$config['oxidized']['default_group'] = 'default';
```

### Feeding Oxidized

Oxidized has support for feeding devices into it via an API call, support for Oxidized has been added to the LibreNMS API. A sample config for Oxidized is provided below.

You will need to configure default credentials for your devices, LibreNMS doesn't provide login credentials at this time.

```bash
      source:
        default: http
        debug: false
        http:
          url: https://librenms/api/v0/oxidized
          scheme: https
          delimiter: !ruby/regexp /:/
          map:
            name: hostname
            model: os
            group: group
          headers:
            X-Auth-Token: '01582bf94c03104ecb7953dsadsadwed'
```

LibreNMS is able to reload the Oxidized list of nodes, each time a device is added to LibreNMS.
To do so, edit the option in Global Settings>External Settings>Oxidized Integration or add the following to your config.php.

```php
$config['oxidized']['reload_nodes'] = TRUE;

```

### Using Groups

To return a group to Oxidized you can do this by matching a regex for either hostname or location. The order is hostname is matched first, if nothing is found then location is attempted.
The first match found will be used. To match on the device hostnames that contain 'lon-sw' or if the location contains 'London' then you would place the following within config.php:

```php
$config['oxidized']['group']['hostname'][] = array('regex' => '/^lon-sw/', 'group' => 'london-switches');
$config['oxidized']['group']['location'][] = array('regex' => '/london/', 'group' => 'london-switches');
```

If you need to, you can specify credentials for groups by using the following in your Oxidized config:

```bash
groups:
  <groupname>:
    username: <user>
    password: <password>
```

### Miscellaneous

If you have devices which you do not wish to appear in Oxidized then you can edit those devices in Device -> Edit -> Misc and enable "Exclude from Oxidized?"

It's also possible to exclude certain device types and OS' from being output via the API. This is currently only possible via config.php:

```php
$config['oxidized']['ignore_types'] = array('server');
$config['oxidized']['ignore_os'] = array('linux');
```
