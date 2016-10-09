source: Extensions/Auto-Discovery.md
# Auto discovery support

### Getting started with auto discovery.

LibreNMS provides the ability to automatically add devices on your network, we can do this with via 
a few methods which will be explained below and also indicate if they are enabled by default.

All discovery methods run when discovery.php runs (every 6 hours by default and within 5 minutes for new devices).

> Please note that you need at least ONE device added before auto-discovery will work.

The first thing to do though is add the required configuration options to `config.php`.

#### SNMP Details

To add devices automatically we need to know your snmp details, examples of SNMP v1, v2c and v3 are below:

```php
// v1 or v2c
$config['snmp']['community'][] = "my_custom_community";
$config['snmp']['community'][] = "another_community";

// v3
$config['snmp']['v3'][0]['authlevel'] = 'AuthPriv';
$config['snmp']['v3'][0]['authname'] = 'my_username';
$config['snmp']['v3'][0]['authpass'] = 'my_password';
$config['snmp']['v3'][0]['authalgo'] = 'MD5';
$config['snmp']['v3'][0]['cryptopass'] = 'my_crypto';
$config['snmp']['v3'][0]['cryptoalgo'] = 'AES';
```

These details will be attempted when adding devices, you can specify any mixture of these.

#### Your networks

To add devices, we need to know what are your subnets so we don't go blindly attempting to add devices not 
under your control.

```php
$config['nets'][] = '192.168.0.0/24';
$config['nets'][] = '172.2.4.0/22';
```

#### Exclusions

If you have added a network as above but a single device exists within it that you can't auto 
add, then you can exclude this with the following:

```php
$config['autodiscovery']['nets-exclude'][] = '192.168.0.1/32';
```

If you want to enable / disable certain auto-discovery modules then see the rest of this doc for further info.

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

#### SNMP Scan
This isn't actually an auto- mechanism but manually invoked.

It's designed to scan through all of the subnets in your config or what you have manually specified 
to automatically add devices. An example of it's usage is:

```bash
./snmp-scan.php -r 192.168.0.0/24
```

#### Discovering devices by IP

By default we don't add devices by IP address, we look for a reverse dns name to be found and add with that. If this fails 
and you would like to still add devices automatically then you will need to set `$config['discovery_by_ip'] = true;`

#### Short hostnames

If your devices only return a short hostname such as lax-fa0-dc01 but the full name should be lax-fa0-dc01.example.com then you can 
set `$config['mydomain'] = 'example.com';`
