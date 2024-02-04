<?php

$gstat_val_vars = [
    'gstats_percentile_x0_val' => 'Percentile X0 Val',
    'gstats_percentile_x1_val' => 'Percentile X1 Val',
];

$gstat_vars = [
    'gstats_no_hourly' => 'Avg',
    'gstats_no_hourly_min' => 'Min',
    'gstats_no_hourly_max' => 'Max',
    'gstats_no_daily' => 'Avg',
    'gstats_no_daily_min' => 'Min',
    'gstats_no_daily_max' => 'Max',
    'gstats_no_weekly' => 'Avg',
    'gstats_no_weekly_min' => 'Min',
    'gstats_no_weekly_max' => 'Max',
    'gstats_no_percentile' => '25/50/75',
    'gstats_no_percentile_x0' => $vars['gstats_percentile_x0_val'],
    'gstats_no_percentile_x1' => $vars['gstats_percentile_x1_val'],
];

echo "<br>\nStat Options <b>::</b>\n";

$gstat_var_start = [
    'gstats_no_hourly' => 'Hourly: ',
    'gstats_no_hourly_min' => '; ',
    'gstats_no_hourly_max' => '; ',
    'gstats_no_daily' => '| Daily: ',
    'gstats_no_daily_min' => '; ',
    'gstats_no_daily_max' => '; ',
    'gstats_no_weekly' => '| Weekly: ',
    'gstats_no_weekly_min' => ';',
    'gstats_no_weekly_max' => ';',
    'gstats_no_percentile' => '| Percentile:',
    'gstats_no_percentile_x0' => '; ',
    'gstats_no_percentile_x1' => '; ',
];

foreach ($gstat_vars as $gstat_var => $gstat_var_descr) {
    if (! isset($gstat_val_vars[$gstat_var])) {
        echo $gstat_var_start[$gstat_var];
        echo $gstat_var_descr . ': ';
        $label = $vars[$gstat_var] == '1'
            ? '<span class="pagemenu-selected">Off</span>'
            : 'Off';
        $new_link_extra_array = $link_extra_array;
        $new_link_extra_array[$gstat_var] = 1;
        echo generate_link($label, $link_array, $new_link_extra_array) . "\n";

        $label = $vars[$gstat_var] == '0'
            ? '<span class="pagemenu-selected">On</span>'
            : 'On';
        $new_link_extra_array[$gstat_var] = 0;
        echo generate_link($label, $link_array, $new_link_extra_array) . "\n";
        echo "\n";
    }
}
