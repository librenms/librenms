source: os/Settings.md
# Optional OS Settings

This page documents settings that can be set in the os yaml files or in config.php.
All settings listed here are optional. If they are not set, the global default will be used.

Users can override these settings in their config.php.

For example, to set an alternate icon for ios:
```php
$config['os']['ios']['icon'] = 'fuzzybunny';
```

### Ignoring Interfaces
See also: [Global Ignoring Interfaces Config](../../Support/Configuration.md#interfaces-to-be-ignored)

> These settings are merged with the global settings, so you can only undo global ones with good_if

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

### Disable snmpbulkwalk
Some devices have buggy snmp implementations and don't respond well to the more efficient snmpbulkwalk.
To disable snmpbulkwalk and only use snmpwalk for an os set the following.

```yaml
nobulk: true
```

### Storage Settings
See also: [Global Storage Config](../../Support/Configuration.md#storage-configuration)

```yaml
ignore_mount array: # exact match
    - /var/run
ignore_mount_string: # substring
    - run
ignore_mount_regexp: # regex
    - "/^\/var/"
```
