### `get_port_vlan_info_by_port`

Get all port_vlan info with port_id

Route: `/api/v0/port_vlan_info/port/:port_id`

- portid must be an integer

Input: {port_id}

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_vlan_info/port/{port_id}
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
        {
          "port_vlan_id": 13297,
          "device_id": 3,
          "port_id": 223,
          "vlan": 102,
          "baseport": 0,
          "priority": 0,
          "state": "unknown",
          "cost": 0,
          "untagged": 1,
          "voice": 1
        },
        {
          "port_vlan_id": 13606,
          "device_id": 3,
          "port_id": 223,
          "vlan": 11,
          "baseport": 0,
          "priority": 0,
          "state": "unknown",
          "cost": 0,
          "untagged": 1,
          "voice": 0
        }
    ]
}
```

### `get_port_security_by_hostname`

Get all port_vlan info by inputting hostname or device_id

Route: `/api/v0/port_security/device/:hostname`

- hostname can be str hostname or int device_id

Input: {hostname}

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_vlan_info/device/switch1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_vlan_info/device/5
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
        {
          "port_vlan_id": 13297,
          "device_id": 3,
          "port_id": 223,
          "vlan": 102,
          "baseport": 0,
          "priority": 0,
          "state": "unknown",
          "cost": 0,
          "untagged": 1,
          "voice": 1
        },
        {
          "port_vlan_id": 13606,
          "device_id": 3,
          "port_id": 223,
          "vlan": 11,
          "baseport": 0,
          "priority": 0,
          "state": "unknown",
          "cost": 0,
          "untagged": 1,
          "voice": 0
        },
        {
          "port_vlan_id": 7680,
          "device_id": 3,
          "port_id": 224,
          "vlan": 477,
          "baseport": 0,
          "priority": 0,
          "state": "unknown",
          "cost": 0,
          "untagged": 1,
          "voice": 0
        }
    ]
}
```
