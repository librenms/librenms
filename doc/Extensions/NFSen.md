source: Extensions/NFSen.md
# NFSen

> The installation of NFSen is out of scope for this document / LibreNMS

#### Configuration

The following is the configuration that can be used:

```php
$config['nfsen_enable'] = 1;
$config['nfsen_split_char']   = "_";
$config['nfsen_rrds']   = "/var/nfsen/profiles-stat/live/";
$config['nfsen_suffix']   = "_yourdomain_com";
```

Set `$config['nfsen_enable'] = 1;` to enable NFSen support.

`$config['nfsen_rrds']` This value tells us where your NFSen rrd files live. This can also be an array to 
specify more directories like:

```php
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat/sitea/';
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat/siteb/';
```

`$config['nfsen_split_char']` This value tells us what to replace the full stops `.` in the devices hostname with.

`$config['nfsen_suffix']` This value will be removed from the domain name and can be useful if your rrd files are 
something like `host1.rrd` but your device hostname is `domain.host1.rrd`. You can then set $config['nfsen_suffix'] = 'domain';

You should a new tab for the device called Nfsen.
