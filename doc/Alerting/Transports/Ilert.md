## Ilert
This integration uses the [ilert LibreNMS
integration](https://docs.ilert.com/integrations/inbound-integrations/librenms).
It accepts all ilert parameters, such as links, images, and comments.

This transport sends these fields:

`integrationKey` - the integration key from the earlier step.
`eventType` - the alert type, that is Alerting, Acknowledged, or
Recovered, converted to an ilert event type.
`summary` - the title of the alert.
`details` - the output of the alert template of the rule.
`alertKey` - the alert id.
`priority` - the priority as an ilert priority value. HIGH is critical.
LOW is warning or OK.

To change the data to ilert, or to override or add fields, create your
own template. This template gives the correct information in JSON. It
**must** send a summary value and a details value. For example:

```json
{
    "summary": "{{ $alert->title }}",
    "details": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
    "links": [
        {
            "href": "{{ route('device', ['device' => $alert->device_id ?: 1]) }}",
            "text": "{{ $alert->hostname }}"
        },
        {
            "href": "{{ route('device', ['device' => $alert->device_id ?? 1, 'tab' => 'alerts']) }}",
            "text": "{{ $alert->hostname }} - Alerts"
        }
    ],
    "images": [
        {
            "src": "@signedGraphUrl(['device' => $alert->device_id, 'type' => 'device_availability','from' => time() - 43200, 'to' => time()])",
            "href": "{{ route('device', ['device' => $alert->device_id ?: 1]) }}",
            "alt": ""
        }
    ]
}
```
If an alert rule uses more than one transport, you can change the
output for each transport. Use this method:

```
@if ($alert->transport == 'ilert')
{
    "summary": "{{ $alert->title }}",
    "details": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
    "links": [
        {
            "href": "{{ route('device', ['device' => $alert->device_id ?: 1]) }}",
            "text": "{{ $alert->hostname }}"
        },
        {
            "href": "{{ route('device', ['device' => $alert->device_id ?? 1, 'tab' => 'alerts']) }}",
            "text": "{{ $alert->hostname }} - Alerts"
        }
    ],
    "images": [
        {
            "src": "@signedGraphUrl(['device' => $alert->device_id, 'type' => 'device_availability','from' => time() - 43200, 'to' => time()])",
            "href": "{{ route('device', ['device' => $alert->device_id ?: 1]) }}",
            "alt": ""
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
| Integration Key          | il1api012962aba7f1bff64b56a353a19b41c5f6ae57a940123f |
