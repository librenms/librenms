# Notes On Graphs

## generic_stats.inc.php

This allows for advanced stats graphs to be created the display large
amounts of information for a specific DS.

| Variables         | Default              | Type                   | Function                                |
|-------------------|----------------------|------------------------|-----------------------------------------|
| $ds               | undef                | DS                     | RRD DS to use                           |
| $descr            | undef                | string                 | string for the description for the DS   |
| $descr_len        | 12                   | integer                | length of the description               |
| $filename         | undef                | filename               | file to use for the RRD                 |
| $unit_text        | ''                   | string                 | text for the unit area of the graph     |
| $colours          | rainbow_stats_purple | palette name           | the graphs colour palette to use        |
| $munge            | false                | boolean                | if the results should be munged         |
| $munge_opts       | 86400,/              | RRD math               | the math to use for munging the results |
| $no_hourly        | false                | boolean                | do not display hourly average           |
| $no_hourly_min    | true                 | boolean                | do not display hourly min               |
| $no_hourly_max    | true                 | boolean                | do not display hourly max               |
| $no_daily         | false                | boolean                | do not display daily average            |
| $no_daily_min     | true                 | boolean                | do not display daily min                |
| $no_daily_max     | true                 | boolean                | do not display daily max                |
| $no_weekly        | false                | boolean                | do not display weekly average           |
| $no_weekly_min    | true                 | boolean                | do not display weekly min               |
| $no_weekly_max    | true                 | boolean                | do not display weekly max               |
| $no_percentile    | false                | boolean                | do not display percentile for 25/50/75  |
| $no_percentile_x0 | true                 | boolean                | display percentile x0                   |
| $percentile_x0    | 90                   | integer. 0 <= x <= 100 | percentile x0 value                     |
| $no_percentile_x1 | true                 | boolean                | display percentile x1                   |
| $percentile_x1    | 95                   | integer, 0 <= x <= 100 | percentile x1 value                     |

Config options are as below.

| Options                       | Default | Type    | Function                    |
|-------------------------------|---------|---------|-----------------------------|
| graph_stat_percentile_disable | false   | boolean | set $no_percentile globally |

## generic_stats_options.inc.php

`includes/html/graphs/generic_stats_options.inc.php` provides a front
end to `generic_stats.inc.php`. It translates variables passed to it
via the URL into options for the graph.

If any of the variables are not specified or do not match what is
expected for the type, then the default is used.

| Mapped From                       | Mapped To         | Default | Type                   | Config Variable                      |
|-----------------------------------|-------------------|---------|------------------------|--------------------------------------|
| $vars['gstats_no_hourly']         | $no_hourly        | false   | boolean, 0/1           | graph_stat_no_hourly_default         |
| $vars['no_hourly']                | $no_hourly_min    | true    | boolean, 0/1           | graph_stat_no_hourly_min_default     |
| $vars['gstats_no_hourly_max']     | $no_hourly_max    | true    | boolean, 0/1           | graph_stat_no_hourly_max_default     |
| $vars['gstats_no_daily']          | $no_daily         | false   | boolean, 0/1           | graph_stat_no_daily_default          |
| $vars['gstats_no_daily_min']      | $no_daily_min     | true    | boolean, 0/1           | graph_stat_no_daily_min_default      |
| $vars['gstats_no_daily_max']      | no_daily_max      | true    | boolean, 0/1           | graph_stat_no_daily_max_default      |
| $vars['gstats_no_weekly']         | $no_weekly        | false   | boolean, 0/1           | graph_stat_no_weekly_default         |
| $vars['gstats_no_weekly_min']     | $no_weekly_min    | true    | boolean, 0/1           | graph_stat_no_weekly_min_default     |
| $vars['gstats_no_weekly_max']     | $no_weekly_max    | true    | boolean, 0/1           | graph_stat_no_weekly_max_default     |
| $vars['gstats_no_percentile']     | $no_percentile    | false   | boolean, 0/1           | graph_stat_no_percentile_default     |
| $vars['gstats_no_percentile_x0']  | $no_percentile_x0 | true    | boolean, 0/1           | graph_stat_no_percentile_x0_default  |
| $vars['gstats_percentile_x0_val'] | $percentile_x0    | 90      | integer, 0 <= x <= 100 | graph_stat_percentile_x0_val_default |
| $vars['gstats_no_percentile_x1']  | $no_percentile_x1 | true    | boolean, 0/1           | graph_stat_no_percentile_x1_default  |
| $vars['gstats_percentile_x1_val'] | $percentile_x1    | 95      | integer, 0 <= x <= 100 | graph_stat_percentile_x1_val_default |


See `includes/html/pages/device/apps/logsize.inc.php` as a example of
making use of this.

The app page portion is inited like below...

```php
$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'logsize',
];

require 'includes/html/graphs/generic_stats_options_init.inc.php';
```

And then in the options area, just include this to add the options row
for it.

```php
require 'includes/html/graphs/generic_stats_options_line.inc.php';
```

Then finally in the graph setup setion this...

```php
    $graph_array['gstats_no_hourly'] = $vars['gstats_no_hourly'];
    $graph_array['gstats_no_hourly_min'] = $vars['gstats_no_hourly_min'];
    $graph_array['gstats_no_hourly_max'] = $vars['gstats_no_hourly_max'];
    $graph_array['gstats_no_daily'] = $vars['gstats_no_daily'];
    $graph_array['gstats_no_daily_min'] = $vars['gstats_no_daily_min'];
    $graph_array['gstats_no_daily_max'] = $vars['gstats_no_daily_max'];
    $graph_array['gstats_no_weekly'] = $vars['gstats_no_weekly'];
    $graph_array['gstats_no_weekly_min'] = $vars['gstats_no_weekly_min'];
    $graph_array['gstats_no_weekly_max'] = $vars['gstats_no_weekly_max'];
    $graph_array['gstats_no_percentile'] = $vars['gstats_no_percentile'];
    $graph_array['gstats_no_percentile_x0'] = $vars['gstats_no_percentile_x0'];
    $graph_array['gstats_percentile_x0_val'] = $vars['gstats_percentile_x0_val'];
    $graph_array['gstats_no_percentile_x1'] = $vars['gstats_no_percentile_x1'];
    $graph_array['gstats_percentile_x1_val'] = $vars['gstats_percentile_x1_val'];
```

And for a graph something like this...

```
<?php

$unit_text = 'Bytes';
$descr = 'Max Size';
$ds = 'max_size';

require 'logsize-common.inc.php';

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats_options.inc.php';
```
