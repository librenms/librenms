# Auto Discovery Support

## Getting Started

LibreNMS provides the ability to automatically add devices on your
network. There are several methods, as described
below and also indicate if they are enabled by default.

All discovery methods run when discovery runs (every 6 hours by
default and within 5 minutes for new devices).

Note: you need at least ONE device before
auto discovery works.

The first thing to do though is add the required configuration options.

## SNMP Details

To add devices automatically we need to know your snmp details,
examples of SNMP v1, v2c and v3 are below:

!!! setting "poller/snmp"
    ```bash
    lnms config:set snmp.community.+ my_custom_community
    lnms config:set snmp.community.+ another_community

    lnms config:set snmp.v3.+ '{
        "authlevel": "authPriv",
        "authname": "my_username",
        "authpass": "my_password",
        "authalgo": "SHA",
        "cryptopass": "my_crypto",
        "cryptoalgo": "AES"
    }'
    ```

LibreNMS tries these details at each new device. You can give
any mixture of these.

## Allowed Networks

### Your Networks

LibreNMS needs your subnets for the new devices. It then does not go
blindly attempting to add devices not under your control.

!!! setting "discovery/networks"
    ```bash
    lnms config:set nets.+ '192.168.0.0/24'
    lnms config:set nets.+ '172.2.4.0/22'
    ```

### Exclusions

If you have added a network as above but a single device exists within
with no automatic add, exclude it with this setting:

!!! setting "discovery/networks"
    ```bash
    lnms config:set autodiscovery.nets-exclude.+ '192.168.0.1/32'
    ```

## Additional Options

### Discovering devices by IP

By default, LibreNMS does not add a device by its IP address. It looks for a reverse
dns name to be found and add with that. If this fails
and you still want the automatic add, you
need to set `$config['discovery_by_ip'] = true;`

### Short hostnames

If your devices only return a short hostname such as lax-fa0-dc01 but
the full name is lax-fa0-dc01.example.com, you can
set

!!! setting "discovery/general"
    ```bash
    lnms config:set mydomain example.com
    ```

### Allow Duplicate sysName

By default we require unique sysNames when adding devices (this is
from the SNMP of your devices. To permit
devices to be added with duplicate sysNames then please set

!!! setting "discovery/discovery_modules"
    ```bash
    lnms config:set allow_duplicate_sysName true
    ```

## Discovery Methods

Below are the methods for auto discovering devices.  Each one can be
enabled or disabled. Some have more configuration options.

### ARP

Disabled by default.

Adds devices that are listed in another device's arp table.  This
module depends on the arp-table module being enabled and returning
data.

To enable, switch on globally the
`discovery_modules.discovery-arp` or per device
within the Modules section.

!!! setting "discovery/discovery_modules"
    ```bash
    lnms config:set discovery_modules.discovery-arp true
    ```

### XDP

Enabled by default. Can be disabled with:

!!! setting "discovery/autodiscovery"
    ```bash
    lnms config:set autodiscovery.xdp false
    ```

This includes FDP, CDP and LLDP support based on the device type.

LibreNMS always discovers the LLDP and xDP links to the neighbours at
the start of the discovery module.
LibreNMS adds the new devices from LLDP and xDP only with
`$config['autodiscovery']['xdp'] = true;`.

You can exclude a device from the xDP discovery by sysName and by sysDescr.

!!! setting "discovery/autodiscovery"
    ```bash
    lnms config:set autodiscovery.xdp_exclude.sysname_regexp.+ '/host1/'
    lnms config:set autodiscovery.xdp_exclude.sysname_regexp.+ '/^dev/'
    
    lnms config:set autodiscovery.xdp_exclude.sysdescr_regexp.+ '/-K9W8/'
    lnms config:set autodiscovery.xdp_exclude.sysdescr_regexp.+ '/Vendor X/'
    ```

You can exclude a device from the CDP discovery by platform. This filter applies only to CDP.

!!! setting "discovery/autodiscovery"
    ```bash
    lnms config:set autodiscovery.cdp_exclude.platform_regexp.+ '/WS-C3750G/'
    lnms config:set autodiscovery.cdp_exclude.platform_regexp.+ '/^Cisco IP Phone/'
    ```

### OSPF

Enabled by default.

!!! setting "discovery/autodiscovery"
    ```bash
    lnms config:set autodiscovery.ospf false
    ```

### OSPFv3

Enabled by default.

!!! setting "discovery/autodiscovery"
    ```bash
    lnms config:set autodiscovery.ospfv3 false
    ```


### BGP

Enabled by default.

!!! setting "discovery/autodiscovery"
    ```bash
    lnms config:set autodiscovery.bgp false
    ```

This module is invoked from bgp-peers discovery module.

### SNMP Scan

Apart from the aforementioned Auto-Discovery options, LibreNMS is also
able to proactively scan a network for SNMP-enabled devices using the
configured version/credentials.

By default, SNMP Scan scans `nets`. It obeys `autodiscovery.nets-exclude`.

To run the SNMP-Scanner you need to execute the `snmp-scan.py` from
within your LibreNMS installation directory.

Here the script's help-page for reference:

```text
usage: snmp-scan.py [-h] [-t THREADS] [-g GROUP] [-l] [-v] [--ping-fallback] [--ping-only] [-P] [network ...]

Scan network for snmp hosts and add them to LibreNMS.

positional arguments:
  network          CIDR noted IP-Range to scan. Can be specified multiple times
                   This argument is only required if 'nets' config is not set
                   Example: 192.168.0.0/24
                   Example: 192.168.0.0/31 will be treated as an RFC3021 p-t-p network with two addresses, 192.168.0.0 and 192.168.0.1
                   Example: 192.168.0.1/32 will be treated as a single host address

optional arguments:
  -h, --help       show this help message and exit
  -t THREADS       How many IPs to scan at a time.  More will increase the scan speed, but could overload your system. Default: 32
  -g GROUP         The poller group all scanned devices will be added to. Default: The first group listed in 'distributed_poller_group', or 0 if not specified
  -l, --legend     Print the legend.
  -v, --verbose    Show debug output. Specifying multiple times increases the verbosity.
  --ping-fallback  Add the device as an ICMP only device if it replies to ping but not SNMP.
  --ping-only      Always add the device as an ICMP only device.
  -P, --ping       Deprecated. Use --ping-fallback instead.
```

### Discovered devices

LibreNMS adds each new device to the `default_poller_group`. Without
this setting, the value is 0.

When using distributed polling, this value can be changed locally by setting `default_poller_group`

To set per-poller, set this in each poller's config.php file:
```php
$config['default_poller_group'] = 3;
```

Set globally

!!! setting "poller/distributed"
    ```bash
    lnms config:set default_poller_group 3
    ```

