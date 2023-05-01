# discovery.php

This document will explain how to use discovery.php to debug issues or
manually running to process data.

## Command options

```bash
-h <device id> | <device hostname wildcard>  Poll single device
-h odd                                       Poll odd numbered devices  (same as -i 2 -n 0)
-h even                                      Poll even numbered devices (same as -i 2 -n 1)
-h all                                       Poll all devices
-h new                                       Poll all devices that have not had a discovery run before
--os <os_name>                               Poll devices only with specified operating system
--type <type>                                Poll devices only with specified type
-i <instances> -n <number>                   Poll as instance <number> of <instances>
                                             Instances start at 0. 0-3 for -n 4

Debugging and testing options:
-d                                           Enable debugging output
-v                                           Enable verbose debugging output
-m                                           Specify module(s) to be run. Comma separate modules, submodules may be added with /
```

`-h` Use this to specify a device via either id or hostname (including
wildcard using *). You can also specify odd and even. all will run
discovery against all devices whilst new will poll only those devices
that have recently been added or have been selected for rediscovery.

`-i` This can be used to stagger the discovery process.

`-d` Enables debugging output (verbose output but with most sensitive
data masked) so that you can see what is happening during a discovery
run. This includes things like rrd updates, SQL queries and response
from snmp.

`-v` Enables verbose debugging output with all data in tact.

`-m` This enables you to specify the module you want to run for discovery.

## Discovery wrapper

We have a `discovery-wrapper.py` script which is based on
`poller-wrapper.py` by [Job Snijders](https://github.com/job). This
script is currently the default.

If you need to debug the output of discovery-wrapper.py then you can
add `-d` to the end of the command - it is NOT recommended to do this
in cron.

You also may use `-m` to pass a list of comma-separated modules.
Please refer to [Command options](#command-options) of discovery.php.
Example: `/opt/librenms/discovery-wrapper.py 1 -m bgp-peers`

If you want to switch back to discovery.php then you can replace:

`33  */6   * * *   librenms    /opt/librenms/discovery-wrapper.py 1 >> /dev/null 2>&1`

With:

`33  */6   * * *   librenms    /opt/librenms/discovery.php -h all >> /dev/null 2>&1`

## Discovery config

These are the default discovery config items. You can globally disable
a module by setting it to 0. If you just want to disable it for one
device then you can do this within the WebUI -> Device -> Settings ->
Modules.

!!! setting "discovery/discovery_modules"
    ```bash
    lnms config:set discovery_modules.os true
    lnms config:set discovery_modules.ports true
    lnms config:set discovery_modules.ports-stack true
    lnms config:set discovery_modules.entity-physical true
    lnms config:set discovery_modules.entity-state false
    lnms config:set discovery_modules.processors true
    lnms config:set discovery_modules.mempools true
    lnms config:set discovery_modules.cisco-vrf-lite true
    lnms config:set discovery_modules.cisco-mac-accounting false
    lnms config:set discovery_modules.cisco-pw false
    lnms config:set discovery_modules.vrf false
    lnms config:set discovery_modules.cisco-cef false
    lnms config:set discovery_modules.slas false
    lnms config:set discovery_modules.cisco-cbqos false
    lnms config:set discovery_modules.cisco-otv false
    lnms config:set discovery_modules.ipv4-addresses true
    lnms config:set discovery_modules.ipv6-addresses true
    lnms config:set discovery_modules.route false
    lnms config:set discovery_modules.sensors true
    lnms config:set discovery_modules.storage true
    lnms config:set discovery_modules.hr-device true
    lnms config:set discovery_modules.discovery-protocols true
    lnms config:set discovery_modules.arp-table true
    lnms config:set discovery_modules.discovery-arp false
    lnms config:set discovery_modules.junose-atm-vp false
    lnms config:set discovery_modules.bgp-peers true
    lnms config:set discovery_modules.vlans true
    lnms config:set discovery_modules.vminfo false
    lnms config:set discovery_modules.printer-supplies false
    lnms config:set discovery_modules.ucd-diskio true
    lnms config:set discovery_modules.applications false
    lnms config:set discovery_modules.services true
    lnms config:set discovery_modules.stp true
    lnms config:set discovery_modules.ntp true
    lnms config:set discovery_modules.loadbalancers false
    lnms config:set discovery_modules.mef false
    lnms config:set discovery_modules.wireless true
    lnms config:set discovery_modules.fdb-table true
    lnms config:set discovery_modules.xdsl false
    ```

## OS based Discovery config

You can enable or disable modules for a specific OS by using
`lnms config:set` OS based settings have preference
over global. Device based settings have preference over all others

Discover performance improvement can be achieved by deactivating all
modules that are not supported by specific OS.

E.g. to deactivate spanning tree but activate discovery-arp module for linux OS

!!! setting "discovery/discovery_modules"
    ```bash
    lnms config:set os.linux.discovery_modules.stp false
    lnms config:set os.linux.discovery_modules.discovery-arp true
    ```

## Discovery modules

`os`: Os detection. This module will pick up the OS of the device.

`ports`: This module will detect all ports on a device excluding ones
configured to be ignored by config options.

`ports-stack`: Same as ports except for stacks.

`xdsl`: Module to collect more metrics for xDSL interfaces.

`entity-physical`: Module to pick up the devices hardware support.

`processors`: Processor support for devices.

`mempools`: Memory detection support for devices.

`cisco-vrf-lite`: VRF-Lite detection and support.

`ipv4-addresses`: IPv4 Address detection

`ipv6-addresses`: IPv6 Address detection

`route`: This module will load the routing table of the device. The default route
 limit is 1000 (configurable with `lnms config:set routes.max_number 1000`), with history data.

`sensors`: Sensor detection such as Temperature, Humidity, Voltages + More

`storage`: Storage detection for hard disks

`hr-device`: Processor and Memory support via HOST-RESOURCES-MIB.

`discovery-protocols`: Auto discovery module for xDP, OSPF and BGP.

`arp-table`: Detection of the ARP table for the device.

`fdb-table`: Detection of the Forwarding DataBase table for the
device, with history data.

`discovery-arp`: Auto discovery via ARP.

`junose-atm-vp`: Juniper ATM support.

`bgp-peers`: BGP detection and support.

`vlans`: VLAN detection and support.

`cisco-mac-accounting`: MAC Address account support.

`cisco-pw`: Pseudowires wires detection and support.

`vrf`: VRF detection and support.

`cisco-cef`: CEF detection and support.

`slas`: SLA detection and support.

`vminfo`: Detection of vm guests for VMware ESXi and libvert

`printer-supplies`: Toner levels support.

`ucd-diskio`: Disk I/O support.

`services`: *Nix services support.

`charge`: APC Charge detection and support.

## Running

Here are some examples of running discovery from within your install directory.

```bash
./discovery.php -h localhost

./discovery.php -h localhost -m ports
```

## Debugging

To provide debugging output you will need to run the discovery process
with the `-d` flag. You can do this either against all modules, single
or multiple modules:

All Modules

```bash
./discovery.php -h localhost -d
```

Single Module

```bash
./discovery.php -h localhost -m ports -d
```

Multiple Modules

```bash
./discovery.php -h localhost -m ports,entity-physical -d
```

Using `-d` shouldn't output much sensitive information, `-v` will so
it is then advisable to sanitise the output before pasting it
somewhere as the debug output will contain snmp details amongst other
items including port descriptions.

The output will contain:

DB Updates

SNMP Response
