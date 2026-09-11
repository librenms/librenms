## Grafana Oncall

This transport sends alerts to Grafana OnCall with a formatted webhook
or with a webhook. For both methods, read [the Grafana
documentation](https://grafana.com/docs/oncall/latest/integrations/webhook/).
The difference is small. The formatted webhook gives a clearer default
view.

> NOTE: by default, Grafana converts an acknowledged alert to a resolved
> alert. To change this behaviour, update the Template settings of your
> integration as below.

Autoresolution: `{{ payload.get("raw_state", "") != 2 and payload.get("state", "").upper() == "OK" }}`

Auto acknowledge: `{{ payload.get("raw_state", "") == 2 }}`

The payload to Grafana holds more information. This information is
useful in the templates and the routes. Run a test of the LibreNMS
transport to see the payload in the Grafana interface.

To change the data to Grafana, or to override or add fields, create
your own template. This template gives the correct information in JSON.
For example:

```
{
    "message": "Severity: {{ $alert->severity }}\nTimestamp: {{ $alert->timestamp }}\nRule: {{ $alert->title }}\n @foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}\n @endforeach",
    "number_of_processors": \App\Models\Processors::where('device_id', $alert->device_id)->count(),
    "title": "{{ $alert->title }}",
    "link_to_upstream_details": "{{ \LibreNMS\Util\Url::deviceUrl($device) }}",
}
```
If an alert rule uses more than one transport, you can change the
output for each transport. Use this method:

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