source: os/Settings.md
# Optional OS Settings

This page documents settings that can be set in the os yaml files or in config.php.
All settings listed here are optional. If they are not set, the global default will be used.

Users can override these settings in their config.php.

For example, to set an alternate icon for ios:
```php
$config['os']['ios']['icon'] = 'fuzzybunny';
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
