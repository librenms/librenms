### `list_vlans`

Get a list of all VLANs.

Route: `/api/v0/resources/vlans`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/vlans
```

Output:

```json
{
    "status": "ok",
    "vlans": [
        {
            "vlan_id": "31",
            "device_id": "10",
            "vlan_vlan": "1",
            "vlan_domain": "1",
            "vlan_name": "default",
            "vlan_type": "ethernet",
            "vlan_mtu": null
        },
        ...
    ],
    "count": 100
}
```

### `get_vlans`

Get a list of all VLANs for a given device.

Route: `/api/v0/devices/:hostname/vlans`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/vlans
```

Output:

```json
{
    "status": "ok",
    "count": 0,
    "vlans": [
    {
   "vlan_vlan": "1",
   "vlan_domain": "1",
   "vlan_name": "default",
   "vlan_type": "ethernet",
   "vlan_mtu": null
    }
  ]
}
```

### `list_links`

Get a list of all Links.

Route: `/api/v0/resources/links`

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/links
```

Output:

```json
{
    "status": "ok",
    "links": [
        {
            "id": 10,
            "local_port_id": 100,
            "local_device_id": 1,
            "remote_port_id": 200,
            "active": 1,
            "protocol": "lldp",
            "remote_hostname": "host2.example.com",
            "remote_device_id": 2,
            "remote_port": "xe-0/0/1",
            "remote_platform": null,
            "remote_version": "Example Router v.1.0"
        },
        ...
    ],
    "count": 100
}
```

### `get_links`

Get a list of Links per giver device.

Route: `/api/v0/devices/:hostname/links`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/links
```

Output:

```json
{
    "status": "ok",
    "links": [
        {
            "id": 10,
            "local_port_id": 100,
            "local_device_id": 1,
            "remote_port_id": 200,
            "active": 1,
            "protocol": "lldp",
            "remote_hostname": "host2.example.com",
            "remote_device_id": 2,
            "remote_port": "xe-0/0/1",
            "remote_platform": null,
            "remote_version": "Example Router v.1.0"
        },
        ...
    ],
    "count": 10
}
```

### `get_link`

Retrieves Link by ID

Route: `/api/v0/resources/links/:id`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/links/10
```

Output:

```json
{
    "status": "ok",
    "links": [
        {
            "id": 10,
            "local_port_id": 100,
            "local_device_id": 1,
            "remote_port_id": 200,
            "active": 1,
            "protocol": "lldp",
            "remote_hostname": "host2.example.com",
            "remote_device_id": 2,
            "remote_port": "xe-0/0/1",
            "remote_platform": null,
            "remote_version": "Example Router v.1.0"
        }
    ],
    "count": 1
}
```

### `list_fdb`

Get a list of all ports FDB.

Route: `/api/v0/resources/fdb/:mac`

- mac is the specific MAC address you would like to query

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/fdb
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/fdb/1aaa2bbb3ccc
```

Output:

```json
{
    "status": "ok",
    "ports_fdb": [
        {
            "ports_fdb_id": 10,
            "port_id": 10000,
            "mac_address": "1aaa2bbb3ccc",
            "vlan_id": 20000,
            "device_id": 1,
            "created_at": "2019-01-1 01:01:01",
            "updated_at": "2019-01-1 01:01:01"
        },
        ...
    ],
    "count": 100
}
```


### `list_fdb_detail`

Get a list of all ports FDB with human readable device  and interface names.

Route: `/api/v0/resources/fdb/:mac/detail`

  - mac is the specific MAC address you would like to query

Input:

-

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/fdb/1aaa2bbb3ccc/detail
```

Output:
```json
{
    'count': 7,
    'mac': '9c:93:aa:bb:cc:dd',
    'mac_oui': 'Xerox Corporation',
    'ports_fdb': [
        {
            'hostname': 'hq-core1',
            'ifName': 'ae10',
            'last_seen': '2 hours ago',
            'updated_at': '2023-05-17 03:19:15'
        },
        {
            'hostname': 'hq-sw1',
            'ifName': 'ge-0/0/0',
            'last_seen': '3 hours ago',
            'updated_at': '2023-05-17 02:02:06'
        },
        ...
    ],
    'status': 'ok'
}
```
