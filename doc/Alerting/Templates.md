source: Alerting/Templates.md
path: blob/master/doc/

# Templates

> This page is for installs running version 1.42 or later. You can
> find the older docs [here](Old_Templates.md)

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
information [here](https://laravel.com/docs/5.7/blade)

## Syntax

Controls:

- if-else (Else can be omitted): `@if ($alert->placeholder  ==
  'value') Some Text @else Other Text @endif`
- foreach-loop: `@foreach ($alert->faults as $key => $value) Key: $key
  </br> alue: $value @endforeach`

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
- sysContact of the Device: `$alert->sysContact`
- OS of the Device: `$alert->os`
- Type of Device: `$alert->type`
- IP of the Device: `$alert->ip`
- hardware of the Device: `$alert->hardware`
- Software version of the Device: `$alert->version`
- location of the Device: `$alert->location`
- uptime of the Device (in seconds): `$alert->uptime`
- short uptime of the Device (28d 22h 30m 7s): `$alert->uptime_short`
- long uptime of the Device (28 days, 22h 30m 7s): `$alert->uptime_long`
- description (purpose db field) of the Device: `$alert->description`
- notes of the Device: `$alert->notes`
- notes of the alert (ack notes): `$alert->alert_notes`
- ping timestamp (if icmp enabled): `$alert->ping_timestamp`
- ping loss (if icmp enabled): `$alert->ping_loss`
- ping min (if icmp enabled): `$alert->ping_min`
- ping max (if icmp enabled): `$alert->ping_max`
- ping avg (if icmp enabled): `$alert->ping_avg`
- debug (array) If `$config['debug']['run_trace] = true;` is set then this will contain:
  - traceroute (if enabled you will receive traceroute output): `$alert->debug['traceroute']`
  - output (if the traceroute fails this will contain why): `$alert->debug['output']`
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

More info: [https://laravel.com/docs/5.7/blade#extending-a-layout](https://laravel.com/docs/5.7/blade#extending-a-layout)

## Examples

#### Default Template

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
Purpose: {{ $alert->purpose }}
Notes: {{ $alert->notes }}

Server: {{ $alert->sysName }}
@foreach ($alert->faults as $key => $value)
Mount Point: {{ $value['storage_descr'] }}
Percent Utilized: {{ $value['storage_perc'] }}
@endforeach
```

#### Temperature Sensors

```text
{{ $alert->title }}

Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
Timestamp: {{ $alert->timestamp }}
Uptime: {{ $alert->uptime_short }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Location: {{ $alert->location }}
Description: {{ $alert->description }}
Features: {{ $alert->features }}
Purpose: {{ $alert->purpose }}
Notes: {{ $alert->notes }}

Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif
@if ($alert->faults) Faults:
@foreach ($faults as $key => $value)
#{{ $key }}: Temperature: {{ $value['sensor_current'] }} °C
** @php echo ($value['sensor_current']-$value['sensor_limit']); @endphp°C over limit
Previous Measurement: {{ $value['sensor_prev'] }} °C
High Temperature Limit: {{ $value['sensor_limit'] }} °C
@endforeach
@endif
```

#### Value Sensors

```text
{{ $alert->title }}

Device Name: {{ $alert->hostname }}
Severity: {{ $alert->severity }}
Timestamp: {{ $alert->timestamp }}
Uptime: {{ $alert->uptime_short }}
@if ($alert->state == 0) Time elapsed: {{ $alert->elapsed }} @endif
Location: {{ $alert->location }}
Description: {{ $alert->description }}
Features: {{ $alert->features }}
Purpose: {{ $alert->purpose }}
Notes: {{ $alert->notes }}

Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif
@if ($alert->faults) Faults:
@foreach ($alert->faults as $key => $value)
#{{ $key }}: Sensor {{ $value['sensor_current'] }}
** @php echo ($value['sensor_current']-$value['sensor_limit']); @endphp over limit
Previous Measurement: {{ $value['sensor_prev'] }}
Limit: {{ $value['sensor_limit'] }}
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
@if ($alert->transport == mail)<a href="https://my.librenms.install/device/device={{ $alert->hostname }}/">{{ $alert->hostname }}</a>
@else
{{ $alert->hostname }}
@endif
```

#### Traceroute debugs

```text
@if ($alert->status == 0)
    @if ($alert->status == icmp)
        {{ $alert->debug['traceroute'] }}
    @endif
@endif
```

## Examples HTML

Note: To use HTML emails you must set HTML email to Yes in the WebUI
under Global Settings > Alerting Settings > Email transport > Use HTML
emails

Note: To include Graphs you must enable unauthorized graphs in
config.php. Allow_unauth_graphs_cidr is optional, but more secure.

```
$config['allow_unauth_graphs_cidr'] = array('127.0.0.1/32');
$config['allow_unauth_graphs'] = true;
```

#### Service Alert

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
@foreach ($alert->faults as $key => $value)<img src="https://server/graph.php?device={{ $value['device_id'] }}&type=device_processor&width=459&height=213&lazy_w=552&from=end-72h"><br>
https://server/graphs/id={{ $value['device_id'] }}/type=device_processor/<br>
@endforeach
Template: CPU alert <br>
@endif
@endif
```

#### MS Teams formatted default template

```
<a href="https://your.librenms.url/device/device={{ $alert->device_id }}/">{{ $alert->title }}</a>
<pre><strong>Device name:</strong> {{ $alert->sysName }}
<strong>Severity:</strong> {{ $alert->severity }}
@if ($alert->state == 0)<strong>Time elapsed:</strong>{{ $alert->elapsed }}
@endif<strong>Timestamp:</strong> {{ $alert->timestamp }}
<strong>Unique-ID:</strong> {{ $alert->uid }}
<strong>Rule:</strong>@if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif</pre>
<pre style="white-space:normal;">@if ($alert->faults) <strong>Faults:</strong>
 @foreach ($alert->faults as $key => $value)  #{{ $key }}: {{ $value['string'] }}
 @endforeach </pre>  @endif
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
