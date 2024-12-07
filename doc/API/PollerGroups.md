###`get_poller_group`

Gets a specific poller group or all if none is specified

Route: `/api/v0/poller_group/:poller_group`

- poller_group: optional name or id of the poller group to get

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
