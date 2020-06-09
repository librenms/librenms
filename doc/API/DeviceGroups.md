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

### `add_devicegroup`

Add a new device group. Upon success, the ID of the new device group is returned
and the HTTP response code is `201`.

Route: `/api/v0/devicegroups`

Input (JSON):

- `name`: *required* - The name of the device group
- `type`: *required* - should be `static` or `dynamic`. Setting this to static
  requires that the devices input be provided
- `desc`: *optional* - Description of the device group
- `rules`: *required if type == dynamic* - A set of rules to determine which
  devices should be included in this device group
- `devices`: *required if type == static* - A list of devices that should be
  included in this group. This is a static list of devices

Examples:

Dynamic Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' \
  -d '{"name": "New Device Group", \
       "desc": "A very fancy dynamic group", \
       "type": "dynamic",
       "rules": "{\"condition\":\"AND\",\"rules\":[{\"id\":\"access_points.name\",\"field\":\"access_points.name\",\"type\":\"string\",\"input\":\"text\",\"operator\":\"equal\",\"value\":\"accesspoint1\"}],\"valid\":true}"}' \
  https://librenms.org/api/v0/devicegroups
```

Output:

```json
{
    "status": "ok",
    "id": 86,
    "message": "Device group New Device Group created"
}
```

Static Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' \
  -X POST \
  -d '{"name":"New Device Group","type":"static","devices":[261,271]}' \
  https://librenms.org/api/v0/devicegroups
```

Output:

```json
{
    "status": "ok",
    "id": 86,
    "message": "Device group New Device Group created"
}
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
