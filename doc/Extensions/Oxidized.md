source: Extensions/Oxidized.md
path: blob/master/doc/

# Oxidized intro

Integrating LibreNMS with
[Oxidized](https://github.com/ytti/oxidized-web) brings the following
benefits:

- Config viewing: Current, History, and Diffs all under the Configs tab of each device
- Automatic addition of devices to Oxidized: Including filtering and
  grouping to ease credential management
- Configuration searching (Requires oxidized-web 0.8.0 or newer)

First you will need to [install Oxidized following their documentation](https://github.com/ytti/oxidized#installation).

Then you can procede to the LibreNMS Web UI and go to Oxidized
Settings in the External Settings section of Global Settings. Enable
it and enter the url to your oxidized instance.

To have devices automatically added, you will need to configure
oxidized to pull them from LibreNMS [Feeding
Oxidized](#feeding-oxidized)

LibreNMS will automatically map the OS to the Oxidized model name if
they don't match. this means you shouldn't need to use the model_map
config option within Oxidized.

# Detailed integration information

This is a straight forward use of Oxidized, it relies on you having a
working Oxidized setup which is already taking config snapshots for
your devices. When you have that, you only need the following config
to enable the display of device configs within the device page itself:

```php
$config['oxidized']['enabled']         = TRUE;
$config['oxidized']['url']             = 'http://127.0.0.1:8888';
```

LibreNMS supports config versioning if Oxidized does.  This is known
to work with the git output module.

```php
$config['oxidized']['features']['versioning'] = true;
```

Oxidized supports various ways to utilise credentials to login to
devices, you can specify global username/password within Oxidized,
Group level username/password or per device. LibreNMS currently
supports sending groups back to Oxidized so that you can then define
group credentials within Oxidized. To enable this support please
switch on 'Enable the return of groups to Oxidized':

```php
$config['oxidized']['group_support'] = true;
```

You can set a default group that devices will fall back to with:

```php
$config['oxidized']['default_group'] = 'default';
```

# SELinux

If you're runnng SELinux, you'll need to allow httpd to connect
outbound to the network, otherwise Oxidized integration in the web UI
will silently fail:

```
setsebool -P httpd_can_network_connect 1
```

# Feeding Oxidized

----

Oxidized has support for feeding devices into it via an API call,
support for Oxidized has been added to the LibreNMS API. A sample
config for Oxidized is provided below.

You will need to configure default credentials for your devices in the
Oxidized config, LibreNMS doesn't provide login credentials at this
time.

```bash
      source:
        default: http
        debug: false
        http:
          url: https://librenms/api/v0/oxidized
          map:
            name: hostname
            model: os
            group: group
          headers:
            X-Auth-Token: '01582bf94c03104ecb7953dsadsadwed'
```

LibreNMS is able to reload the Oxidized list of nodes, each time a
device is added to LibreNMS. To do so, edit the option in Global
Settings>External Settings>Oxidized Integration or add the following
to your config.php.

```php
$config['oxidized']['reload_nodes'] = true;

```

# Creating overrides

To return an override to Oxidized you can do this by providing the
override key, followed by matching a lookup for a host (or hosts), and
finally by defining the overriding value itself. LibreNMS does not
check for the validity of these attributes but will deliver them to
Oxidized as defined.

Matching of hosts can be done using `hostname`, `sysname`, `os`,
`location`, `sysDescr` or `hardware` and including either a 'match'
key and value, or a 'regex' key and value. The order of matching is:

- `hostname`
- `sysname`
- `sysDescr`
- `hardware`
- `os`
- `location`
- `ip`

To match on the device hostnames or sysnames that contain 'lon-sw' or
if the location contains 'London' then you would place the following
within config.php:

```php
$config['oxidized']['maps']['group']['hostname'][] = array('regex' => '/^lon-sw/', 'group' => 'london-switches');
$config['oxidized']['maps']['group']['sysname'][] = array('regex' => '/^lon-sw/', 'group' => 'london-switches');
$config['oxidized']['maps']['group']['location'][] = array('regex' => '/london/', 'group' => 'london-switches');
```

To match on a device os of edgeos then please use the following:

```php
$config['oxidized']['maps']['group']['os'][] = array('match' => 'edgeos', 'group' => 'wireless');
```

Matching on OS requires system name of the OS. For example, 'match' =>
'RouterOS' will not work, while 'match' => 'routeros' will.

To override the IP Oxidized uses to poll the device, you can add the
following within config.php:

```php
$config['oxidized']['maps']['ip']['sysname'][] = array('regex' => '/^my.node/', 'ip' => '192.168.1.10');
$config['oxidized']['maps']['ip']['sysname'][] = array('match' => 'my-other.node', 'ip' => '192.168.1.20');
```

This allows extending the configuration further by providing a
completely flexible model for custom flags and settings, for example,
below shows the ability to add an ssh_proxy host within Oxidized
simply by adding the below to your configuration:

```php
$config['oxidized']['maps']['ssh_proxy']['sysname'][] = array('regex' => '/^my.node/', 'ssh_proxy' => 'my-ssh-gateway.node');
```

Or of course, any custom value that could be needed or wanted can be
applied, for example, setting a "myAttribute" to "Super cool value"
for any configured and enabled "routeros" device.

```php
$config['oxidized']['maps']['myAttribute']['os'][] = array('match' => 'routeros', 'myAttribute' => 'Super cool value');
```

Verify the return of groups by querying the API:

```
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/oxidized
```

If you need to, you can specify credentials for groups by using the
following in your Oxidized config:

```bash
groups:
  <groupname>:
    username: <user>
    password: <password>
```

# Miscellaneous

If you have devices which you do not wish to appear in Oxidized then
you can edit those devices in Device -> Edit -> Misc and enable
"Exclude from Oxidized?"

It's also possible to exclude certain device types and OS' from being
output via the API. This is currently only possible via config.php:

```php
$config['oxidized']['ignore_types'] = array('server','power');
$config['oxidized']['ignore_os'] = array('linux','windows');
```

# Trigger configuration backups

Using the Oxidized REST API and [Syslog
Hooks](/Extensions/Syslog/#external-hooks), Oxidized can trigger
configuration downloads whenever a configuration change event has been
logged. An example script to do this is included in
`./scripts/syslog-notify-oxidized.php`. Oxidized can spawn a new
worker thread and perform the download immediately with the following
configuration

```bash
next_adds_job: true
```

# Validate Oxidized config

You can perform basic validation of the Oxidized configuration by
going to the Overview -> Tools -> Oxidized link and in the Oxidized
config validation page, paste your yaml file into the input box and
click 'Validate YAML'.

We check for yaml syntax errors and also actual config values to
ensure they are used in the correct location.

# Accessing configuration of a disabled/removed device

When you're disabling or removing a device from LibreNMS, Oxidized
will no longer shows it in the web interface the configuration. You
can access directly to this information in the Git repository of
Oxidized (if using Git to store versions).

1: Check in your Oxidized where are stored your Git repositories:

```
/home/oxidized/.config/oxidized/config
```

2: Go the correct Git repository for the needed device (the .git one)
and get the list of devices using this command:

```
git ls-files -s
```

3: Save the object ID of the device, and run the command to get the
file content:

```
git cat-file -p <object id>
```
