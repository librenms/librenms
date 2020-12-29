source: API/ARP.md
path: blob/master/doc/

### `list_arp`

Retrieve a specific ARP entry or all ARP entries for a device

Route: `/api/v0/resources/ip/arp/:query`

Query can be:
- An IP address
- A MAC address
- A CIDR network (192.168.1.0/24)
- `all` and set ?device=_hostname_ (or device id)

Input:

- device if you specify all for the query then you need to populate this
  with the hostname or id of the device.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/1.1.1.1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/192.168.1.0/24
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/all?device=localhost
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 1,
    "arp": [
        {
            "port_id": "229",
            "mac_address": "da160e5c2002",
            "ipv4_address": "1.1.1.1",
            "context_name": ""
        }
    ]
}
```
