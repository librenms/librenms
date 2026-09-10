# Oxidized

Integrating LibreNMS with
[Oxidized](https://github.com/ytti/oxidized-web) brings the following
benefits:

- Config viewing: Current, History, and Diffs all under the Configs tab of each device
- Automatic addition of devices to Oxidized: Including filtering and
  grouping to ease credential management
- Configuration searching (Requires oxidized-web 0.8.0 or newer)

First [install Oxidized with their
documentation](https://github.com/ytti/oxidized#installation).

Then you can procede to the LibreNMS Web UI and go to Oxidized
Settings in the External Settings section of Global Settings. Enable
it and enter the url to your oxidized instance.

For an automatic add of the devices, configure
oxidized to pull them from LibreNMS [Feeding
Oxidized](#feeding-oxidized). Note: the LibreNMS API then controls the
devices, not router.db. The passwords stay in the
oxidized config file.

LibreNMS maps the OS to the Oxidized model name automatically when the
two names differ. You therefore do not need the model_map
config option within Oxidized.

## Detailed integration information

This is a straight forward use of Oxidized, it relies on you having a
working Oxidized setup which is already taking config snapshots for
your devices. When you have that, you only need the following config
to enable the display of device configs within the device page itself:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.enabled true
    lnms config:set oxidized.url http://127.0.0.1:8888
    ```

LibreNMS supports config versioning if Oxidized does.  This is known
to work with the git output module.

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.features.versioning true
    ```

Oxidized has several methods for the login credentials of
devices, you can specify global username/password within Oxidized,
Group level username/password or per device. LibreNMS currently
supports sending groups back to Oxidized so that you can then define
group credentials within Oxidized. To enable this support please
switch on 'Enable the return of groups to Oxidized':

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.group_support true
    ```

This setting gives a default group for the devices:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.default_group default
    ```

You can ignore specific groups

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.ignore_groups '["badgroup", "nobackup"]'
    ```

One trick you can do to ignore all ungrouped devices is set both of these settings

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.default_group nobackup
    lnms config:set oxidized.ignore_groups.+ nobackup
    ```

## SELinux

With SELinux, permit httpd to connect
outbound to the network, otherwise Oxidized integration in the web UI
fails without a message:

```
setsebool -P httpd_can_network_connect 1
```

## Feeding Oxidized

----

Oxidized has support for feeding devices into it via an API call,
the LibreNMS API supports Oxidized. A sample
config for Oxidized is provided below.

Configure the default credentials of your devices in the Oxidized
config. LibreNMS does not supply login credentials at this
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

LibreNMS can reload the Oxidized list of nodes each time that a
device is added to LibreNMS. To do so, edit the option in Global
Settings>External Settings>Oxidized Integration or add the following
to your config.

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.reload_nodes true
    ```

## Creating overrides

To return an override to Oxidized you can do this by providing the
override key, followed by matching a lookup for a host (or hosts), and
finally by defining the overriding value itself. LibreNMS does not
test the validity of these attributes. It sends them to
Oxidized as defined.

Matching of hosts can be done using `hostname`, `sysname`, `os`,
`location`, `sysDescr`, `hardware`, `purpose` or `notes` and including either a 'match'
key and value, or a 'regex' key and value. The order of matching is:

- `hostname`
- `sysName`
- `sysDescr`
- `hardware`
- `os`
- `location`
- `ip`
- `purpose`
- `notes`

To match on the device hostnames or sysNames that contain 'lon-sw' or
for a location that holds 'London', use this setting:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.maps.group.hostname.+ '{"regex": "/^lon-sw/", "value": "london-switches"}'
    lnms config:set oxidized.maps.group.sysName.+ '{"regex": "/^lon-sw/", "value": "london-switches"}'
    lnms config:set oxidized.maps.group.location.+ '{"regex": "/london/", "value": "london-switches"}'
    ```

To match on a device os of edgeos then please use the following:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.maps.group.os.+ '{"match": "edgeos", "value": "wireless"}'
    ```

Matching on OS requires system name of the OS. For example, "match": "RouterOS"
does not work. "match": "routeros" works.

To match a device purpose or device note that holds 'lon-net', use this
setting:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.maps.group.purpose.+ '{"regex": "/^lon-sw/", "value": "london-network"}'
    lnms config:set oxidized.maps.group.notes.+ '{"regex": "/^lon-sw/", "value": "london-network"}'
    ```

To edit an existing map, you must use the index to override it.

!!! setting "external/oxidized"
    ```bash
    lnms config:get oxidized.maps.os.os
    [
        {
            "match": "airos-af-ltu",
            "value": "airfiber"
        },
        {
            "match": "airos-af",
            "value": "airfiber"
        },
    ]
    
    lnms config:set oxidized.maps.os.os.1 '{"match": "airos-af", "value": "something-else"}'
    ```

To override the IP Oxidized uses to poll the device, set the following:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.maps.ip.sysName.+ '{"regex": "/^my.node/", "value": "192.168.1.10"}'
    lnms config:set oxidized.maps.ip.sysName.+ '{"match": "my-other.node", "value": "192.168.1.20"}'
    ```

This allows extending the configuration further by providing a
completely flexible model for custom flags and settings, for example,
below shows the ability to add an ssh_proxy host within Oxidized
Add these lines to your configuration:

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.maps.ssh_proxy.sysName.+ '{"regex": "/^my.node/", "value": "my-ssh-gateway.node"}'
    ```

You can also add any other necessary value
applied, for example, setting a "myAttribute" to "Super cool value"
for any configured and enabled "routeros" device.

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.maps.myAttribute.os.+ '{"match": "routeros", "value": "Super cool value"}'
    ```

Verify the return of groups by querying the API:

```
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/oxidized
```

If you need to, you can specify credentials for groups by using the
following in your Oxidized config:

```bash
groups:
  <groupname>:
    username: <user>
    password: <password>
```
## Have Oxidized add to LibreNMS Eventlog

Oxidized can be configured to add to LibreNMS's eventlog for devices. This gives better visibility when it is having issues checking for config changes, or when it observes the configuration has changed. This uses [Oxidized's Hooks](https://github.com/ytti/oxidized/blob/master/docs/Hooks.md#hook-type-exec).

Example:
```
hooks:
  libre_log_fail:
    type: exec
    events: [node_fail]
    async: true
    cmd: 'curl -k -s -X POST -d "{\"text\": \"Check for config change failed. Reason: ${OX_ERR_REASON//\"}. Group: ${OX_NODE_GROUP} Timetaken: ${OX_JOB_TIME}\",\"severity\":\"3\",\"type\":\"oxidized\"}" -H "X-Auth-Token: YOURAPITOKENHERE" https://foo.example/api/v0/devices/${OX_NODE_NAME}/eventlog'
  libre_log_change:
    type: exec
    events: [post_store]
    async: true
    cmd: 'curl -k -s -X POST -d "{\"text\": \"Config change observed on check. Commit Ref: ${OX_REPO_COMMITREF} Group: ${OX_NODE_GROUP} Timetaken: ${OX_JOB_TIME}\",\"severity\":\"2\",\"type\":\"oxidized\"}" -H "X-Auth-Token: YOURAPITOKENHERE" https://foo.example/api/v0/devices/${OX_NODE_NAME}/eventlog'
```
Note: `/bin/sh` must be bash, or the substitutions fail. [Ruby runs
commands only with the shell at
/bin/sh.](https://ruby-doc.org/core-2.6.5/Process.html#method-c-exec)

## Miscellaneous

If you have devices which you do not wish to appear in Oxidized then
you can edit those devices in Device -> Edit -> Misc and enable
"Exclude from Oxidized?"

The use of custom ssh and telnet ports can be set through device settings misc tab, and can be passed on to oxidized with the following `vars_map`

```bash
      source:
        http:
          map:
            name: hostname
            model: os
            group: group
          vars_map:
            ssh_port: ssh_port
            telnet_port: telnet_port
```

You can also exclude some device types and operating systems from
output via the API.

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.ignore_types '["server", "power"]'
    lnms config:set oxidized.ignore_os '["linux", "windows"]'
    ```

You can also ignore whole groups of devices

!!! setting "external/oxidized"
    ```bash
    lnms config:set oxidized.ignore_groups '["london-switches", "default"]'
    ```

## Trigger configuration backups

Using the Oxidized REST API and [Syslog
Hooks](Syslog.md#external-hooks), Oxidized can trigger
configuration downloads at each configuration change event that LibreNMS
logged. An example script to do this is included in
`./scripts/syslog-notify-oxidized.php`. Oxidized can spawn a new
worker thread and perform the download immediately with the following
configuration

```bash
next_adds_job: true
```

## Accessing configuration of a disabled/removed device

At the disable or the removal of a device in LibreNMS, the
configuration is then no longer available in the LibreNMS web
interface.  
You can gain access to these configurations directly in the Git repository of
Oxidized (if using Git for version control).

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

## Remove disabled/removed device
If you want to purge saved config of a device that is not in LibreNMS anymore, you can run the following command:

```
git rm --cached <object id>
```
