source: Extensions/NFSen.md
# NFSen

> The installation of NFSen is out of scope for this document / LibreNMS

#### Configuration

The following is the configuration that can be used:

```php
$config['nfsen_enable'] = 1;
$config['nfsen_split_char']   = '_';
$config['nfsen_rrds'][]   = '/var/nfsen/profiles-stat/live/';
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat';
$config['nfsen_suffix']   = "_yourdomain_com";
```

Set `$config['nfsen_enable'] = 1;` to enable NFSen support.

`$config['nfsen_rrds']` This value tells us where your NFSen rrd files live. This can also be an array to 
specify more directories like:

```php
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat/sitea/';
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat/siteb/';
```

Although for most setups, it will look like below, with the profiles-stat/live directory being where it stores the general RRDs for data sources.

```php
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat/live';
```

`$config['nfsen_split_char']` This value tells us what to replace the full stops `.` in the devices hostname with.

`$config['nfsen_suffix']` This value will be removed from the domain name and can be useful if your rrd files are 
something like `host1.rrd` but your device hostname is `domain.host1.rrd`. You can then set $config['nfsen_suffix'] = 'domain';

If you wish to render info for configure channels for a device, you need add the various profile-stat directories your system uses, which for most systems will be as below.

```php
$config['nfsen_rrds'][] = '/var/nfsen/profiles-stat';
```

When adding sources to nfsen.conf, it is important to use the hostname that matches what is configured in LibreNMS, because the rrd files NfSen creates is named after the source name (ident), and it doesn't allow you to use an IP address instead. However, in LibreNMS, if your device is added by an IP address, add your source with any name of your choice, and create a symbolic link to the rrd file.
```sh
cd /var/nfsen/profiles-stat/sitea/
ln -s mychannel.rrd librenmsdeviceIP.rrd
```

When creating profiles under nfsen, be sure to use the hostname so it matches the name in LibreNMS. That is where channel data will be pulled from.

You should see a new tab for the device called Nfsen.

