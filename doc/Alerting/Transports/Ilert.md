## Ilert
This integration uses the [ilert LibreNMS integration](https://docs.ilert.com/integrations/inbound-integrations/librenms) 
which allows you to use all available ilert parameters such as links, images, comments, etc.

This transport will send over the following fields:

`integrationKey` - The integration key you generated earlier.
`eventType` - The type of alert such as Alerting, Acknowledged or Recovered translated to ilert event types.
`summary` - The title of the alert.
`details` - The output from the alert template associated with the rule.
`alertKey` - The alert id.
`priority` - The priority translated to the ilert priority values of HIGH (Critical) or LOW (Warning or OK)

To customise what is sent to ilert and override or add additional fields, you can create a custom template which outputs the correct information via JSON. For this to work you **must** send over a summary and details values. As an example:

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
If you are using more than one transport for an alert rule and need to customise the output per transport then you can do the following:

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
