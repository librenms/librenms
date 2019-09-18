source: os/Settings.md
path: blob/master/doc/
# Optional OS Settings

This page documents settings that can be set in the os yaml files or
in config.php. All settings listed here are optional. If they are not
set, the global default will be used.

### User override in config.php

Users can override these settings in their config.php.

For example, to set an alternate icon for ios:

```php
$config['os']['ios']['icon'] = 'fuzzybunny';
```

### Ignoring Sensors

It is possible to filter some sensors from the configuration:

- Filter all 'current' sensors for Operating System 'vrp'.

```php
$config['os']['vrp']['disabled_sensors']['current'] = true;
```

- Filter all sensors matching regexp ``` '/PEM Iout/' ``` for
Operating System iosxe.

```php
$config['os']['iosxe']['disabled_sensors_regex'][] = '/PEM Iout/';
```

- Ignore all temperature sensors

```php
$config['disabled_sensors']['current'] = true;
```

- Filter all sensors matching regexp ``` '/PEM Iout/' ```.

```php
$config['disabled_sensors_regex'][] = '/PEM Iout/';
```

### Ignoring Interfaces
See also: [Global Ignoring Interfaces Config](../../Support/Configuration.md#interfaces-to-be-ignored)

> These settings are merged with the global settings, so you can only
> undo global ones with good_if

```yaml
empty_ifdescr: false # allow empty ifDescr
bad_if: # ifDescr (substring, case insensitive)
    - lp0
bad_if_regexp: # ifDescr (regex, case insensitive)
    - "/^ng[0-9]+$/"
bad_ifname_regexp: # ifName (regex, case insensitive)
    - "/^xdsl_channel /"
bad_ifalias_regexp: # ifAlias (regex, case insensitive)
    - "/^vlan/"
bad_iftype: # ifType (substring)
    - sonet
good_if: # ignore all other bad_if settings ifDescr (substring, case insensitive)
    - virtual

```

### Controlling interface labels
By default we use ifDescr to label ports/interfaces.
Setting either `ifname` or `ifalias` will override that.  Only set one
of these.  ifAlias is user supplied. `ifindex` will append the ifindex
to the port label.

```yaml
ifname: true
ifalias: true

ifindex: true
```

### Poller and Discovery Modules

The various discovery and poller modules can be enabled or disabled
per OS.  The defaults are usually reasonable, so likely you won't want
to change more than a few. These modules can be enabled or disabled
per-device in the webui and per os or globally in config.php. Usually,
a poller module will not work if it's corresponding discovery module
is not enabled.

```yaml
poller_modules:
    bgp-peers: true
discovery_modules:
    arp-table: false
```

### SNMP Settings

#### Disable snmpbulkwalk

Some devices have buggy snmp implementations and don't respond well to
the more efficient snmpbulkwalk. To disable snmpbulkwalk and only use
snmpwalk for an os set the following.

```yaml
nobulk: true
```

#### Limit the oids per snmpget

```yaml
snmp_max_oid: 8
```

### Storage Settings

See also: [Global Storage Config](../../Support/Configuration.md#storage-configuration)

```yaml
ignore_mount_array: # exact match
    - /var/run
ignore_mount_string: # substring
    - run
ignore_mount_regexp: # regex
    - "/^\/var/"
```
