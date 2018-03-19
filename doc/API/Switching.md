source: API/Switching.md

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
