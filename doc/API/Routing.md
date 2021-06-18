source: API/Routing.md
path: blob/master/doc/

### `list_bgp`

List the current BGP sessions.

Route: `/api/v0/bgp`

Input:

- hostname = Either the devices hostname or id.
- asn = The local ASN you would like to filter by
- remote_asn = Filter by remote peer ASN
- remote_address = Filter by remote peer address
- local_address = Filter by local address



Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp?hostname=host.example.com
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp?asn=1234
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp?remote_asn=1234
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp?local_address=1.1.1.1&remote_address=2.2.2.2
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "bgp_sessions": [
        {
            "bgpPeer_id": "4",
            "device_id": "2",
            "astext": "",
            "bgpPeerIdentifier": "1234:1b80:1:12::2",
            "bgpPeerRemoteAs": "54321",
            "bgpPeerState": "established",
            "bgpPeerAdminStatus": "running",
            "bgpLocalAddr": "1234:1b80:1:12::1",
            "bgpPeerRemoteAddr": "0.0.0.0",
            "bgpPeerInUpdates": "3",
            "bgpPeerOutUpdates": "1",
            "bgpPeerInTotalMessages": "0",
            "bgpPeerOutTotalMessages": "0",
            "bgpPeerFsmEstablishedTime": "0",
            "bgpPeerInUpdateElapsedTime": "0",
            "context_name": ""
        },
    ...
    ],
    "count": 100
}
```

### `get_bgp`

Retrieves a BGP session by ID

Route: `/api/v0/bgp/:id`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp/4
```

Output:

```json
{
    "status": "ok",
    "bgp_session": [
        {
            "bgpPeer_id": "4",
            "device_id": "2",
            "astext": "",
            "bgpPeerIdentifier": "1234:1b80:1:12::2",
            "bgpPeerRemoteAs": "54321",
            "bgpPeerState": "established",
            "bgpPeerAdminStatus": "running",
            "bgpLocalAddr": "1234:1b80:1:12::1",
            "bgpPeerRemoteAddr": "0.0.0.0",
            "bgpPeerInUpdates": "3",
            "bgpPeerOutUpdates": "1",
            "bgpPeerInTotalMessages": "0",
            "bgpPeerOutTotalMessages": "0",
            "bgpPeerFsmEstablishedTime": "0",
            "bgpPeerInUpdateElapsedTime": "0",
            "context_name": ""
        }
    ],
    "count": 1
}
```

### `list_cbgp`

List the current BGP sessions counters.

Route: `/api/v0/routing/bgp/cbgp`

Input:

- hostname = Either the devices hostname or id

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/bgp/cbgp
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/bgp/cbgp?hostname=host.example.com
```

Output:

```json
{
    "status": "ok",
    "bgp_counters": [
        {
            "device_id": "9",
            "bgpPeerIdentifier": "192.168.99.31",
            "afi": "ipv4",
            "safi": "multicast",
            "AcceptedPrefixes": "2",
            "DeniedPrefixes": "0",
            "PrefixAdminLimit": "0",
            "PrefixThreshold": "0",
            "PrefixClearThreshold": "0",
            "AdvertisedPrefixes": "11487",
            "SuppressedPrefixes": "0",
            "WithdrawnPrefixes": "10918",
            "AcceptedPrefixes_delta": "-2",
            "AcceptedPrefixes_prev": "2",
            "DeniedPrefixes_delta": "0",
            "DeniedPrefixes_prev": "0",
            "AdvertisedPrefixes_delta": "-11487",
            "AdvertisedPrefixes_prev": "11487",
            "SuppressedPrefixes_delta": "0",
            "SuppressedPrefixes_prev": "0",
            "WithdrawnPrefixes_delta": "-10918",
            "WithdrawnPrefixes_prev": "10918",
            "context_name": ""
        },
        ...
    ],
    "count": 100
}
```

### `list_ip_addresses`

List all IPv4 and IPv6 addresses.

Route: `/api/v0/resources/ip/addresses`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/addresses
```

Output:

```json
{
    "status": "ok",
    "ip_addresses": [
        {
            "ipv4_address_id": "69",
            "ipv4_address": "127.0.0.1",
            "ipv4_prefixlen": "8",
            "ipv4_network_id": "55",
            "port_id": "135",
            "context_name": ""
        },
        ...
    ],
    "count": 55
}

```

### `get_network_ip_addresses`

Get all IPv4 and IPv6 addresses for particular network.

Route: `/api/v0/resources/ip/networks/:id/ip`

- id must be integer

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/networks/55/ip
```

Output:

```json
{
    "status": "ok",
    "addresses": [
        {
            "ipv4_address_id": "69",
            "ipv4_address": "127.0.0.1",
            "ipv4_prefixlen": "8",
            "ipv4_network_id": "55",
            "port_id": "135",
            "context_name": ""
        }
    ],
    "count": 1
}
```

### `list_ip_networks`

List all IPv4 and IPv6 networks.

Route: `/api/v0/resources/ip/networks`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/networks
```

