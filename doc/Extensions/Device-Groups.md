# Grouping Devices

LibreNMS groups your devices in the same way as the alerts. This
document describes how to start.

## Dynamic Groups

### Rule Editor

A dynamic group uses the MySQL structure of your data, in the same way
as the alerting system. QueryBuilder then generates the SQL queries of
your groups.

In MySQL, run `show tables` to see all the LibreNMS tables. Then run
`desc <tablename>` to see the structure of a table. These two names
give the format of the QueryBuilder interface:
__tablename.columnname__.

To see the data of the table, run `select * from <tablename> limit 5;`.
The output shows the data of your dynamic group.

This common example groups the devices by hostname. The hostname format
is `dcX.[devicetype].example.com`.

To group them by the device type `rtr`, add a rule for the routers:
`devices.hostname` `endswith` `rtr.example.com`. This rule matches
dcX.`rtr.example.com`.

To group them by data centre, use the rule `devices.hostname` regex
`dc1\..*\.example\.com`. Escape each period in the regex. This rule
matches `dc1.rtr.example.com`.

### CIDR prefix matching

Two operators are available for matching IP addresses against a network prefix:

| Operator | Meaning |
|---|---|
| `ip in prefix` | The field value falls within the given CIDR prefix |
| `ip not in prefix` | The field value does not fall within the given CIDR prefix |

Both IPv4 (`192.168.30.0/25`) and IPv6 (`2001:db8::/32`) prefixes are supported. A bare IP without a length is treated as a host route (`/32` or `/128`).

**Which field to use:**

- `devices.ip` — the management IP LibreNMS uses to reach the device (one value per device, no join).  
  Useful for grouping by management network, e.g. all devices reachable via an out-of-band subnet.
- `ipv4_addresses.ipv4_address` / `ipv6_addresses.ipv6_compressed` — every address on every interface.  
  `ip in prefix` matches devices with **at least one** interface address in the prefix;  
  `ip not in prefix` matches devices with **no** interface address in the prefix.
- `ipv4_networks.ipv4_network` / `ipv6_networks.ipv6_network` — discovered subnet prefixes.  
  Useful for matching devices that have a subnet inside a given supernet.

Example: all devices whose management IP is in `192.168.30.0/25`:

- Field: `devices.ip`, Operator: `ip in prefix`, Value: `192.168.30.0/25`

## Static Groups

A static group holds specific devices. You can also convert a dynamic
group to a static group. Select `static` as the type. Then select the
devices of the group.

![Device Groups](../img/device_groups.png)

The group is now available at Devices -> All Devices in the top
navigation. You can also map your device groups to an alert rule. Use
the `Match devices, groups and locations list` section of that rule.