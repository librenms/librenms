source: Extensions/Auto-Discovery.md
path: blob/master/doc/

# Auto Discovery Support

## Getting Started

LibreNMS provides the ability to automatically add devices on your
network, we can do this via a few methods which will be explained
below and also indicate if they are enabled by default.

All discovery methods run when discovery runs (every 6 hours by
default and within 5 minutes for new devices).

Please note that you need at least ONE device added before
auto-discovery will work.

The first thing to do though is add the required configuration options to `config.php`.

## SNMP Details

To add devices automatically we need to know your snmp details,
examples of SNMP v1, v2c and v3 are below:

```php
// v1 or v2c
$config['snmp']['community'][] = "my_custom_community";
$config['snmp']['community'][] = "another_community";

// v3
$config['snmp']['v3'][0]['authlevel'] = 'authPriv';
$config['snmp']['v3'][0]['authname'] = 'my_username';
$config['snmp']['v3'][0]['authpass'] = 'my_password';
$config['snmp']['v3'][0]['authalgo'] = 'MD5';
$config['snmp']['v3'][0]['cryptopass'] = 'my_crypto';
$config['snmp']['v3'][0]['cryptoalgo'] = 'AES';
```

These details will be attempted when adding devices, you can specify
any mixture of these.

## Allowed Networks

### Your Networks

To add devices, we need to know what are your subnets so we don't go
blindly attempting to add devices not under your control.

```php
$config['nets'][] = '192.168.0.0/24';
$config['nets'][] = '172.2.4.0/22';
```

## Exclusions

If you have added a network as above but a single device exists within
it that you can't auto add, then you can exclude this with the following:

```php
$config['autodiscovery']['nets-exclude'][] = '192.168.0.1/32';
```

# Additional Options

## Discovering devices by IP

By default we don't add devices by IP address, we look for a reverse
dns name to be found and add with that. If this fails
and you would like to still add devices automatically then you will
need to set `$config['discovery_by_ip'] = true;`

### Short hostnames

If your devices only return a short hostname such as lax-fa0-dc01 but
the full name should be lax-fa0-dc01.example.com then you can
set `$config['mydomain'] = 'example.com';`

### Allow Duplicate sysName

By default we require unique sysNames when adding devices (this is
returned over snmp by your devices). If you would like to allow
devices to be added with duplicate sysNames then please set
`$config['allow_duplicate_sysName'] = true;`.

# Discovery Methods

Below are the methods for auto discovering devices.  Each one can be
enabled or disabled and may have additional configuration options.

## ARP

Disabled by default.

Adds devices that are listed in another device's arp table.  This
module depends on the arp-table module being enabled and returning
data.

To enable, switch on globally the
`$config['discovery_modules']['discovery-arp'] = true;` or per device
within the Modules section.

## XDP

Enabled by default.

`$config['autodiscovery']['xdp'] = false;` to disable.

This includes FDP, CDP and LLDP support based on the device type.

Devices may be excluded from xdp discovery by sysName and sysDescr.

```php
//Exclude devices by name
$config['autodiscovery']['xdp_exclude']['sysname_regexp'][] = '/host1/';
$config['autodiscovery']['xdp_exclude']['sysname_regexp'][] = '/^dev/';

//Exclude devices by description
$config['autodiscovery']['xdp_exclude']['sysdesc_regexp'][] = '/Vendor X/';
$config['autodiscovery']['xdp_exclude']['sysdesc_regexp'][] = '/Vendor Y/';
```

Devices may be excluded from cdp discovery by platform.

```php
//Exclude devices by platform(Cisco only)
$config['autodiscovery']['cdp_exclude']['platform_regexp'][] = '/WS-C3750G/';
```

These devices are excluded by default:

```php
$config['autodiscovery']['xdp_exclude']['sysdesc_regexp'][] = '/-K9W8-/'; // Cisco Lightweight Access Point
$config['autodiscovery']['cdp_exclude']['platform_regexp'][] = '/^Cisco IP Phone/'; //Cisco IP Phone
```

## OSPF

Enabled by default.

`$config['autodiscovery']['ospf'] = false;` to disable.

## BGP

Enabled by default.

`$config['autodiscovery']['bgp'] = false;` to disable.

This module is invoked from bgp-peers discovery module.

## SNMP Scan

Apart from the aforementioned Auto-Discovery options, LibreNMS is also
able to proactively scan a network for SNMP-enabled devices using the
configured version/credentials.

SNMP Scan will scan `$config['nets']` by default and respects `$config['autodiscovery']['nets-exclude']`.

To run the SNMP-Scanner you need to execute the `snmp-scan.py` from
within your LibreNMS installation directory.

Here the script's help-page for reference:

```text
usage: snmp-scan.py [-h] [-r NETWORK] [-t THREADS] [-l] [-v]

Scan network for snmp hosts and add them to LibreNMS.

optional arguments:
  -h, --help     show this help message and exit
  -r NETWORK     CIDR noted IP-Range to scan. Can be specified multiple times
                 This argument is only required if $config['nets'] is not set
                 Example: 192.168.0.0/24 Example: 192.168.0.0/31 will be
                 treated as an RFC3021 p-t-p network with two addresses,
                 192.168.0.0 and 192.168.0.1 Example: 192.168.0.1/32 will be
                 treated as a single host address
  -t THREADS     How many IPs to scan at a time. More will increase the scan
                 speed, but could overload your system. Default: 32
  -l, --legend   Print the legend.
  -v, --verbose  Show debug output. Specifying multiple times increases the
                 verbosity.

```