Output:

```json
{
    "status": "ok",
    "ip_networks": [
        {
            "ipv4_network_id": "1",
            "ipv4_network": "127.0.0.0/8",
            "context_name": ""
        },
        ...
    ],
    "count": 100
}
```

### `list_ipsec`

List the current IPSec tunnels which are active.

Route: `/api/v0/routing/ipsec/data/:hostname`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/ipsec/data/localhost
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 0,
    "ipsec": [
        "tunnel_id": "1",
        "device_id": "1",
        "peer_port": "0",
        "peer_addr": "127.0.0.1",
        "local_addr": "127.0.0.2",
        "local_port": "0",
        "tunnel_name": "",
        "tunnel_status": "active"
    ]
}
```

> Please note, this will only show active VPN sessions not all configured.

### `list_ospf`

List the current OSPF neighbours.

Route: `/api/v0/ospf`

Input:

- hostname = Either the devices hostname or id.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/ospf
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/ospf?hostname=host.example.com
```

Output:

```json
{
 "status": "ok",
 "ospf_neighbours": [
        {
            "device_id": "1",
            "port_id": "0",
            "ospf_nbr_id": "172.16.1.145.0",
            "ospfNbrIpAddr": "172.16.1.145",
            "ospfNbrAddressLessIndex": "0",
            "ospfNbrRtrId": "172.16.0.140",
            "ospfNbrOptions": "82",
            "ospfNbrPriority": "1",
            "ospfNbrState": "full",
            "ospfNbrEvents": "5",
            "ospfNbrLsRetransQLen": "0",
            "ospfNbmaNbrStatus": "active",
            "ospfNbmaNbrPermanence": "dynamic",
            "ospfNbrHelloSuppressed": "false",
            "context_name": ""
        }
    ],
    "count": 1
}
```

### `list_ospf_ports`

List the current OSPF ports.

Route: `/api/v0/ospf_ports`

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/ospf_ports
```

Output:

```json
{
 "status": "ok",
 "ospf_ports": [
        {
          "id": 189086,
          "device_id": 43,
          "port_id": 2838,
          "ospf_port_id": "10.10.2.86.0",
          "ospfIfIpAddress": "10.10.2.86",
          "ospfAddressLessIf": 0,
          "ospfIfAreaId": "0.0.0.0",
          "ospfIfType": "pointToPoint",
          "ospfIfAdminStat": "enabled",
          "ospfIfRtrPriority": 128,
          "ospfIfTransitDelay": 1,
          "ospfIfRetransInterval": 5,
          "ospfIfHelloInterval": 10,
          "ospfIfRtrDeadInterval": 40,
          "ospfIfPollInterval": 90,
          "ospfIfState": "pointToPoint",
          "ospfIfDesignatedRouter": "0.0.0.0",
          "ospfIfBackupDesignatedRouter": "0.0.0.0",
          "ospfIfEvents": 33,
          "ospfIfAuthKey": "",
          "ospfIfStatus": "active",
          "ospfIfMulticastForwarding": "unicast",
          "ospfIfDemand": "false",
          "ospfIfAuthType": "0",
          "ospfIfMetricIpAddress": "10.10.2.86",
          "ospfIfMetricAddressLessIf": 0,
          "ospfIfMetricTOS": 0,
          "ospfIfMetricValue": 10,
          "ospfIfMetricStatus": "active",
          "context_name": null
        }
    ],
    "count": 1
}
```

### `list_vrf`

List the current VRFs.

Route: `/api/v0/routing/vrf`

Input:

- hostname = Either the devices hostname or id

**OR**

- vrfname = The VRF name you would like to filter by

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/vrf
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/vrf?hostname=host.example.com
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/vrf?vrfname=Mgmt-vrf
```

Output:

```json
{
    "status": "ok",
    "vrfs": [
        {
            "vrf_id": "2",
            "vrf_oid": "8.77.103.109.116.45.118.114.102",
            "vrf_name": "Mgmt-vrf",
            "mplsVpnVrfRouteDistinguisher": "",
            "mplsVpnVrfDescription": "",
            "device_id": "8"
        },
        ...
    ],
    "count": 100
}
```

### `get_vrf`

Retrieves VRF by ID

Route: `/api/v0/routing/vrf/:id`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/vrf/2
```

Output:

```json
{
    "status": "ok",
    "vrf": [
        {
            "vrf_id": "2",
            "vrf_oid": "8.77.103.109.116.45.118.114.102",
            "vrf_name": "Mgmt-vrf",
            "mplsVpnVrfRouteDistinguisher": "",
            "mplsVpnVrfDescription": "",
            "device_id": "8"
        }
    ],
    "count": 1
}
```
