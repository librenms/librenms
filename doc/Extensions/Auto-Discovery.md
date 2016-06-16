# Auto discovery support

LibreNMS provides the ability to automatically add devices on your network, we can do this with via 
a few methods which will be explained below and also indicate if they are enabled by default.

All discovery methods run when discovery.php runs (every 6 hours by default and within 5 minutes for new devices).

### Discovery methods

#### ARP
Disabled by default.

To enable, switch on globally the `$config['discovery_modules']['discovery-arp'] = 1;` or per device within the Modules section.

#### XDP
Enabled by default.

`$config['autodiscovery']['xdp'] = false;` to disable.

This includes FDP, CDP and LLDP support based on the device type.

#### OSPF
Enabled by default.

`$config['autodiscovery']['ospf'] = false;` to disable.

#### BGP
Enabled by default.

`$config['autodiscovery']['bgp'] = false;` to disable.

This module is invoked from bgp-peers discovery module.

### Including / Excluding subnets to scan

By default the following config is in place to exclude loopback, multicast, etc ranges. You can expand this out by adding more 
ranges to config.php

```php
$config['autodiscovery']['nets-exclude'][] = '0.0.0.0/8';
$config['autodiscovery']['nets-exclude'][] = '127.0.0.0/8';
$config['autodiscovery']['nets-exclude'][] = '169.254.0.0/16';
$config['autodiscovery']['nets-exclude'][] = '224.0.0.0/4';
$config['autodiscovery']['nets-exclude'][] = '240.0.0.0/4';
```

You will need to specify your own subnets that you would like to scan for which can be done with:

`$config['nets'][] = '8.8.8.0/24';`

#### Discovering devices by IP

By default we don't add devices by IP address, we look for a reverse dns name to be found and add with that. If this fails 
and you would like to still add devices automatically then you will need to set `$config['discovery_by_ip'] = true;`

#### Short hostnames

If your devices only return a short hostname such as lax-fa0-dc01 but the full name should be lax-fa0-dc01.example.com then you can 
set `$config['mydomain'] = 'example.com';`
