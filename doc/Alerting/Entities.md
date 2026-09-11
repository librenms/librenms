# Entities

An entity comes from a table name and a column name in the database. To
find the correct entity, open MySQL. Then use `show tables` and
`desc <tablename>`.

The tables below give some common entities for the alerting system.
This list is not complete. For the full list, read the MySQL database
schema.

## Devices

Entity | Description
---|---
`devices.hostname` | The device hostname
`devices.sysName` | The device sysName
`devices.sysDescr` | The device sysDescr
`devices.hardware` | The device hardware
`devices.version` | The device os version
`devices.location` | The device location
`devices.status` | The status of the device, 1 | up, 0 | down
`devices.status_reason` | The reason for the down status of the device, icmp or snmp
`devices.ignore` | The value is 1 for an ignored device
`devices.disabled` | The value is 1 for a disabled device
`devices.last_polled` | The datetime of the last poll (yyyy-mm-dd hh:mm:ss)
`devices.type` | The device type, such as network, server, or firewall

## Device Stats

Entity | Description
---|---
`device_stats.ping_loss_last` | Packet loss at last poll (percent)
`device_stats.ping_loss_avg` | Average packet loss (percent)
`device_stats.ping_rtt_last` | Ping RTT at last poll (ms)
`device_stats.ping_rtt_avg` | Average ping RTT (ms)
`device_stats.ping_rtt_diff_avg_last` | Difference between the RTT last and average ping (ms)
`device_stats.ping_rtt_diff_avg_last` | Difference between the RTT of the ping from the last 2 polls (ms)

For the calculation of these averages, read the [averaging
factor](../Support/Configuration.md#averaging-factor) section.

Use the difference fields to detect a ping time above its normal value.

## BGP Peers

Entity | Description
---|---
`bgpPeers.astext` | This is the description of the BGP Peer
`bgpPeers.bgpPeerIdentifier` | The IP address of the BGP Peer
`bgpPeers.bgpPeerRemoteAs` | The AS number of the BGP Peer
`bgpPeers.bgpPeerState` | The operational state of the BGP session
`bgpPeers.bgpPeerAdminStatus` | The administrative state of the BGP session
`bgpPeers.bgpLocalAddr` | The local address of the BGP session.

## IPSec Tunnels

Entity | Description
---|---
`ipsec_tunnels.peer_addr` | The remote VPN peer address
`ipsec_tunnels.local_addr` | The local VPN address
`ipsec_tunnels.tunnel_status` | The VPN tunnels operational status.

## Memory pools

Entity | Description
---|---
`mempools.mempool_type` | The memory pool type such as hrstorage, cmp and cemp
`mempools.mempool_descr` | The description of the pool such as Physical memory, Virtual memory and System memory
`mempools.mempool_perc` | The used percentage of the memory pool.

## Ports

Entity | Description
---|---
`ports.ifDescr` | The interface description
`ports.ifName` | The interface name
`ports.ifSpeed` | The port speed in bps
`ports.ifHighSpeed` | The port speed in mbps
`ports.ifOperStatus` | The operational status of the port (up or down)
`ports.ifAdminStatus` | The administrative status of the port (up or down)
`ports.ifDuplex` | Duplex setting of the port
`ports.ifMtu` | The MTU setting of the port.

## Processors

Entity | Description
---|---
`processors.processor_usage` | The usage of the processor as a percentage
`processors.processor_descr` | The description of the processor.

## Storage

Entity | Description
---|---
`storage.storage_descr` | The description of the storage
`storage.storage_perc` | The usage of the storage as a percentage.

## Health / Sensors

 Entity | Description
---|---
 `sensors.sensor_desc` | The sensors description.
 `sensors.sensor_current` | The current sensors value.
 `sensors.sensor_prev` | The previous sensor value.
 `sensors.lastupdate` | The sensors last updated datetime stamp.
