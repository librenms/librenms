<?php

$gstat_val_vars = [
    'gstats_percentile_x0_val' => 'Percentile X0 Val',
    'gstats_percentile_x1_val' => 'Percentile X1 Val',
];

$gstat_vars = [
    'gstats_no_hourly' => 'Hourly',
    'gstats_no_hourly_max' => 'Hourly Max',
    'gstats_no_daily' => 'Daily',
    'gstats_no_daily_max' => 'Daily Max',
    'gstats_no_weekly' => 'Weekly',
    'gstats_no_weekly_max' => 'Weekly Max',
    'gstats_no_percentile' => 'Percentile 25/50/75',
    'gstats_percentile_x0' => 'Percentile ' . $vars['gstats_percentile_x0_val'],
    'gstats_percentile_x0_val' => 'Percentile X0 Val',
    'gstats_percentile_x1' => 'Percentile ' . $vars['gstats_percentile_x1_val'],
    'gstats_percentile_x1_val' => 'Percentile X1 Val',
];

echo "<br>\nStat Options <b>::</b>\n";

foreach ($gstat_vars as $gstat_var => $gstat_var_descr) {
    if (!isset($gstat_val_vars[$gstat_var])) {
        echo $gstat_var_descr . ': ';
        $label = $vars[$gstat_var] == '1'
            ? '<span class="pagemenu-selected">Off</span>'
            : 'Off';
        $new_link_extra_array = $link_extra_array;
        $new_link_extra_array[$gstat_var]= 1;
        echo generate_link($label, $link_array, $new_link_extra_array) . "\n";

        $label = $vars[$gstat_var] == '0'
            ? '<span class="pagemenu-selected">On</span>'
            : 'On';
        $new_link_extra_array[$gstat_var] = 0;
        echo generate_link($label, $link_array, $new_link_extra_array) . "\n";
        echo "; \n";
    }
}
