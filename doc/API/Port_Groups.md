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
