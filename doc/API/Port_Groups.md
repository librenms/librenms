source: API/Port_Groups.md
path: blob/master/doc/

### `get_portgroups`

List all port groups.

Route: `/api/v0/portgroups`

Input (JSON):

  -

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/portgroups
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

### `add_portgroup`

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

### `assign_portgroup`

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

### `remove_portgroup`

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

