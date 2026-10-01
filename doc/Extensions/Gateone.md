# GateOne integration

LibreNMS has a simple integration for
[GateOne](https://github.com/liftoff/GateOne). It sends you to your
GateOne command line frontend for access to your equipment. This
integration works only with SSH.

LibreNMS does not include GateOne. Install it separately on the
LibreNMS infrastructure or on a standalone appliance. This document
does not describe that installation.

Add this line to your `config.php`:

```php
$config['gateone']['server'] = 'http://<your_gateone_url/';
```

**Note:** use the full URL with the `/` at the end.

LibreNMS can also put the current LibreNMS user at the start of the SSH
connection URL, for example `ssh://admin@localhost`. To enable this
behaviour, add this line to your `config.php`:

```php
$config['gateone']['use_librenms_user'] = true;
```
