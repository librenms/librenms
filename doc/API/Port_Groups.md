source: API/Port_Groups.md
path: blob/master/doc/

### `get_port_groups`

List all port groups.

Route: `/api/v0/port_groups`

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_groups
```

Output:

```json
[
    {
        "status": "ok",
        "message": "Found 1 port groups",
        "count": 1,
        "groups": [
        {
            "id": "1",
            "name": "Testing",
            "desc": "Testing"
        }
        ]
    }
]
```

### `get_ports_by_group`

List all ports matching the group provided.

Route: `/api/v0/port_groups/:name`

- name Is the name of the port group which can be obtained using
  [`get_port_groups`](#function-get_port_groups). Please ensure that
  the name is urlencoded if it needs to be (i.e Linux Servers would
  need to be urlencoded.

Params:

- full: set to any value to return all data for the devices in a given group

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_groups/Billable
```

Output:

```json
{
    "status": "ok",
    "ports": [
        {
            "port_id": 1376
        },
        {
            "port_id": 2376
        }
    ],
    "count": 2
}
```

### `add_port_group`

Add a new port group. Upon success, the ID of the new port group is returned
and the HTTP response code is `201`.

Route: `/api/v0/port_groups`

Input (JSON):

- `name`: *required* - The name of the port group
- `desc`: *optional* - Description of the port group

Examples:

Dynamic Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' \
  -X POST \
  -d '{"name": "New Port Group", \
       "desc": "A very fancy port group"}' \
  https://librenms.org/api/v0/port_groups
```

Output:

```json
{
    "status": "ok",
    "id": 86,
    "message": "Port group New Port Group created"
}
```

### `assign_port_group`

Assign a Port Group to a list of Ports

Route: `/api/v0/port_groups/:port_group_id/assign`

Input (JSON):

- `port_ids`: *required* - List of Port Ids

Examples:

Dynamic Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' -X POST -d '{"port_ids": ["4","34","25,"983"]}' https://librenms.org/api/v0/port_groups/3/assign
```

Output:

```json
{
    "status": "ok",
    "Port Ids 4, 34, 25, 983 have been added to Port Group Id 3": 200
}
```

### `remove_port_group`

Remove a Port Group from a list of Ports

Route: `/api/v0/port_groups/:port_group_id/remove`

Input (JSON):

- `port_ids`: *required* - List of Port Ids

Examples:

Dynamic Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' -X POST -d '{"port_ids": ["4","34","25,"983"]}' https://librenms.org/api/v0/port_groups/3/remove
```

Output:

```json
{
    "status": "ok",
    "Port Ids 4, 34, 25, 983 have been removed from Port Group Id 3": 200
}
```

