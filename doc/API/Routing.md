source: API/Routing.md

### `list_bgp`

List the current BGP sessions.

Route: `/api/v0/bgp`

Input:

  - hostname = Either the devices hostname or id.
**OR**
  - asn = The ASN you would like to filter by

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp?hostname=host.example.com
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp?asn=1234
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
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
    "err-msg": "",
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
