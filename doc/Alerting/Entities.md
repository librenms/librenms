source: Alerting/Entities.md

# <a name="entities">Entities

Entities as described earlier are based on the table and column names within the database, if you are unsure of what the entity is you want then have a browse around inside MySQL using `show tables` and `desc <tablename>`.

## <a name="entity-devices">Devices</a>

__devices.hostname__ = The device hostname.

__devices.sysName__ = The device sysName.

__devices.sysDescr__ = The device sysDescr.

__devices.hardware__ = The device hardware.

__devices.version__ = The device os version.

__devices.location__ = The device location.

__devices.status__ = The status of the device, 1 = up, 0 = down.

__devices.status_reason__ = The reason the device was detected as down (icmp or snmp).

__devices.ignore__ = If the device is ignored this will be set to 1.

__devices.disabled__ = If the device is disabled this will be set to 1.

__devices.last_polled__ = The the last polled datetime (yyyy-mm-dd hh:mm:ss).

__devices.type__ = The device type such as network, server, firewall, etc.

## <a name="entity-bgppeers">BGP Peers</a>

__bgpPeers.astext__ = This is the description of the BGP Peer.

__bgpPeers.bgpPeerIdentifier__ = The IP address of the BGP Peer.

__bgpPeers.bgpPeerRemoteAs__ = The AS number of the BGP Peer.

__bgpPeers.bgpPeerState__ = The operational state of the BGP session.

__bgpPeers.bgpPeerAdminStatus__ = The administrative state of the BGP session.

__bgpPeers.bgpLocalAddr__ = The local address of the BGP session.

## <a name="entity-ipsec">IPSec Tunnels</a>

__ipsec_tunnels.peer_addr__ = The remote VPN peer address.

__ipsec_tunnels.local_addr__ = The local VPN address.

__ipsec_tunnels.tunnel_status__ = The VPN tunnels operational status.

## <a name="entity-mempools">Memory pools</a>

__mempools.mempool_type__ = The memory pool type such as hrstorage, cmp and cemp.

__mempools.mempool_descr__ = The description of the pool such as Physical memory, Virtual memory and System memory.

__mempools.mempool_perc__ = The used percentage of the memory pool.

## <a name="entity-ports">Ports</a>

__ports.ifDescr__ = The interface description.

__ports.ifName__ = The interface name.

__ports.ifSpeed__ = The port speed in bps.

__ports.ifHighSpeed__ = The port speed in mbps.

__ports.ifOperStatus__ = The operational status of the port (up or down).

__ports.ifAdminStatus__ = The administrative status of the port (up or down).

__ports.ifDuplex__ = Duplex setting of the port.

__ports.ifMtu__ = The MTU setting of the port.

## <a name="entity-processors">Processors</a>

__processors.processor_usage__ = The usage of the processor as a percentage.

__processors.processor_descr__ = The description of the processor.

## <a name="entity-storage">Storage</a>

__storage.storage_descr__ = The description of the storage.

__storage.storage_perc__ = The usage of the storage as a percentage.

## <a name="entity-sensors">Health / Sensors</a>
 
 __sensors.sensor_desc__ = The sensors description.
 
 __sensors.sensor_current__ = The current sensors value.
 
 __sensors.sensor_prev__ = The previous sensor value.
 
 __sensors.lastupdate__ = The sensors last updated datetime stamp.