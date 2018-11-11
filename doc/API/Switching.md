source: API/Switching.md
path: blob/master/doc/

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

Get a list of all or per hostname discovered Links.

Route: `/api/v0/resources/links`

Input:

  - hostname = Either the devices hostname or id

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/links
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/links?hostname=host.example.com
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

### `get_link`

Retrieves Link by ID

Route: `/api/v0/resources/links/:id`

Input:

-

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/link/10
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
