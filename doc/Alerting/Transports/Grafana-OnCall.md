## Grafana Oncall

Send alerts to Grafana Oncall via either a Formatted Webhook or Webhook.
[See the Grafana documentation for both](https://grafana.com/docs/oncall/latest/integrations/webhook/).

There is little difference between the two, but the Formatted Webhook will 
provide a more friendly view of things by default.

> NOTE: By default Grafana translates acknowledged alerts to resolved alerts.
> This can be changed by updating the Template settings for the integration you
> added as follows.

Autoresolution: `{{ payload.get("raw_state", "") != 2 and payload.get("state", "").upper() == "OK" }}`

Auto acknowledge: `{{ payload.get("raw_state", "") == 2 }}`

You will also find additional information is sent as part of the payload to Grafana which 
can be useful within the templates or routes. If you perform a test of the LibreNMS transport 
you will be able to see the payload within the Grafana interface.

customise what is sent to Grafana and override or add additional fields, you can create
a custom template which outputs the correct information via JSON. As an example:

```
{
    "message": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
    "number_of_processors": \App\Models\Processors::where('device_id', $alert->device_id)->count(),
    "title": "{{ $alert->title }}",
    "link_to_upstream_details": "{{ \LibreNMS\Util\Url::deviceUrl($device) }}",
}
```
If you are using more than one transport for an alert rule and need to customise the output per
transport then you can do the following:

```
@if ($alert->transport == 'grafana')
{
  "message": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
  "number_of_processors": \App\Models\Processors::where('device_id', $alert->device_id)->count(),
  "title": "{{ $alert->title }}",
  "link_to_upstream_details": "{{ \LibreNMS\Util\Url::deviceUrl($device) }}",
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

**Example:**

| Config | Example |
| ------ | ------- |
| Webhook URL | https://a-prod-us-central-0.grafana.net/integrations/v1/formatted_webhook/m12xmIjOcgwH74UF8CN4dk0Dh/ |