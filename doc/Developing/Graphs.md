source: Developing/Graphs.md

# Creating Graphs

Create new time-series graphs with a PHP class in `app/Graphs`. The class
describes which stored metrics to show and how to draw them. LibreNMS handles
RRD file lookup, rendering, and the standard legend.

This guide builds a device graph for active Pulse Secure users. It uses the
data collected in [Custom Device Graphs](os/Custom-Graphs.md).

## Before you start

The metric must already be collected by a poller. A graph reads stored data;
it does not poll the device or create RRD files. For this example, the poller
stores the measurement `pulse_users` with a field named `users`.

For most device graphs, extend `App\Graphs\Device\DeviceGraph`. It provides
access to the requested device through `$this->device()` and checks that the
user can view that device.

## 1. Choose the graph name

A graph name has two parts: a type and a subtype, separated by the first
underscore. LibreNMS uses these parts to find the class automatically.

| Graph name | Class | File |
| --- | --- | --- |
| `device_pulse_users` | `App\Graphs\Device\PulseUsersGraph` | `app/Graphs/Device/PulseUsersGraph.php` |
| `device_netstat_ip` | `App\Graphs\Device\NetstatIpGraph` | `app/Graphs/Device/NetstatIpGraph.php` |
| `device_processor` | `App\Graphs\Device\ProcessorGraph` | `app/Graphs/Device/ProcessorGraph.php` |

No factory registration or new route is needed. The graph name does not have
to match the stored measurement, but the metric identity in the next step must.

## 2. Create the graph class

Create `app/Graphs/Device/PulseUsersGraph.php` with this content:

```php
<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PulseUsersGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return $this->device()->display . ' :: Active Users';
    }

    /**
     * @return array<string, DataSeries>
     */
    public function getSeries(): array
    {
        $metric = $this->metric('pulse_users');

        return [
            'users' => new DataSeries(
                metric: $metric,
                field: 'users',
                description: 'Active Users',
                style: new SeriesStyle(fill: true),
            ),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            scaleMin: 0,
            verticalLabel: 'Users',
            legendTitle: 'Users',
        );
    }
}
```

The class has three jobs:

- `getTitle()` supplies the graph's title text.
- `getSeries()` returns the values to plot, keyed by a unique series name.
  Each `DataSeries` selects a stored field and gives it a legend label.
- `options()` sets graph-wide presentation options. It is optional; omit it
  to use the defaults.

### Match the stored data

`MetricIdentity` identifies a stored metric by its measurement and tags, the
same ones used when storing the data. What the metric belongs to is a tag too:
`$this->metric('pulse_users')` is the measurement `pulse_users` with the
`hostname` tag of the device. `DataSeries::field` selects the dataset inside
that measurement. Field names must match the stored dataset names exactly.

Include the tags that tell one series from another, with short names that do
not repeat the measurement: `type`, not `processor_type`. Tags that can change,
such as a description, do not belong in the identity. Use a model's
`metricIdentity()` method when it provides one, so the graph and poller share
the same identity:

```php
$processor->metricIdentity('processor');

// the same as
new MetricIdentity('processor', [
    'hostname' => $processor->device->hostname,
    'type' => $processor->processor_type,
    'index' => $processor->processor_index,
]);
```

#### RRD files

The RRD file is found in the directory of the first tag, which is `hostname`
for a metric of a device, and named after the measurement followed by the
other tag values, in order. `$this->metric('pulse_users')` reads
`pulse_users.rrd`.

A metric whose names were cleaned up is still stored under the names it had
before, unless `datastore.canonical_metrics` is enabled.
`LibreNMS\Data\Store\MetricNames` lists those names by measurement, for all
datastores. Identify the metric by its clean measurement and tags: the graph
finds the file under either name.

## 3. Add the graph to the device's Graphs tab

Class discovery makes a graph renderable. Listing it on the device's
**Graphs** tab requires two separate pieces:

- `graph_types.device.pulse_users` defines the graph's label and section for
  all devices. It does not enable the graph on any device.
- `$this->enableGraph('pulse_users')` marks the graph as available on the
  device being polled. It does not define the graph's label or section.

The Graphs tab lists the graphs enabled for that device and uses `graph_types`
to place them in sections and label them.

For a contribution, add the graph's metadata to
`resources/definitions/config_definitions.json`:

```json
"graph_types.device.pulse_users": {
    "default": {
        "section": "firewall",
        "order": 0,
        "descr": "Active Users"
    },
    "type": "graph"
}
```

