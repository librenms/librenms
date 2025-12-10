### `get_all_port_voice_vlan`

Get all port voice vlan info

Route: `/api/v0/port_voice_vlan`

-

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_voice_vlan/
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
        {
          "ports_voice_vlan_id": "1",
          "device_id": "15",
          "port_id": "227",
          "voice_vlan": "102",
        },
        {
          "ports_voice_vlan_id": "2",
          "device_id": "15",
          "port_id": "155",
          "voice_vlan": "108",
        }
    ]
}
```

### `get_port_voice_vlan_by_port`

Get all port security info by inputting port_id

Route: `/api/v0/port_voice_vlan/port/:port_id`

- portid must be an integer

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_voice_vlan/227
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
         {
          "ports_voice_vlan_id": "1",
          "device_id": "15",
          "port_id": "227",
          "voice_vlan": "102",
        }
    ]
}
```

### `get_port_voice_vlan_by_hostname`

Get all port security info by inputting device_id or hostname

Route: `/api/v0/port_voice_vlan/:hostname`

- hostname can be str hostname or int device_id

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_voice_vlan/device/switch1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_voice_vlan/device/5
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
         {
          "ports_voice_vlan_id": "1",
          "device_id": "5",
          "port_id": "200",
          "voice_vlan": "102",
        },
         {
          "ports_voice_vlan_id": "2",
          "device_id": "5",
          "port_id": "201",
          "voice_vlan": "102",
        },
        ...
         {
          "ports_voice_vlan_id": "10",
          "device_id": "5",
          "port_id": "210",
          "voice_vlan": "102",
        },

    ]
}
```
