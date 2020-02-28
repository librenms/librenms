source: Extensions/Gateone.md
path: blob/master/doc/

# GateOne integration

We have simple integration for
[GateOne](https://github.com/liftoff/GateOne), you will be redirected
to your Gateone command line frontend to access your
equipment. (Currently this only works with SSH)

GateOne itself isn't included within LibreNMS, you will need to
install this separately either on the same infrastructure as LibreNMS
or as a totally  standalone appliance. The installation is beyond the
scope of this document.

Config is simple, include the following in your `config.php`:

```php
$config['gateone']['server'] = 'http://<your_gateone_url/';
```

**Note:** You *must* use the full url including the trailing `/`!

We also support prefixing the currently logged in Librenms user to the
SSH connection URL that is created, eg. `ssh://admin@localhost`\ To
enable this, put the following in your `config.php`:

```php
$config['gateone']['use_librenms_user'] = true;
```
