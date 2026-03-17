### `get_alert`

Get details of an alert

Route: `/api/v0/alerts/:id`

- id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#list_alerts).

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/1
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

- id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#list_alerts).
- note is the note to add to the alert
- until_clear is a boolean and if set to false, the alert will re-alert if it gets worse/better or changes.

Input:

  -

Example:

```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/1
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

- id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#list_alerts).

Input:

  -

Example:

```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/unmute/1
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
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts?state=1
```

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts?severity=critical
```

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts?order=timestamp%20ASC
```

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts?alert_rule=49
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
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/rules/1
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
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/rules/1
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
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/rules
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
curl -X POST -d '{"devices":[1,2,3], "name": "testrule", "builder":{"condition":"AND","rules":[{"id":"devices.hostname","field":"devices.hostname","type":"string","input":"text","operator":"equal","value":"localhost"}],"valid":true},"severity": "critical","count":15,"delay":"5 m","interval":"5 m","mute":false,"notes":"This a note from the API"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/rules
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
curl -X PUT -d '{"rule_id":1,"device_id":"-1", "name": "testrule", "builder":{"condition":"AND","rules":[{"id":"devices.hostname","field":"devices.hostname","type":"string","input":"text","operator":"equal","value":"localhost"}],"valid":true},"severity": "critical","count":15,"delay":"5 m","interval":"5 m","mute":false,"notes":"This a note from the API"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/rules
```

Output:

```json
{
 "status": "ok"
}
```

## Scheduled maintenance

### `list_scheduled_maintenance`

List all scheduled maintenances.

Route: `/api/v0/alerts/scheduled_maintenance`

Input: None

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/scheduled_maintenance
```

Output:

```json
{
  "status": "ok",
  "schedule": [
    {
      "schedule_id": 1,
      "title": "Planned maintenance",
      "notes": "Monthly maintenance window",
      "recurring": 0,
      "start": "2026-03-17 00:00:00",
      "end": "2026-03-17 06:00:00",
      "start_recurring_dt": "2026-03-17",
      "start_recurring_hr": "00:00",
      "end_recurring_dt": "2026-03-17",
      "end_recurring_hr": "06:00",
      "recurring_day": ["Mo"],
      "status": 1,
      "devices": [1, 2],
      "device_groups": [1],
      "locations": []
    }
  ],
  "count": 1
}
```

### `get_scheduled_maintenance`

Get details of a specific alert maintenance schedule.

Route: `/api/v0/alerts/scheduled_maintenance/:id`

- id is the scheduled maintenance id, obtainable from [`list_scheduled_maintenance`](#list_scheduled_maintenance).

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/scheduled_maintenance/1
```

Output:

```json
{
  "status": "ok",
  "schedule": {
    "schedule_id": 1,
    "title": "Planned maintenance",
    "notes": "Monthly maintenance window",
    "recurring": 0,
    "start": "2026-03-17 00:00:00",
    "end": "2026-03-17 06:00:00",
    "start_recurring_dt": "2026-03-17",
    "start_recurring_hr": "00:00",
    "end_recurring_dt": "2026-03-17",
    "end_recurring_hr": "06:00",
    "recurring_day": ["Mo"],
    "status": 1,
    "devices": [1, 2],
    "device_groups": [1],
    "locations": []
  }
}
```

### `add_scheduled_maintenance`

Create a new scheduled maintenance.

Route: `/api/v0/alerts/scheduled_maintenance`

Input (JSON):

- title: (Required) Title of the maintenance schedule.
- notes: Optional notes for this maintenance schedule.
- behavior: Optional integer controlling maintenance behavior (`1`, `2`, or `3`).
- recurring: Whether this is a recurring schedule (boolean, default `false`).
- **When `recurring` is `false`** (non-recurring):
  - start: (Required) Start datetime in `Y-m-d H:i:s` format.
  - end: (Required) End datetime in `Y-m-d H:i:s` format. Must be after `start`.
- **When `recurring` is `true`**:
  - start_recurring_dt: (Required) Start date in `Y-m-d` format.
  - start_recurring_hr: (Required) Start time in `H:i` format.
  - end_recurring_hr: (Required) End time in `H:i` format. May be before `start_recurring_hr` to span midnight.
  - end_recurring_dt: Optional end date in `Y-m-d` format (no end date if omitted).
  - recurring_day: Optional array of day abbreviations. Valid values: `Mo`, `Tu`, `We`, `Th`, `Fr`, `Sa`, `Su`.
- **Targets** (at least one is required):
  - devices: Array of device IDs.
  - device_groups: Array of device group IDs.
  - locations: Array of location IDs.

Example (non-recurring):

```curl
curl -X POST -d '{"title":"Planned maintenance","notes":"Monthly maintenance window","devices":[1,2],"start":"2026-03-17 00:00:00","end":"2026-03-17 06:00:00"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/scheduled_maintenance
```

