# Templates

> This page is for installs running version 1.42 or later. You can
> find the older docs [here](https://github.com/librenms/librenms/blob/773411359489e0ffcc3ba763f1f138403343591a/doc/Alerting/Old_Templates.md)

Templates can be assigned to a single or a group of rules and can
contain any kind of text. There is also a default template which is
used for any rule that isn't associated with a template. This template
can be found under `Alert Templates` page and can be edited. It also
has an option revert it back to its default content.

To attach a template to a rule just open the `Alert Templates`
settings page, choose the template to assign and click the yellow
button in the `Actions` column. In the appearing popupbox select the
rule(s) you want the template to be assigned to and click the `Attach`
button. You might hold down the CTRL key to select multiple rules at once.

The templating engine in use is Laravel Blade. We will cover some of
the basics here, however the official Laravel docs will have more
information [here](https://laravel.com/docs/blade)

## Syntax

Controls:

- if-else (Else can be omitted): `@if ($alert->placeholder  ==
  'value') Some Text @else Other Text @endif`
- foreach-loop: `@foreach ($alert->faults as $key => $value) Key: $key Value: $value @endforeach`

Placeholders:

Placeholders are special variables that if used within the template
will be replaced with the relevant data, I.e:

`The device {{ $alert->hostname }} has been up for {{ $alert->uptime
}} seconds` would result in the following `The device localhost has
been up for 30344 seconds`.

> When using placeholders to echo data, you need to wrap
> the placeholder in `{{ }}`. I.e `{{ $alert->hostname }}`.

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
  - poller_name - name of poller (for distributed setups)
  - If `$config['debug']['run_trace] = true;` is set then this will contain:
   - traceroute (if enabled you will receive traceroute output): `$alert->debug['traceroute']`
   - traceroute_output (if the traceroute fails this will contain why): `$alert->debug['traceroute_output']`
- Title for the Alert: `$alert->title`
- Time Elapsed, Only available on recovery (`$alert->state == 0`): `$alert->elapsed`
- Rule Builder (the actual rule) (use `{!! $alert->builder !!}`): `$alert->builder`
- Alert-ID: `$alert->id`
- Unique-ID: `$alert->uid`
- Faults, Only available on alert (`$alert->state != 0`), must be
  iterated in a foreach (`@foreach ($alert->faults as $key => $value)
  @endforeach`). Holds all available information about the Fault,
  accessible in the format `$value['Column']`, for example:
  `$value['ifDescr']`. Special field `$value['string']` has most
  Identification-information (IDs, Names, Descrs) as single string,
  this is the equivalent of the default used and must be encased in `{{ }}`
- State: `$alert->state`
- Severity: `$alert->severity`
- Rule: `$alert->rule`
- Rule-Name: `$alert->name`
- Procedure URL: `$alert->proc`
- Timestamp: `$alert->timestamp`
- Transport type: `$alert->transport`
- Transport name: `$alert->transport_name`
- Contacts, must be iterated in a foreach, `$key` holds email and
  `$value` holds name: `$alert->contacts`

Placeholders can be used within the subjects for templates as well
although $faults is most likely going to be worthless.

The Default Template is a 'one-size-fit-all'. We highly recommend
defining your own templates for your rules to include more specific
information.

## Base Templates

If you'd like to reuse a common template for your alerts follow below

A default file is located in
` resources/views/alerts/templates/default.blade.php`
Displays the following:

```
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

The important part being the `@yield('content')`

You can use plain text or html as per Alert templates and this will
form the basis of your common template, feel free to make as many
templates in the directory as needed.

In your alert template just use

```
@extends('alerts.templates.default')

@section('content')
  {{ $alert->title }}
  Severity: {{ $alert->severity }}
  ...
@endsection
```

More info: [https://laravel.com/docs/blade#extending-a-layout](https://laravel.com/docs/blade#extending-a-layout)

## Examples

### Default Template

```text
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

```text
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

```text
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

```text
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

```text
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

### Advanced options

#### Conditional formatting

Conditional formatting example, will display a link to the host in
email or just the hostname in any other transport:

```text
@if ($alert->transport == 'mail')<a href="https://my.librenms.install/device/device={{ $alert->hostname }}/">{{ $alert->hostname }}</a>
@else
{{ $alert->hostname }}
@endif
```

#### Traceroute debugs

```text
@if ($alert->status == 0)
    @if ($alert->status_reason == 'icmp')
        {{ $alert->debug['traceroute'] }}
    @endif
@endif
```

## Examples HTML

Note: To use HTML emails you must set HTML email to Yes in the WebUI
under Global Settings > Alerting Settings > Email transport > Use HTML
emails

## Graphs

There are two helpers for graphs that will use a signed url to allow secure external
access.  Anyone using the signed url will be able to view the graph.

 - Your LibreNMS web must be accessible from the location where the graph is viewed.
   Some alert transports require publicly accessible urls.
 - APP_URL must be set in .env to use signed graphs.
 - Changing APP_KEY will invalidate all previously issued singed urls.

You may specify the graph one of two ways, a php array of parameters, or
a direct url to a graph.

Note that to and from can be specified either as timestamps with `time()`
or as relative time `-3d` or `-36h`.  When using relative time, the graph
will show based on when the user views the graph, not when the event happened.
Sharing a graph image with a relative time will always give the recipient access
to current data, where a specific timestamp will only allow access to that timeframe.

### @signedGraphTag

This will insert a specially formatted html img tag linking to the graph.
Some transports may search the template for this tag to attach images properly
for that transport.

```
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

Specific graph using url input:

```
@signedGraphTag('https://librenms.org/graph.php?type=device_processor&from=-2d&device=2&legend=no&height=400&width=1200')
```

### @signedGraphUrl

This is used when you need the url directly. One example is using the
API Transport, you may want to include the url only instead of a html tag.

```
@signedGraphUrl([
    'id' => $value['port_id'],
    'type' => 'port_bits',
    'from' => time() - 43200,
    'to' => time(),
])
```

## Using models for optional data

If some value does not exist within the `$faults[]`-array, you may
query fields from the database using Laravel models. You may use
models to query additional values and use them on the template by
placing the model and the value to search for within the braces. For
example, ISIS-alerts do have a `port_id` value associated with the
alert but `ifName` is not directly accessible from the
`$faults[]`-array. If the name of the port was needed, it's value
could be queried using a template such as:

```
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

```
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

```
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

We include a few templates for you to use, these are specific to the
type of alert rules you are creating. For example if you create a rule
that would alert on BGP sessions then you can assign the BGP template
to this rule to provide more information.

The included templates apart from the default template are:

- BGP Sessions
- Ports
- Temperature

## Other Examples

### Microsoft Teams - Markdown

```
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

```
{
    "@context": "https://schema.org/extensions",
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

