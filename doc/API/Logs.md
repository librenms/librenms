source: API/Logs.md
path: blob/master/doc/

All the `list_*logs` calls are aliased to `list_logs`.

Retrieve all logs or logs for a specific device.

- id or hostname is the specific device

Input:

- start: The page number to request.
- limit: The limit of results to be returned.
- from: The date and time or the event id to search from.
- to: The data and time or the event id to search to.

### `list_eventlog`

Route: `/api/v0/logs/eventlog/:hostname`

### `list_syslog`

Route: `/api/v0/logs/syslog/:hostname`

### `list_alertlog`

Route: `/api/v0/logs/alertlog/:hostname`

### `list_authlog`

Route: `/api/v0/logs/authlog/:hostname`

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/logs/eventlog/:hostname
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/logs/syslog/:hostname?limit=20
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/logs/eventlog/:hostname?limit=20&start=5&from=2017-07-22%2023:00:00
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 5,
    "total": "15",
    "logs": [
        {
            "hostname": "localhost",
            "sysName": "web01.1.novalocal",
            "event_id": "10050349",
            "host": "279",
            "device_id": "279",
            "datetime": "2017-07-22 19:57:47",
            "message": "ifAlias:  ->  <pptp-something-something-tunnel-something>",
            "type": "interface",
            "reference": "NULL",
            "username": "",
            "severity": "3"
        },
        ....
        {
            "hostname": "localhost",
            "sysName": "web01.1.novalocal",
            "event_id": "10050353",
            "host": "279",
            "device_id": "279",
            "datetime": "2017-07-22 19:57:47",
            "message": "ifHighSpeed:  ->  0",
            "type": "interface",
            "reference": "NULL",
            "username": "",
            "severity": "3"
        }
    ]
}
```
