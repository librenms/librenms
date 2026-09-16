## Zenduty

There are two options for ZenDuty support. The first option is [native
ZenDuty](#native-zenduty) through the API transport. The official
[ZenDuty integration
documentation](https://docs.zenduty.com/docs/librenms) describes it.
The second option is a [native LibreNMS
transport](#native-librenms-transport).

### Native ZenDuty
The Zenduty integration sends new LibreNMS alerts to the correct team.
It notifies them from the on-call schedules by email, SMS, phone call,
Slack, Microsoft Teams, and mobile push notification. Zenduty gives
engineers detailed context about the alert. It also gives playbooks and
an incident command framework. Engineers can then triage and correct
the incidents quickly.

In [Zenduty](https://www.zenduty.com), create a [LibreNMS
integration](https://docs.zenduty.com/docs/librenms). Then copy the
webhook URL from Zenduty to LibreNMS.

For a full guide with screenshots, read the [LibreNMS documentation at
Zenduty](https://docs.zenduty.com/docs/librenms).

**Example:**

| Config | Example |
| ------ | ------- |
| WebHook URL | <https://www.zenduty.com/api/integration/librenms/integration-key/> |

### Native LibreNMS Transport
This integration uses the [ZenDuty
webhooks](https://zenduty.com/docs/generic-integration/). It accepts
all ZenDuty parameters, such as URLs, SLA, and escalation policies.

Obey the instructions in the link above to get your webhook URL. Then
paste it into the `ZenDuty WebHook` field of the LibreNMS transport.

You can also set the SLA ID and the escalation policy ID in the
transport configuration. LibreNMS then sends them with all alerts.

This transport sends these fields:

`message` - the alert title.
`alert_type` - the severity of the alert rule. It is acknowledged or
resolved, from the state of the alert.
`entity_id` - the alert ID.
`urls` - a link to the device of the alert.
`summary` - the output of the template of the alert rule.

To change the data to ZenDuty, or to override or add fields, create
your own template. This template gives the correct information in JSON.
For example:

```json
{
    "message": "{{ $alert->title }}",
    "payload": {
        "sysName": "{{ $alert->sysName }}",
        "Device Type": "{{ $alert->type }}"
    },
    "summary": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
    "sla": "ccaf3fd6-db51-4f9f-818b-de42aee54f29",
    "urls": [
        {
            "link_url": "{{ route('device', ['device' => $alert->device_id ?: 1]) }}",
            "link_text": "{{ $alert->hostname }}"
        },
        {
            "link_url": "{{ route('device', ['device' => $alert->device_id ?? 1, 'tab' => 'alerts']) }}",
            "link_text": "{{ $alert->hostname }} - Alerts"
        }
    ]
}
```
If an alert rule uses more than one transport, you can change the
output for each transport. Use this method:

```
@if ($alert->transport == 'ZenDuty')
{
  "message": "{{ $alert->title }}",
  "payload": {
    "sysName": "{{ $alert->sysName }}",
    "Device Type": "{{ $alert->type }}"
  },
  "summary": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
  "sla": "ccaf3fd6-db51-4f9f-818b-de42aee54f29",
  "urls": [
    {
      "link_url": "{{ route('device', ['device' => $alert->device_id ?: 1]) }}",
      "link_text": "{{ $alert->hostname }}"
    },
    {
      "link_url": "{{ route('device', ['device' => $alert->device_id ?? 1, 'tab' => 'alerts']) }}",
      "link_text": "{{ $alert->hostname }} - Alerts"
    }
  ]
}
@else
{{ $alert->title }}
Severity: {{ $alert->severity }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Unique-ID: {{ $alert->uid }}
Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif
@if ($alert->faults) Faults:
@foreach ($alert->faults as $key => $value)
  {{ $key }}: {{ $value['string'] }}
@endforeach
@endif
Alert sent to:
@foreach ($alert->contacts as $key => $value)
  {{ $value }} <{{ $key }}>
@endforeach
@endif
```

| Config               | Example                                                      |
|----------------------|--------------------------------------------------------------|
| WebHook URL          | <https://events.zenduty.com/integration/we8jv/generic/hash/> |
| SLA ID               | g27u4gr824r-dd32rf2wdedeas-3e2wd223d23                       |
| Escalation Policy ID | KIJDi23rwnef23-dankjd323r-DSAD£2232fds                        |