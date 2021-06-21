source: Extensions/Interface-Description-Parsing.md
path: blob/master/doc/

# About

Librenms can interpret, display and group certain additional information on ports.
This is done based on the format that the port description is written
although it's possible  to customise the parser to be specific for your setup.

# Keywords

See [examples](#examples) for formats.

- **Keywords**
  - `Cust`    - Customer
  - `Transit` - Transit link
  - `Peering` - Peering link
  - `Core`    - Infrastructure link (non-customer)
- Info-keywords
  - `()` contains a note
  - `{}` contains *your* circuit id
  - `[]` contains the service type or speed

# Examples

Cisco IOS / NXOS / IOSXR:

```text
interface Gi0/1
descr Transit: Example Provider (AS65000)
interface Gi0/2
descr Peering: Peering Exchange
interface Gi0/3
descr Core: core.router01 FastEthernet0/0 (Telco X CCID023141)
interface Gi0/4
descr Cust: Example Customer [10Mbit] (T1 Telco Y CCID129031) {EXAMP0001}
```

Unix / Linux:

This requires an additional script to be [setup](#setup)

```text
# eth3: Cust: Example Customer [10Mbit] (T1 Telco Y CCID129031) {EXAMP0001}
# eth0: Transit: Example Provider (AS65000)
# eth1: Core: core.router01 FastEthernet0/0 (Telco X CCID023141)
# eth2: Peering: Peering Exchange
```

# Customisation

The following config options can be set to enable more custom types:

```php
$config['customers_descr'][]         = 'cust';
$config['transit_descr'][]           = "transit";
$config['peering_descr'][]           = "peering";
$config['core_descr'][]              = "core";
$config['custom_descr'][]            = "something_made_up";
```

# Custom interface parser

It's also possible to write your own parser, the existing one is: includes/port-descr-parser.inc.php

Once you've created your own then you can enable it with:

```php
$config['port_descr_parser'] = "includes/custom/my-port-descr-parser.inc.php";
```

## Setup

For Unix / Linux based systems, you need to run an additional script
to support the parsing of interface infor.

- Add `ifAlias` from `/opt/librenms/scripts/` or download it from
  [here](https://github.com/librenms/librenms/blob/master/scripts/ifAlias)
  to the Server and make it executable `chmod +x /path/to/ifAlias`
- Add to `snmpd.conf` something like:
    ``pass .1.3.6.1.2.1.31.1.1.1.18 /path/to/ifAlias``
 Restart `snmpd` - `service snmpd restart`

There are no changes to be made or additions to install for the polling librenms.

Now you can set up your [keywords](#keywords) in your `/etc/network/interfaces`

``//Add more distributions than just Debian based``