This is one entry in the existing JSON object; add a comma between entries.
To customize the setting locally, use `lnms config:set`:

```bash
lnms config:set graph_types.device.pulse_users '{"section":"firewall","order":0,"descr":"Active Users"}'
```

Then have the poller enable the subtype after collecting valid data. In an OS
poller's `pollOS()` method, this is:

```php
$this->enableGraph('pulse_users');
```

Use the subtype `pulse_users` here, without the `device_` prefix. See
[Custom Device Graphs](os/Custom-Graphs.md) for the complete polling example.

## 4. Display and check the graph

After polling has created the RRD file and enabled the graph, open the device's
**Graphs** tab and select the **Firewall** section.

To show the same graph in an existing Blade view that has a `$device` variable,
use the graph component:

```blade
<x-graph type="device_pulse_users" :device="$device" legend="yes" />
```

The component supplies the graph URL and links to the full graph page. On that
page, **Show RRD Command** displays the generated command for troubleshooting.

Check that the label and units are correct, recent values appear after polling,
and a user without access to the device cannot view the graph.

## Customize the appearance

Add more `DataSeries` entries to `getSeries()` to draw several values on the
same graph. They may use different fields or different metric identities.
Series are drawn in array order.

### Series options

Set these named arguments on `SeriesStyle` to control an individual series:

| Option | Effect |
| --- | --- |
| `role: SeriesRole::In` | Says what the series shows, which sets its colors. Otherwise, uses the graph palette. |
| `fill: true` | Fills the area beneath a line. |
| `stacked: true` | Draws an area above the preceding stacked series. |
| `invert: true` | Draws below zero, unless `webui.graph_stacked` is enabled. |
| `drawScale: 0.5` | Scales only the drawing; legend values retain the series value. |
| `stackAverage: true` | Divides the drawing by the number of drawn series with this set, so a stack of percentages shows their average. |
| `maxFill: true` | Fills the area below the highest value of each interval, behind the other fills. |
| `range: true` | Fills the area between the lowest and highest value of each interval, behind the other fills. |
| `legendOnly: true` | Shows the series in the legend without drawing it. |

Inverted stacked series stack separately below the axis. Stacked series may be
listed in any order, such as the in and out series of each port together.

Graphs never set colors. A `SeriesRole` says what a series shows, such as
`In`, `Out`, `Error`, `Value` (the measured value, in the color of the legend
text), `Muted` (free space) or `SeriesRole::usage($percent, $warn)` for used
capacity, and the renderer picks colors that suit the light or dark site style.
Drawn series that share a role get different shades, so stacked ports each get
their own color. Stacked `In` and `Out` series take their shades from the
`graph_colours.greens` and `graph_colours.purples` palettes, so a site that
changes those palettes changes these graphs too. When `webui.graph_stacked`
draws inverted series above the axis, the renderer makes areas and fills
translucent so the series behind them stay visible.

To convert the values themselves, use `scale` on `DataSeries`. For example,
`scale: 8, unit: 'bps'` converts a stored bytes-per-second field to bits per
second, including the legend values. Setting `unit` alone only adds a suffix;
it does not convert values. `offset` is added after the scale: `scale: 1.8,
offset: 32` converts celsius to fahrenheit.

The lowest and highest value of each interval come from the stored minimum and
maximum of `field`. When they are stored in fields of their own, name them with
`minField` and `maxField`.

To show a value calculated from several fields of the metric, pass a `Formula`
as the field. It is written in reverse polish notation with field names,
numbers and the operators `+ - * /`:

```php
// percent of pings lost
new DataSeries($metric, new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*'), 'Loss', '%');
```

The lowest and highest values of a formula are those of its averages.
Datastores that cannot calculate it themselves use `Formula::evaluate()`.

To add up series of the graph, such as the traffic of every port of a device,
add a `SeriesSum` with the keys of those series. It may only use series listed
before it, including other sums. A series without a value at a time counts as
zero, and series without stored data are left out:

```php
$series['in'] = new SeriesSum(['in_1', 'in_2'], 'Total In', 'bps', new SeriesStyle(legendOnly: true));
```

Like a formula, the lowest and highest values of a sum are those of its averages.

### Graph options

Common named arguments on `GraphOptions` are:

