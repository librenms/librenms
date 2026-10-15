### `get_devicegroups`

List all device groups.

Route: `/api/v0/devicegroups`

Input (JSON):

  -

Examples:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' https://foo.example/api/v0/devicegroups
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

- `name`: *required* - The name of the device group. It must not end in
  `/devices` or `/maintenance`, see
  [Reserved device group names](#reserved-device-group-names)
- `type`: *required* - `static` or `dynamic`. The value static
  needs the devices input
- `desc`: *optional* - Description of the device group
- `rules`: *required if type == dynamic* - a set of rules. These rules
  select the devices of this device group
- `devices`: *present if type == static* - a static list of the devices
  in this group

Examples:

Dynamic Example:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' \
  -X POST https://foo.example/api/v0/devicegroups \
  --data-raw '
{
 "name": "New Device Group", 
 "desc": "A very fancy dynamic group",
 "type": "dynamic", 
 "rules": "{\"condition\":\"AND\",\"rules\":[{\"id\":\"access_points.name\",\"field\":\"access_points.name\",\"type\":\"string\",\"input\":\"text\",\"operator\":\"equal\",\"value\":\"accesspoint1\"}],\"valid\":true}"
}
'
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
curl -H 'Authorization: Bearer YOURAPITOKENHERE' \
  -X POST https://foo.example/api/v0/devicegroups \
  -d '{"name":"New Device Group","type":"static","devices":[261,271]}'
```

Output:

```json
{
    "status": "ok",
    "id": 86,
    "message": "Device group New Device Group created"
}
```

### `update_devicegroup`

Updates a device group.

Route: `/api/v0/devicegroups/:name`

- name Is the name or id of the device group which can be obtained using
  [`get_devicegroups`](#get_devicegroups). Urlencode the name where
  necessary. For example, `Linux Servers` needs urlencoding and
  `Site A/Core` is sent as `Site%20A%2FCore`. See
  [Reserved device group names](#reserved-device-group-names).

Input (JSON):

- `name`: *optional* - The name of the device group. It must not end in
  `/devices` or `/maintenance`, see
  [Reserved device group names](#reserved-device-group-names)
- `type`: *optional* - `static` or `dynamic`. The value static
  needs the devices input
- `desc`: *optional* - Description of the device group
- `rules`: *required if type == dynamic* - a set of rules. These rules
  select the devices of this device group
- `devices`: *required if type == static* - a static list of the
  devices in this group

Examples:

```curl
curl -X PATCH -d '{"name": "NewLinuxServers"}' -H 'Authorization: Bearer YOURAPITOKENHERE' https://foo.example/api/v0/devicegroups/LinuxServers
```

Output:

```json
{
    "status": "ok",
    "message": "Device group LinuxServers updated"
}
```

### `delete_devicegroup`

Deletes a device group.

Route: `/api/v0/devicegroups/:name`

- name Is the name or id of the device group which can be obtained using
  [`get_devicegroups`](#get_devicegroups). Urlencode the name where
  necessary. For example, `Linux Servers` needs urlencoding and
  `Site A/Core` is sent as `Site%20A%2FCore`. See
  [Reserved device group names](#reserved-device-group-names).

Input:

-

Examples:

```curl
curl -X DELETE -H 'Authorization: Bearer YOURAPITOKENHERE' https://foo.example/api/v0/devicegroups/LinuxServers
```

Output:

```json
{
    "status": "ok",
    "message": "Device group LinuxServers deleted"
}
```

### `get_devices_by_group`

List all devices matching the group provided.

Route: `/api/v0/devicegroups/:name`

- name Is the name or id of the device group which can be obtained using
  [`get_devicegroups`](#get_devicegroups). Urlencode the name where
  necessary. For example, `Linux Servers` needs urlencoding and
  `Site A/Core` is sent as `Site%20A%2FCore`. See
  [Reserved device group names](#reserved-device-group-names).

Input (JSON):

- full: set to any value to return all data for the devices in a given group

Examples:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' https://foo.example/api/v0/devicegroups/LinuxServers
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

### `maintenance_devicegroup`

Set a device group into maintenance mode.

Route: `/api/v0/devicegroups/:name/maintenance`

- name Is the name or id of the device group which can be obtained using
  [`get_devicegroups`](#get_devicegroups). Urlencode the name where
  necessary. For example, `Cisco switches` needs urlencoding and
  `Site A/Core` is sent as `Site%20A%2FCore`. See
  [Reserved device group names](#reserved-device-group-names).

Input (JSON):

- `title`: *optional* - Some title for the Maintenance
  Without this field, LibreNMS uses the device group name
- `behavior`: *optional* - id of maintenance behavior desired
  Defaults to alert.scheduled_maintenance_default_behavior if omitted
- `notes`: *optional* - Some description for the Maintenance
- `start`: *optional* - start time of Maintenance in full format `Y-m-d H:i:00`
  eg: 2022-08-01 22:45:00
  Without this field, LibreNMS uses the current system time `now()`
- `duration`: *required* - Duration of Maintenance in format `H:i` / `Hrs:Mins`
  eg: 02:00

Example with start time:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' \
  -X POST https://foo.example/api/v0/devicegroups/Cisco%20switches/maintenance/ \
  --data-raw '
{
 "title":"Device group Maintenance",
  "notes":"A 2 hour Maintenance triggered via API with start time",
  "start":"2022-08-01 08:00:00",
  "duration":"2:00"
}
'
```

Output:

```json
{
    "status": "ok",
    "message": "Device group Cisco switches (2) will begin maintenance mode at 2022-08-01 22:45:00 for 2:00h"
}
```

Example with no start time:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' \
  -X POST https://foo.example/api/v0/devicegroups/Cisco%20switches/maintenance/ \
  --data-raw '
{
 "title":"Device group Maintenance",
  "notes":"A 2 hour Maintenance triggered via API with no start time",
  "duration":"2:00"
}
'
```

Output:

```json
{
    "status": "ok",
    "message": "Device group Cisco switches (2) moved into maintenance mode for 2:00h"
}
```

### Add devices to group

Add devices to a device group.

Route: `/api/v0/devicegroups/:name/devices`

- name Is the name or id of the device group which can be obtained using
  [`get_devicegroups`](#get_devicegroups). Urlencode the name where
  necessary. For example, `Linux Servers` needs urlencoding and
  `Site A/Core` is sent as `Site%20A%2FCore`. See
  [Reserved device group names](#reserved-device-group-names).

Input (JSON):

- `devices`: *required* - A list of device ids to be added to the
  group. A request without it is rejected with `422`.

Example:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' \
  -X POST https://foo.example/api/v0/devicegroups/LinuxServers/devices \
  --data-raw '{"devices":[261,271]}'
```

Output:

```json
{
    "status": "ok",
    "message": "Devices added"
}
```

### Remove devices from group

Removes devices from a device group.

Route: `/api/v0/devicegroups/:name/devices`

- name Is the name or id of the device group which can be obtained using
  [`get_devicegroups`](#get_devicegroups). Urlencode the name where
  necessary. For example, `Linux Servers` needs urlencoding and
  `Site A/Core` is sent as `Site%20A%2FCore`. See
  [Reserved device group names](#reserved-device-group-names).

Input (JSON):

- `devices`: *required* - A list of device ids to be removed from the
  group. A request without it is rejected with `422`.

Example:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' \
  -X DELETE https://foo.example/api/v0/devicegroups/LinuxServers/devices \
  --data-raw '{"devices":[261,271]}'
```

Output:

```json
{
    "status": "ok",
    "message": "Devices removed"
}
```

### Reserved device group names

A `/` in a device group name is decoded before the route is matched, so
`/api/v0/devicegroups/Core%2Fdevices` is the same request as
`/api/v0/devicegroups/Core/devices`. For that reason the API rejects new
device group names ending in `/devices` or `/maintenance`.

A group that already has such a name, for example one created in the
web UI, can still be fetched and updated by name. It can be deleted by
name too, except when its name ends in `/devices`: `DELETE
/api/v0/devicegroups/Core%2Fdevices` removes devices from the group
`Core`, so delete such a group by its id.

If no group has the name and it ends in `/devices` or `/maintenance`,
the request is treated as a sub-route called with the wrong method and
returns `404` "This API route doesn't exist.".