Example (recurring):

```curl
curl -X POST -d '{"title":"Weekly maintenance","recurring":true,"start_recurring_dt":"2026-03-17","start_recurring_hr":"22:00","end_recurring_hr":"06:00","recurring_day":["Mo","Tu","We","Th","Fr"],"device_groups":[1]}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/scheduled_maintenance
```

Output:

```json
{
  "status": "ok",
  "message": "Scheduled maintenance created",
  "schedule_id": 1
}
```

### `delete_scheduled_maintenance`

Delete an alert maintenance schedule.

Route: `/api/v0/alerts/scheduled_maintenance/:id`

- id is the scheduled maintenance id, obtainable from [`list_scheduled_maintenance`](#list_scheduled_maintenance).

Input:

  -

Example:

```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alerts/scheduled_maintenance/1
```

Output:

```json
{
  "status": "ok",
  "message": "Scheduled maintenance deleted"
}
```

## Alert templates

### `get_alert_template`

Get the alert template details.

Route: `/api/v0/alert_templates/:id`



Input:

  - id: (Required) is the alert template id.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alert_templates/1
```

Output:

```json
{
  "status": "ok",
  "alert_templates": [
    {
      "id": 4,
      "name": "Default Alert Template",
      "template": "{{ $alert->title }}\nSeverity: {{ $alert->severity }}\n@if ($alert->state == 0)Time elapsed: {{ $alert->elapsed }} @endif\nTimestamp: {{ $alert->timestamp }}\nUnique-ID: {{ $alert->uid }}\nRule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif\n@if ($alert->faults) Faults:\n@foreach ($alert->faults as $key => $value)\n  #{{ $key }}: {{ $value['string'] }}\n@endforeach\n@endif\nAlert sent to:\n@foreach ($alert->contacts as $key => $value)\n  {{ $value }} <{{ $key }}>\n@endforeach",
      "title": null,
      "title_rec": null,
      "alert_rules": []
    },
  ],
  "count": 1
}
```

### `list_alert_templates`

List the alert templates.

Route: `/api/v0/alert_templates`


Input: None

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alert_templates
```

Output:

```json
{
  "status": "ok",
  "alert_templates": [
    {
      "id": 4,
      "name": "Default Alert Template",
      "template": "{{ $alert->title }}\nSeverity: {{ $alert->severity }}\n@if ($alert->state == 0)Time elapsed: {{ $alert->elapsed }} @endif\nTimestamp: {{ $alert->timestamp }}\nUnique-ID: {{ $alert->uid }}\nRule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif\n@if ($alert->faults) Faults:\n@foreach ($alert->faults as $key => $value)\n  #{{ $key }}: {{ $value['string'] }}\n@endforeach\n@endif\nAlert sent to:\n@foreach ($alert->contacts as $key => $value)\n  {{ $value }} <{{ $key }}>\n@endforeach",
      "title": null,
      "title_rec": null,
      "alert_rules": []
    },
  ],
  "count": 1
}
```

### `add_alert_template`

Add a new alert template.

Route: `/api/v0/alert_templates`

Input (JSON):

- name: (Required) Name for the new template
- template: (Required) Template code used to generate the alert message
- title: Title that is used when an alert is generated
- title_rec: Title that is used when an alert has recovered
- alert_rules: an array of rule_id's for which this template should apply (see also: [`list_alert_rules`](#list_alert_rules).)

Example:

```curl
curl -X POST -d '{"name":"new alert template","template":"---","title":"CREATED ALERT","title_rec": "ALERT RECOVERED","alert_rules":[]}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alert_templates
```

Output:
- status: Status of the request. Can be: ok, warning, error
- message: The output of this call. Error messages will be displayed here.
- id: The id of the newly created alert template

```json
{
  "status": "ok",
  "message": "Alert template has been created and attached rules have been updated.",
  "id": 2
}
```

### `edit_rule`

Edit an existing alert rule

Route: `/api/v0/alert_templates`

Input (JSON):

- name: (Required) Name for the new template
- template: (Required) Template code used to generate the alert message
- template_id: (Required) template id that will be changed. If this is not present a new alert template will be created.
- title: Title that is used when an alert is generated
- title_rec: Title that is used when an alert has recovered
- alert_rules: an array of rule_id's for which this template should apply (see also: [`list_alert_rules`](#list_alert_rules).)

Example:

```curl
curl -X POST -d '{"name":"new alert template","template":"---","template_id":"2","title":"CREATED ALERT","title_rec": "ALERT RECOVERED","alert_rules":[]}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/alert_templates
```

Output:

```json
{
  "status": "ok",
  "message": "Alert template has been updated and attached rules have been updated.",
  "id": 2
}
```
