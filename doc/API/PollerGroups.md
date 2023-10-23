###`get_poller_group`

Gets a specific poller group

Route: `/api/v0/poller_group/:poller_group`

- poller_group: name or id of the poller group to get

Output:

```json
{
    "status": "ok",
    "get_location": [
        {
            "id": 1,
            "group_name": "test",
            "descr": "test group"
        }
    ],
    "count": 1
}
```
