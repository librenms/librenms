###`get_poller_group`

Get one poller group. Without a name, it returns all poller groups.

Route: `/api/v0/poller_group/:poller_group`

- poller_group: optional. The name or the id of the poller group.

Output:

```json
{
    "status": "ok",
    "get_poller_group": [
        {
            "id": 1,
            "group_name": "test",
            "descr": "test group"
        }
    ],
    "count": 1
}
```
