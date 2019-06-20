source: API/DeviceGroups.md
path: blob/master/doc/

### `get_devicegroups`

List all device groups.

Route: `/api/v0/devicegroups`

Input (JSON):

  -

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devicegroups
```

Output:

```json
[
    {
        "status": "ok",
        "message": "Found 1 device groups",
        "count": 1,
        "groups": [
        {
            "id": "1",
            "name": "Testing",
            "desc": "Testing",
            "pattern": "%devices.status = \"1\" &&"
        }
        ]
    }
]
```

### `get_devices_by_group`

List all devices matching the group provided.

Route: `/api/v0/devicegroups/:name`

- name Is the name of the device group which can be obtained using
  [`get_devicegroups`](#function-get_devicegroups). Please ensure that
  the name is urlencoded if it needs to be (i.e Linux Servers would
  need to be urlencoded.

Input (JSON):

- full: set to any value to return all data for the devices in a given group

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devicegroups/LinuxServers
```

Output:

```json
[
     {
         "status": "ok",
         "message": "Found 3 in group LinuxServers",
         "count": 3,
         "devices": [
            {
                "device_id": "15"
            },
            {
                "device_id": "18"
            },
            {
                "device_id": "20"
            }
         ]
     }
]
```
