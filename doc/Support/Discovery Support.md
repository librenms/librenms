source: Support/Discovery Support.md
### discovery.php

This document will explain how to use discovery.php to debug issues or manually running to process data.

#### Command options
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
-m                                           Specify single module to be run


```

`-h` Use this to specify a device via either id or hostname (including wildcard using *). You can also specify odd and
even. all will run discovery against all devices whilst
new will poll only those devices that have recently been added or have been selected for rediscovery.

`-i` This can be used to stagger the discovery process.

`-d` Enables debugging output (verbose output but with most sensitive data masked) so that you can see what is happening during a discovery run. This includes things like rrd updates, SQL queries and response from snmp.

`-v` Enables verbose debugging output with all data in tact.

`-m` This enables you to specify the module you want to run for discovery.

#### Discovery wrapper

We have a `discovery-wrapper.py` script which is based on `poller-wrapper.py` by [Job Snijders](https://github.com/job).
You can enable support for this within cron by replacing:

`33  */6   * * *   librenms    /opt/librenms/discovery.php -h all >> /dev/null 2>&1`

With:

`33  */6   * * *   librenms    /opt/librenms/discovery-wrapper.php 1 >> /dev/null 2>&1`

The default is for discovery wrapper to only use 1 thread so that it mimics the current behaviour. However if your 
system is powerful enough and the devices can cope then you can increase the thread count from 1 to a value of your
choosing.

#### Discovery config

These are the default discovery config items. You can globally disable a module by setting it to 0. If you just want to
disable it for one device then you can do this within the WebUI -> Device -> Settings -> Modules.

```php
$config['discovery_modules']['os']                        = 1;
$config['discovery_modules']['ports']                     = 1;
$config['discovery_modules']['ports-stack']               = 1;
$config['discovery_modules']['entity-physical']           = 1;
$config['discovery_modules']['processors']                = 1;
$config['discovery_modules']['mempools']                  = 1;
$config['discovery_modules']['cisco-vrf-lite']            = 1;
$config['discovery_modules']['ipv4-addresses']            = 1;
$config['discovery_modules']['ipv6-addresses']            = 1;
$config['discovery_modules']['route']                     = 0;
$config['discovery_modules']['sensors']                   = 1;
$config['discovery_modules']['storage']                   = 1;
$config['discovery_modules']['hr-device']                 = 1;
$config['discovery_modules']['discovery-protocols']       = 1;
$config['discovery_modules']['arp-table']                 = 1;
$config['discovery_modules']['discovery-arp']             = 0;
$config['discovery_modules']['junose-atm-vp']             = 1;
$config['discovery_modules']['bgp-peers']                 = 1;
$config['discovery_modules']['vlans']                     = 1;
$config['discovery_modules']['cisco-mac-accounting']      = 1;
$config['discovery_modules']['cisco-pw']                  = 1;
$config['discovery_modules']['cisco-vrf']                 = 1;
#$config['discovery_modules']['cisco-cef']                = 1;
$config['discovery_modules']['cisco-sla']                 = 1;
$config['discovery_modules']['vmware-vminfo']             = 1;
$config['discovery_modules']['libvirt-vminfo']            = 1;
$config['discovery_modules']['toner']                     = 1;
$config['discovery_modules']['ucd-diskio']                = 1;
$config['discovery_modules']['services']                  = 1;
$config['discovery_modules']['charge']                    = 1;
```

#### OS based Discovery config

You can enable or disable modules for a specific OS by add corresponding line in `includes/definitions/$os.yaml`
OS based settings have preference over global. Device based settings have preference over all others

Discover performance improvement can be achieved by deactivating all modules that are not supported by specific OS.

E.g. to deactivate spanning tree but activate discovery-arp module for linux OS

```php
$config['os']['linux']['discovery_modules']['stp'] = 0;
$config['os']['linux']['discovery_modules']['discovery-arp'] = 1;
```

#### Discovery modules

`os`: Os detection. This module will pick up the OS of the device.

`ports`: This module will detect all ports on a device excluding ones configured to be ignored by config options.

`ports-stack`: Same as ports except for stacks.

`entity-physical`: Module to pick up the devices hardware support.

`processors`: Processor support for devices.

`mempools`: Memory detection support for devices.

`cisco-vrf-lite`: VRF-Lite detection and support.

`ipv4-addresses`: IPv4 Address detection

`ipv6-addresses`: IPv6 Address detection

`route`: Route detection

`sensors`: Sensor detection such as Temperature, Humidity, Voltages + More

`storage`: Storage detection for hard disks

`hr-device`: Processor and Memory support via HOST-RESOURCES-MIB.

`discovery-protocols`: Auto discovery module for xDP, OSPF and BGP.

`arp-table`: Detection of the ARP table for the device.

`discovery-arp`: Auto discovery via ARP.

`junose-atm-vp`: Juniper ATM support.

`bgp-peers`: BGP detection and support.

`vlans`: VLAN detection and support.

`cisco-mac-accounting`: MAC Address account support.

`cisco-pw`: Pseudowires wires detection and support.

`cisco-vrf`: VRF detection and support.

`cisco-cef`: CEF detection and support.

`cisco-sla`: SLA detection and support.

`vmware-vminfo`: Detection of vmware guests on an ESXi host

`libvirt-vminfo`: Detection of libvirt guests.

`toner`: Toner levels support.

`ucd-diskio`: Disk I/O support.

`services`: *Nix services support.

`charge`: APC Charge detection and support.

#### Running

Here are some examples of running discovery from within your install directory.
```bash
./discovery.php -h localhost

./discovery.php -h localhost -m ports
```

#### Debugging

To provide debugging output you will need to run the discovery process with the `-d` flag. You can do this either against
all modules, single or multiple modules:

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

Using `-d` shouldn't output much sensitive information, `-v` will so it is then advisable to sanitise the output before pasting it somewhere as the debug output will contain snmp details amongst other items including port descriptions.

The output will contain:

DB Updates

SNMP Response

### SNMP Scan

Apart from the aforementioned Auto-Discovery options, LibreNMS is also able to proactively scan a network for SNMP-enabled devices using the configured version/credentials.

Using the SNMP-Scanner may take a long time to finish depending on the size of your network. Tests have shown that a sparsely-populated /24 is scanned within 2 Minutes whereas a sparsely populated /16 will take about 11 Hours.

If possible, divide your network into smaller subnets and scan these subnets instead. You can use an utility like the GNU Screen or tmux to avoid aborting the scan when logging out of your Shell. You can run several instances of the SNMP-Scanner simultaneously.

To run the SNMP-Scanner you need to execute the `snmp-scan.php` from within your LibreNMS installation directory.

Here the script's help-page for reference:
```text
Usage: ./snmp-scan.php -r <CIDR_Range> [-d] [-l] [-h]
  -r CIDR_Range     CIDR noted IP-Range to scan
                    Example: 192.168.0.0/24
  -d                Enable Debug
  -l                Show Legend
  -h                Print this text
```
