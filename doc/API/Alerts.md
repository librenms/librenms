### `get_alert`

Get details of an alert

Route: `/api/v0/alerts/:id`

- id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#function-list_alerts).

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/1
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "count": 7,
 "alerts": [
  {
   "hostname": "localhost",
   "id": "1",
   "device_id": "1",
   "rule_id": "1",
   "state": "1",
   "alerted": "1",
   "open": "1",
   "timestamp": "2014-12-11 14:40:02"
  }]
}
```

### `ack_alert`

Acknowledge an alert

Route: `/api/v0/alerts/:id`

- id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#function-list_alerts).
- note is the note to add to the alert
- until_clear is a boolean and if set to false, the alert will re-alert if it worsens/betters.

Input:

  -

Example:

```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/1
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "message": "Alert has been acknowledged"
}
```

### `unmute_alert`

Unmute an alert

Route: `/api/v0/alerts/unmute/:id`

- id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#function-list_alerts).

Input:

  -

Example:

```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/unmute/1
```

Output:

```json
{
 "status": "ok",
 "message": "Alert has been unmuted"
}
```

### `list_alerts`

List all alerts

Route: `/api/v0/alerts`

Input:

- state: Filter the alerts by state, 0 = ok, 1 = alert, 2 = ack
- severity: Filter the alerts by severity. Valid values are `ok`, `warning`, `critical`.
- alert_rule: Filter alerts by alert rule ID.
- order: How to order the output, default is by timestamp
  (descending). Can be appended by DESC or ASC to change the order.

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts?state=1
```

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts?severity=critical
```

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts?order=timestamp%20ASC
```

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts?alert_rule=49
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "count": 1,
 "alerts": [
  {
   "id": "1",
   "device_id": "1",
   "rule_id": "1",
   "state": "1",
   "alerted": "1",
   "open": "1",
   "timestamp": "2014-12-11 14:40:02"
  }]
}
```

## Rules

### `get_alert_rule`

Get the alert rule details.

Route: `/api/v0/rules/:id`

- id is the rule id.

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules/1
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "count": 1,
 "rules": [
  {
   "id": "1",
   "device_id": "1",
   "rule": "%devices.os != \"Juniper\"",
   "severity": "warning",
   "extra": "{\"mute\":true,\"count\":\"15\",\"delay\":null,\"invert\":false}",
   "disabled": "0",
   "name": "A test rule"
  }
 ]
}
```

### `delete_rule`

Delete an alert rule by id

Route: `/api/v0/rules/:id`

- id is the rule id.

Input:

  -

Example:

```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules/1
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "message": "Alert rule has been removed"
}
```

### `list_alert_rules`

List the alert rules.

Route: `/api/v0/rules`

  -

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:

```json
{
 "status": "ok",
 "message": "",
 "count": 1,
 "rules": [
  {
   "id": "1",
   "device_id": "-1",
   "rule": "%devices.os != \"Juniper\"",
   "severity": "critical",
   "extra": "{\"mute\":false,\"count\":\"15\",\"delay\":\"300\",\"invert\":false}",
   "disabled": "0",
   "name": "A test rule"
  }]
}
```

### `add_rule`

Add a new alert rule.

Route: `/api/v0/rules`

  -

Input (JSON):

- devices: This is either an array of device ids or -1 for a global rule
- builder: The rule which should be in the format entity.condition
  value (i.e devices.status != 0 for devices marked as down). It must
  be json encoded in the format rules are currently stored.
- severity: The severity level the alert will be raised against, Ok, Warning, Critical.
- disabled: Whether the rule will be disabled or not, 0 = enabled, 1 = disabled
- count: This is how many polling runs before an alert will trigger and the frequency.
- delay: Delay is when to start alerting and how frequently. The value
  is stored in seconds but you can specify minutes, hours or days by
  doing 5 m, 5 h, 5 d for each one.
- interval: How often to re-issue notifications while this alert is active,0 means notify once.The value
  is stored in seconds but you can specify minutes, hours or days by
  doing 5 m, 5 h, 5 d for each one.
- mute: If mute is enabled then an alert will never be sent but will
  show up in the Web UI (true or false).
- invert: This would invert the rules check.
- name: This is the name of the rule and is mandatory.
- notes: Some informal notes for this rule

Example:

```curl
curl -X POST -d '{"devices":[1,2,3], "name": "testrule", "builder":{"condition":"AND","rules":[{"id":"devices.hostname","field":"devices.hostname","type":"string","input":"text","operator":"equal","value":"localhost"}],"valid":true},"severity": "critical","count":15,"delay":"5 m","interval":"5 m","mute":false,"notes":"This a note from the API"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:

```json
{
 "status": "ok"
}
```

### `edit_rule`

Edit an existing alert rule

Route: `/api/v0/rules`

  -

Input (JSON):

- rule_id: You must specify the rule_id to edit an existing rule, if
  this is absent then a new rule will be created.
- devices: This is either an array of device ids or -1 for a global rule
- builder: The rule which should be in the format entity.condition
  value (i.e devices.status != 0 for devices marked as down). It must
  be json encoded in the format rules are currently stored.
- severity: The severity level the alert will be raised against, Ok, Warning, Critical.
- disabled: Whether the rule will be disabled or not, 0 = enabled, 1 = disabled
- count: This is how many polling runs before an alert will trigger and the frequency.
- delay: Delay is when to start alerting and how frequently. The value
  is stored in seconds but you can specify minutes, hours or days by
  doing 5 m, 5 h, 5 d for each one.
- interval: How often to re-issue notifications while this alert is active,0 means notify once.The value
  is stored in seconds but you can specify minutes, hours or days by
  doing 5 m, 5 h, 5 d for each one.
- mute: If mute is enabled then an alert will never be sent but will
  show up in the Web UI (true or false).
- invert: This would invert the rules check.
- name: This is the name of the rule and is mandatory.
- notes: Some informal notes for this rule

Example:

```curl
curl -X PUT -d '{"rule_id":1,"device_id":"-1", "name": "testrule", "builder":{"condition":"AND","rules":[{"id":"devices.hostname","field":"devices.hostname","type":"string","input":"text","operator":"equal","value":"localhost"}],"valid":true},"severity": "critical","count":15,"delay":"5 m","interval":"5 m","mute":false,"notes":"This a note from the API"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:

```json
{
 "status": "ok"
}
```
