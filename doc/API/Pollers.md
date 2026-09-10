### `list_pollers`

List all pollers in the system. It returns the data of the
`poller_cluster` table. Without that table, it returns the `pollers`
table with its statistics.

Route: `/api/v0/pollers`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/pollers
```

Output (pollers table):

```json
{
    "status": "ok",
    "pollers": [
        {
            "id": 1,
            "poller_name": "localhost",
            "last_polled": "2025-01-15 10:30:00",
            "devices": 50,
            "time_taken": 120.5
        }
    ],
    "count": 1
}
```

Output (poller_cluster with stats):

```json
{
    "status": "ok",
    "pollers": [
        {
            "id": 1,
            "node_id": "abc123",
            "poller_name": "poller1",
            "poller_version": "24.1.0",
            "poller_groups": "0",
            "last_report": "2025-01-15T10:30:00.000000Z",
            "master": 1,
            "stats": [
                {
                    "id": 1,
                    "parent_poller": 1,
                    "poller_type": "poller",
                    "depth": 0,
                    "devices": 50,
                    "worker_seconds": 120.5,
                    "workers": 16,
                    "frequency": 300
                }
            ]
        }
    ],
    "count": 1
}
```

### `list_poller_log`

List all devices with their polling information. It returns the last
poll time, the poll duration, and the poller group. It shows only the
active devices of the user.

Route: `/api/v0/pollers/log`

Input:

- `unpolled` (optional): it shows only the devices without a recent
  poll. The limit is 1.2 times the `rrd.step` value. The default is 300
  seconds

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/pollers/log
```

Example with unpolled filter:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/pollers/log?unpolled=1
```

Output:

```json
{
    "status": "ok",
    "log": [
        {
            "hostname": "router1.example.com",
            "display_name": "Router 1",
            "last_polled": "2025-01-15 10:30:00",
            "last_polled_timetaken": 2.45,
            "poller_group": "General",
            "poller_group_id": 0
        },
        {
            "hostname": "switch1.example.com",
            "display_name": "Switch 1",
            "last_polled": "2025-01-15 10:29:45",
            "last_polled_timetaken": 1.23,
            "poller_group": "Data Center",
            "poller_group_id": 1
        }
    ],
    "count": 2
}
```