| Option | Purpose |
| --- | --- |
| `scaleMin`, `scaleMax` | Sets the lower and upper scale limits. |
| `scaleRigid` | Controls whether the graph strictly follows the scale limits. |
| `verticalLabel` | Labels the vertical axis. |
| `legendTitle` | Adds a heading above the series labels. |
| `palette` | Selects a palette from `graph_colours`; defaults to `mixed`. |
| `base` | Sets the scaling base, such as `1024` for binary units. |
| `unitsExponent` | Controls the axis unit exponent; `0` shows axis and legend values without SI prefixes, for units such as days or milliseconds. |
| `thresholds` | Draws a dashed `Threshold` line for each value, such as alert limits, in the units of the series. Its role, such as `SeriesRole::HighLimit`, sets its color. |
| `scaleMargin` | Widens an autoscaled axis by this fraction of the values, so values that barely change still get axis labels. |
| `axisUnit` | Shows a unit after the axis values, which are then shown without SI prefixes. |
| `rightAxisLabel` | Adds a right axis with the same scale as the left one. |

Describe what the graph should show rather than how rrdtool draws it.
`leftAxisFormat` and `rightAxis` take rrdtool syntax and only exist for the
legacy graphs; graphs drawn from other datastores ignore them.

For example, a percentage graph can use `scaleMin: 0, scaleMax: 100,
scaleRigid: true`. See `app/Data/Graphing/GraphOptions.php` for all options.

## Troubleshooting

| Symptom | What to check |
| --- | --- |
| Graph is missing from the Graphs tab | Check the `graph_types.device` entry and that polling calls `enableGraph()` with the same subtype. |
| No data | Confirm polling has created the RRD file and the metric identity matches the stored filename. Missing files are skipped; if no series remain, the graph reports no data. |
| Draw error | Check the field name against the RRD dataset and inspect **Show RRD Command**. |
| Graph cannot be found | Check the class namespace, filename, and type/subtype naming. |
| Access denied | Check that the requested device exists and the user has permission to view it. |

## Migrating an existing graph

Create the class using the existing graph name so callers can keep using that
name. LibreNMS prefers a matching graph class over a legacy
`includes/html/graphs/<type>/<subtype>.inc.php` file. If no class matches, it
falls back to the legacy include.

A graph type may have a `GenericGraph` class that draws every subtype without
a class or legacy include of its own. For example, `App\Graphs\Sensor\GenericGraph`
draws `sensor_temperature`, `sensor_voltage` and the other sensor classes.

The metric identity is authoritative: datastores store and read the metric by
it, so choose clean measurement and tag names rather than copying how the data
happens to be stored now. If existing data is stored under other names, such
as the `icmp-perf` measurement of `ping` or `mempool_type` instead of `type`,
add those names to `LibreNMS\Data\Store\MetricNames`. Every datastore then
finds the existing data by the clean identity.

Replace legacy RRD filename and dataset variables with `MetricIdentity` and
`DataSeries`, and move presentation settings into `SeriesStyle` and
`GraphOptions`. Compare the resulting graph and legend before removing the
old include.

For working examples, read `app/Graphs/Device/NetstatIpGraph.php` for multiple
fields and inverted series, or `app/Graphs/Device/ProcessorGraph.php` for one
series per model and stacked drawing. Their regression tests are in
`tests/Feature/Graphs/Device`.

`app/Graphs/Port/BitsGraph.php` shows a port graph with byte-to-bit conversion
and maximum fills. It extends `PortGraph`, which checks permission to view the
port and supplies its page context and title. Its `GraphOptions::traffic`
uses `TrafficOptions` to add percentiles, transferred-byte totals, previous
period comparisons, future trend lines, and port speed references.
`TrafficOptions::series` limits these to some of the series:
`app/Graphs/Device/BitsGraph.php` stacks the traffic of each port and only
summarizes the device totals.

For graph types outside device graphs, extend `AbstractSeriesGraph` and
implement `isAuthorized()` for the entities being displayed, using `canView()`
for each entity. Users need permission to view the entity; guests may only view
graphs when `allow_unauth_graphs` or `allow_unauth_graphs_cidr` allows them, or
when the caller vouched for the request (a signed graph URL or an alert
image). Graph-specific request rules belong in `validation()`, and typed input
is available through `$this->params->input`. Override `getPageContext()` to
choose the device, port or subtitle shown in the graph page heading.

A series graph only declares its series and options; `RrdGraphRenderer`
draws them with rrdtool. Override `renderer()` to draw them with another
backend.
