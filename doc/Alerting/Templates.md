# Templates

You can assign a template to one rule or to a group of rules. A
template holds any text. A default template also exists. LibreNMS uses
this default template for each rule without its own template. The
default template is on the `Alert Templates` page, and you can edit it.
An option resets it to its original content.

To attach a template to a rule, open the `Alert Templates` settings
page. Choose the template. Then click the yellow button in the
`Actions` column. In the popup box, select the rules for this template
and click the `Attach` button. Hold the CTRL key to select more than
one rule.

!!! note
    A rule can have only one template at a time.

Alert templates use Laravel Blade. This page describes the basics. For
more information, read the [official Laravel
documentation](https://laravel.com/docs/blade).

!!! warning
    Do not give template access to a user that you do not trust.
    Laravel Blade accepts @php. This directive reads and writes local
    files, runs database queries, and more.

## Syntax

Controls:

- if-else (Else can be omitted): `@if ($alert->placeholder  ==
  'value') Some Text @else Other Text @endif`
- foreach-loop: `@foreach ($alert->faults as $key => $value) Key: $key Value: $value @endforeach`

Placeholders:

A placeholder is a special variable. LibreNMS replaces it with the
relevant data. For example:

`The device {{ $alert->hostname }} has been up for {{ $alert->uptime
}} seconds` gives `The device localhost has been up for 30344 seconds`.

!!! note
    A placeholder that gives output needs `{{ }}` around it. An example
    is `{{ $alert->hostname }}`.

- Device ID: `$alert->device_id`
- Hostname of the Device: `$alert->hostname`
- sysName of the Device: `$alert->sysName`
- sysDescr of the Device: `$alert->sysDescr`
- display name of the Device: `$alert->display`
- sysContact of the Device: `$alert->sysContact`
- OS of the Device: `$alert->os`
- Type of Device: `$alert->type`
- IP of the Device: `$alert->ip`
- Hardware of the Device: `$alert->hardware`
- Software version of the Device: `$alert->version`
- Features of the Device: `$alert->features`
- Serial number of the Device: `$alert->serial`
- Location of the Device: `$alert->location`
- Device Groups of the Device (group_id->group_name Array): `$alert->device_groups`
- uptime of the Device (in seconds): `$alert->uptime`
- Short uptime of the Device (28d 22h 30m 7s): `$alert->uptime_short`
- Long uptime of the Device (28 days, 22h 30m 7s): `$alert->uptime_long`
- Description (purpose db field) of the Device: `$alert->description`
- Notes of the Device: `$alert->notes`
- Notes of the alert (ack notes): `$alert->alert_notes`
- ping timestamp (if icmp enabled): `$alert->ping_timestamp`
- ping loss (if icmp enabled): `$alert->ping_loss`
- ping min (if icmp enabled): `$alert->ping_min`
- ping max (if icmp enabled): `$alert->ping_max`
- ping avg (if icmp enabled): `$alert->ping_avg`
- debug (array) 
- Title for the Alert: `$alert->title`
- Time Elapsed, Only available on recovery (`$alert->state == 0`): `$alert->elapsed`
- Rule Builder (the actual rule) (use `{!! $alert->builder !!}`): `$alert->builder`
- Alert-ID: `$alert->id`
- Unique-ID: `$alert->uid`
- Faults: available only on an alert (`$alert->state != 0`). Use a
  foreach loop (`@foreach ($alert->faults as $key => $value)
  @endforeach`). It holds all the information about the fault, in the
  format `$value['Column']`. An example is `$value['ifDescr']`. The
  special field `$value['string']` holds most of the identification
  information, that is the IDs, the names, and the descriptions, in one
  string. This field is the default, and it needs `{{ }}` around it.
- State: `$alert->state`
- Severity: `$alert->severity`
- Rule-Name: `$alert->name`
- Procedure URL: `$alert->proc`
- Timestamp: `$alert->timestamp`
- Transport type: `$alert->transport`
- Transport name: `$alert->transport_name`
- Contacts, must be iterated in a foreach, `$key` holds email and
  `$value` holds name: `$alert->contacts`
- Application Data: `$alert->applications`
- Application Metrics: `$alert->applications_metrics`

You can also use placeholders in the subject of a template. `$faults`
is an array, so it gives no useful output there.

The default template is generic. We recommend your own templates for
your rules. Your own templates hold more specific information.

## Base Templates

To reuse a common template for your alerts, create your own base
template. A default base template is included.

The default file is `resources/views/alerts/templates/default.blade.php`.
It holds this content:

```php
<html>
    <head>
        <title>LibreNMS Alert</title>
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
```

The important part is `@yield('content')`.

You can use plain text or HTML, as in an alert template. This content
is the base of your common template. You can create any number of
templates in the directory.

In your alert template, use this code:

```php
@extends('alerts.templates.default')

@section('content')
  {{ $alert->title }}
  Severity: {{ $alert->severity }}
  ...
@endsection
```

For more information about the extension of a template, read the
[Laravel documentation](https://laravel.com/docs/blade#extending-a-layout).

### Including other Alert templates

You can also reuse the content of another alert template in LibreNMS.
This method uses the AlertTemplate database model. Pass each variable
of the included template to the second parameter of the
`Blade::render()` method. An example is ```["alert" => $alert]```.

The example below includes the whole content of the template with the
ID 5. This method keeps the common text parts, such as a header or a
footer, in separate templates.
```php
{ \Illuminate\Support\Facades\Blade::render(\App\Models\AlertTemplate::find(5)->template , ["alert" => $alert]) }}
```

## Examples

### Default Template

```php
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
```

#### Ports Utilization Template

```php
{{ $alert->title }}
Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif
@foreach ($alert->faults as $key => $value)
Physical Interface: {{ $value['ifDescr'] }}
Interface Description: {{ $value['ifAlias'] }}
Interface Speed: {{ ($value['ifSpeed']/1000000000) }} Gbs
Inbound Utilization: {{ (($value['ifInOctets_rate']*8)/$value['ifSpeed'])*100 }}
Outbound Utilization: {{ (($value['ifOutOctets_rate']*8)/$value['ifSpeed'])*100 }}
@endforeach
```

#### Storage

```php
{{ $alert->title }}

Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
Uptime: {{ $alert->uptime_short }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Location: {{ $alert->location }}
Description: {{ $alert->description }}
Features: {{ $alert->features }}
Notes: {{ $alert->notes }}

Server: {{ $alert->sysName }}
@foreach ($alert->faults as $key => $value)
Mount Point: {{ $value['storage_descr'] }}
Percent Utilized: {{ $value['storage_perc'] }}
@endforeach
```

#### Value Sensors (Temperature, Humidity, Fanspeed, ...)

```php
{{ $alert->title }}

Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
Timestamp: {{ $alert->timestamp }}
Uptime: {{ $alert->uptime_short }}
@if ($alert->state == 0)
Time elapsed: {{ $alert->elapsed }}
@endif
Location: {{ $alert->location }}
Description: {{ $alert->description }}
Features: {{ $alert->features }}
Notes: {{ $alert->notes }}

Rule: {{ $alert->name ?? $alert->rule }}
@if ($alert->faults)
Faults:
@foreach ($alert->faults as $key => $value)
@php($unit = __("sensors.${value["sensor_class"]}.unit"))
#{{ $key }}: {{ $value['sensor_descr'] ?? 'Sensor' }}

Current: {{ $value['sensor_current'].$unit }}
Previous: {{ $value['sensor_prev'].$unit }}
Limit: {{ $value['sensor_limit'].$unit }}
Over Limit: {{ round($value['sensor_current']-$value['sensor_limit'], 2).$unit }}

@endforeach
@endif
```

#### Memory Alert

```php
{{ $alert->title }}

Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
Uptime: {{ $alert->uptime_short }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Location: {{ $alert->location }}
Description: {{ $alert->description }}
Notes: {{ $alert->notes }}

Server: {{ $alert->hostname }}
@foreach ($alert->faults as $key => $value)
Memory Description: {{ $value['mempool_descr'] }}
Percent Utilized: {{ $value['mempool_perc'] }}
@endforeach
```

#### Sneck Alert

```text
{{ $alert->title }}
Severity: {{ $alert->severity }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Unique-ID: {{ $alert->uid }}
@if ($alert->description) Description: {{ $alert->description }} @endif
@if ($alert->notes) Notes: {{ $alert->notes }} @endif
Alert String: {{ $alert->applications['sneck'][0]['data']['alertString'] }}
```

### Advanced options

#### Conditional formatting

This conditional formatting example shows a link to the host in an
email. In any other transport, it shows only the hostname:

```php
@if ($alert->transport == 'mail')<a href="https://my.librenms.install/device/device={{ $alert->hostname }}/">{{ $alert->hostname }}</a>
@else
{{ $alert->hostname }}
@endif
```

#### Traceroute debugs

```php
@if ($alert->status == 0)
    @if (str_contains((string) $alert->status_reason, 'icmp'))
        {{ $alert->debug['traceroute'] }}
    @endif
@endif
```

### Using Application Data In Alert Templates

You can use application data in an alert template.
`$alert->applications` is an associative array. It holds the
applications of the device of the alert. Each subarray holds one line
from the applications table. For example, the app data for Sneck is
`$alert->applications['sneck'][0]['data']`. To use the value
`.data.alertString` from the stored JSON, use
`$alert->applications['sneck'][0]['data']['data']['alertString']`.

To see the available data, run this command on a device with the app:
`lnms report:devices -o json -r applications $device | jq -S .applications | less`.
Then read the app data section.

The index `[0]` is necessary because the legacy apps proxmox and drbd
do not use app data. They can have several instances instead.

#### Metrics

The application metrics are in `$alert->application_metrics`.

For example, this code adds the ZFS error information:

```
Current Total Errors: {{ $alert->applications['zfs'][0]['total_errors']['value'] }}
Current Read Errors: {{ $alert->applications['zfs'][0]['read_errors']['value'] }}
Current Write Errors: {{ $alert->applications['zfs'][0][write_errors']['value'] }}

Previous Total Errors: {{ $alert->applications['zfs'][0]['total_errors']['value_prev'] }}
Previous Read Errors: {{ $alert->applications['zfs'][0]['read_errors']['value_prev'] }}
Previous Write Errors: {{ $alert->applications['zfs'][0][write_errors']['value_prev'] }}
```

## Examples HTML

To use HTML emails, set HTML email to Yes in the web interface:

!!! setting "alerting/email"
    ```bash
    lnms config:set email_html true
    ```

## Graphs

Two helpers for graphs use a signed URL for secure external access.
Each person with the signed URL can see the graph.

 - Your LibreNMS web server must be available from the location of the
   viewer. Some alert transports need a public URL.
 - Signed graphs need `APP_URL` in the `.env` file.
 - A change to `APP_KEY` invalidates all existing signed URLs.

There are two ways to give the graph. The first way is a PHP array of
parameters. The second way is a direct URL to a graph.

You can give `to` and `from` as timestamps with `time()`. You can also
give them as a relative time, such as `-3d` or `-36h`. With a relative
time, the graph shows the data at the moment of the view, not at the
moment of the event. A relative time therefore always gives the
recipient access to the current data. A specific timestamp gives access
only to that time frame.

### @signedGraphTag

This helper inserts an HTML img tag with a link to the graph. Some
transports search the template for this tag. They then attach the
images in the correct way for that transport.

```php
@signedGraphTag([
    'id' => $value['port_id'],
    'type' => 'port_bits',
    'from' => time() - 43200,
    'to' => time(),
    'width' => 700, 
    'height' => 250
])
```

Output:

```html
<img class="librenms-graph" src="https://librenms.org/graph?from=1662176216&amp;height=250&amp;id=20425&amp;to=1662219416&amp;type=port_bits&amp;width=700&amp;signature=f6e516e8fd893c772eeaba165d027cb400e15a515254de561a05b63bc6f360a4">
```

A specific graph with a URL input:

```php
@signedGraphTag('https://librenms.org/graph.php?type=device_processor&from=-2d&device=2&legend=no&height=400&width=1200')
```

### @signedGraphUrl

Use this helper when you need the URL itself. One example is the API
transport, where you want the URL and not an HTML tag.

```php
@signedGraphUrl([
    'id' => $value['port_id'],
    'type' => 'port_bits',
    'from' => time() - 43200,
    'to' => time(),
])
```

## Using models for optional data

If a value is not in the `$faults[]` array, you can query the database
fields with Laravel models. Put the model and the search value inside
the braces. For example, an ISIS alert has a `port_id` value, but
`ifName` is not in the `$faults[]` array. This template queries the
name of the port:

```php
{{ $alert->title }}
Severity: {{ $alert->severity }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }}
Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif
@if ($alert->faults) Faults:
@foreach ($alert->faults as $key => $value)
  Local interface: {{ \App\Models\Port::find($value['port_id'])->ifName }}
  Adjacent IP: {{ $value['isisISAdjIPAddrAddress'] }}
  Adjacent state: {{ $value['isisISAdjState'] }}
@endforeach
@endif
```

### Service Alert

```php
<div style="font-family:Helvetica;">
<h2>@if ($alert->state == 1) <span style="color:red;">{{ $alert->severity }} @endif
@if ($alert->state == 2) <span style="color:goldenrod;">acknowledged @endif</span>
@if ($alert->state == 3) <span style="color:green;">recovering @endif</span>
@if ($alert->state == 0) <span style="color:green;">recovered @endif</span>
</h2>
<b>Host:</b> {{ $alert->hostname }}<br>
<b>Duration:</b> {{ $alert->elapsed }}<br>
<br>

@if ($alert->faults)
@foreach ($alert->faults as $key => $value) <b>{{ $value['service_desc'] }} - {{ $value['service_type'] }}</b><br>
{{ $value['service_message'] }}<br>
<br>
@endforeach
@endif
</div>
```

#### Processor Alert with Graph

```php
{{ $alert->title }} <br>
Severity: {{ $alert->severity }}  <br>
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Timestamp: {{ $alert->timestamp }} <br>
Alert-ID: {{ $alert->id }} <br>
Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif <br>
@if ($alert->faults) Faults:
@foreach ($alert->faults as $key => $value)
{{ $key }}: {{ $value['string'] }}<br>
@endforeach
@if ($alert->faults) <b>Faults:</b><br>
@foreach ($alert->faults as $key => $value)
@signedGraphTag(['device' => $value['device_id'], 'type' => 'device_processor', 'width' => 459, 'height' => 213, 'from' => time() - 259200])<br>
https://server/graphs/device={{ $value['device_id'] }}/type=device_processor/<br>
@endforeach
Template: CPU alert <br>
@endif
@endif
```

## Included

We include some templates for common alert rule types. For example, a
rule that alerts on BGP sessions can use the BGP template. That
template gives more information.

Apart from the default template, these templates are included:

- BGP Sessions
- Ports
- Temperature

## Other Examples

### Microsoft Teams - Markdown

```php
[{{ $alert->title }}](https://your.librenms.url/device/device={{ $alert->device_id }}/)
**Device name:** {{ $alert->sysName }}
**Severity:** {{ $alert->severity }}
@if ($alert->state == 0)
**Time elapsed:** {{ $alert->elapsed }}
@endif
**Timestamp:** {{ $alert->timestamp }}
**Unique-ID:** {{ $alert->uid }}
@if ($alert->name)
**Rule:** {{ $alert->name }}
@else
**Rule:** {{ $alert->rule }}
@endif
@if ($alert->faults)
**Faults:**@foreach ($alert->faults as $key => $value) {{ $key }}: {{ $value['string'] }}
@endforeach
@endif
```

### Microsoft Teams - JSON

```php
{
    "@@context": "https://schema.org/extensions",
    "@type": "MessageCard",
    "title": "{{ $alert->title }}",
@if ($alert->state === 0)
    "themeColor": "00FF00",
@elseif ($alert->state === 1)
    "themeColor": "FF0000",
@elseif ($alert->state === 2)
    "themeColor": "337AB7",
@elseif ($alert->state === 3)
    "themeColor": "FF0000",
@elseif ($alert->state === 4)
    "themeColor": "F0AD4E",
@else
    "themeColor": "337AB7",
@endif
    "summary": "LibreNMS",
    "sections": [
        {
@if ($alert->name)
            "facts": [
                {
                    "name": "Rule:",
                    "value": "[{{ $alert->name }}](https://your.librenms.url/device/device={{ $alert->device_id }}/tab=alert/)"
                },
@else
                {
                    "name": "Rule:",
                    "value": "[{{ $alert->rule }}](https://your.librenms.url/device/device={{ $alert->device_id }}/tab=alert/)"
                },
@endif
                {
                    "name": "Severity:",
                    "value": "{{ $alert->severity }}"
                },
                {
                    "name": "Unique-ID:",
                    "value": "{{ $alert->uid }}"
                },
                {
                    "name": "Timestamp:",
                    "value": "{{ $alert->timestamp }}"
                },
@if ($alert->state == 0)
                {
                    "name": "Time elapsed:",
                    "value": "{{ $alert->elapsed }}"
                },
@endif
                {
                    "name": "Hostname:",
                    "value": "[{{ $alert->hostname }}](https://your.librenms.url/device/device={{ $alert->device_id }}/)"
                },
                {
                    "name": "Hardware:",
                    "value": "{{ $alert->hardware }}"
                },
                {
                    "name": "IP:",
                    "value": "{{ $alert->ip }}"
                },
                {
                    "name": "Faults:",
                    "value": " "
                }
            ]
@if ($alert->faults)
@foreach ($alert->faults as $key => $value)
        },
        {
            "facts": [
                {
                    "name": "Port:",
                    "value": "[{{ $value['ifName'] }}](https://your.librenms.url/device/device={{ $alert->device_id }}/tab=port/port={{ $value['port_id'] }}/)"
                },
                {
                    "name": "Description:",
                    "value": "{{ $value['ifAlias'] }}"
                },
@if ($alert->state != 0)
                {
                    "name": "Status:",
                    "value": "down"
                }
            ]
@else
                {
                    "name": "Status:",
                    "value": "up"
                }
            ]
@endif
@endforeach
@endif
        }
    ]
}
```

### Microsoft Teams - AdaptiveCard JSON

```php
@php
    $state_color = match ((int) $alert->state) {
        0  => 'Good',       // CLEAR, RECOVERED
        1  => 'Attention',  // ACTIVE
        2  => 'Accent',     // ACKNOWLEDGED
        3  => 'Attention',  // WORSE
        4  => 'Warning',    // BETTER
        5  => 'Warning',    // CHANGED
        default => 'Default',
    };
    $severity_color = match ($alert->severity) {
        'ok', 'Ok' => 'Good',
        'warning', 'Warning' => 'Warning',
        'critical', 'Critical' => 'Attention',
        default => 'Default',
    };
@endphp
{
    "type": "message"
    "attachments": [
        {
            "contentType": "application/vnd.microsoft.card.adaptive",
            "content": {
                "$schema": "http://adaptivecards.io/schemas/adaptive-card.json",
                "version": "1.4",
                "type": "AdaptiveCard",
                "body": [
                    {
                        "type":  "TextBlock",
                        "size":  "Large",
                        "weight":  "Bolder",
                        "color":  "{{ $state_color }}",
                        "text":  "🚨 **LibreNMS Alert @if ($alert->state == 0) - Resolved @endif**",
                        "horizontalAlignment":  "Center",
                        "spacing":  "Small"
                    },
                    {
                        "type":  "TextBlock",
                        "text":  "**🔔** {{ $alert->title }}",
                        "wrap":  true,
                        "color": "Accent",
                        "weight":  "Bolder",
                        "spacing":  "Small"
                    },
                    {
                        "type":  "TextBlock",
                        "text":  "**📌 State:** @switch ($alert->state)
                            @case (0) OK ✅ @break
                            @case (1) Warning ⚠️ @break
                            @case (2) Critical ❌ @break
                            @default Unknown @endswitch",
                        "wrap":  true,
                        "color":  "{{ $state_color }}",
                        "spacing":  "Small"
                    },
                    @if ($alert->state == 0) {
                        "type":  "TextBlock",
                        "text":  "**🕒 Elapsed:** {{ $alert->elapsed }}",
                        "wrap":  true,
                        "spacing":  "Small"
                    }, @endif
                    {
                        "type":  "TextBlock",
                        "text":  "**📅 Timestamp:** {{ $alert->timestamp }}",
                        "wrap":  true,
                        "spacing":  "Small"
                    },
                    {
                        "type":  "TextBlock",
                        "text":  "**🆔 Unique-ID:** {{ $alert->uid }}",
                        "wrap":  true,
                        "spacing":  "Small"
                    },
                    {
                        "type":  "TextBlock",
                        "text":  "**⚠️ Severity:**  {{ $alert->severity }}",
                        "wrap":  true,
                        "color":  "{{ $severity_color }}",
                        "spacing":  "Small"
                    },
                    {
                        "type":  "TextBlock",
                        "text":  "**📜 Rule:**  @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif",
                        "wrap":  true,
                        "color":  "Accent",
                        "spacing":  "Small"
                    },
                    @if ($alert->faults and count($alert->faults) > 0)
                    {
                        "type":  "TextBlock",
                        "text":  "**🔍 Fault Details:**",
                        "wrap":  true,
                        "size":  "Medium",
                        "weight":  "Bolder",
                        "spacing":  "Small"
                    },
                    @foreach ($alert->faults as $fault_key => $fault_details)
                    {
                        "type": "ActionSet",
                        "actions": [
                            {
                                "type": "Action.ShowCard",
                                "title": "Fault {{ $fault_key }} ",
                                "card": {
                                    "type": "AdaptiveCard",
                                    "body": [
                                        {
                                            "type":  "FactSet",
                                            "separator":  true,
                                            "facts":  [
                                                @foreach ($fault_details as $key => $value)
                                                @if ($key == 'string')
                                                    {{--
                                                        the 'string' key is a redundant amalgam of all 
                                                        other keys in the assoc array, skip it
                                                    --}}
                                                    @continue    
                                                @endif
                                                {
                                                    "title":  "{{ $key }}",
                                                    "value":  "{{ str_replace(array("\r\n", "\n", "\r"), "", $value) }}"
                                                },
                                                @endforeach
                                                {"title": "", "value": ""}
                                            ]
                                        }
                                    ]
                                }
                            }
                        ]
                    },
                    @endforeach
                    {"type": "TextBlock", "text": ""}
                    @else
                    {"type": "TextBlock", "text": "No fault data in this alert"}
                    @endif
                ],
                "actions":  [
                    {
                        "type":  "Action.OpenUrl",
                        "title":  "View Alert",
                        "style": "positive",
                        "url":  "https://librenms.server.utsc.utoronto.ca/device/{{ $alert->device_id }}/alerts"
                    }
                ]
                }
        }
    ]
}
```
