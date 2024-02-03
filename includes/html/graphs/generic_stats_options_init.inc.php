<?php
//
// do not display hourly average
//
if (!isset($vars['gstats_no_hourly'])) {
     $vars['gstats_no_hourly'] = \LibreNMS\Config::get('graph_stat_no_hourly_default');
}

//
// do not display hourly max
//
if (isset($vars['gstats_no_hourly_max'])) {
    $vars['gstats_no_hourly_max'] = \LibreNMS\Config::get('graph_stat_no_hourly_max_default');
}

//
// do not display daily average
//
if (isset($vars['gstats_no_daily'])) {
    $vars['gstats_no_daily'] = \LibreNMS\Config::get('graph_stat_no_daily_default');
}

//
// do not display daily max
//
if (isset($vars['gstats_no_daily_max'])) {
    $vars['gstats_no_daily_max'] = \LibreNMS\Config::get('graph_stat_no_daily_max_default');
}

//
// do not display weekly average
//
if (isset($vars['gstats_no_weekly'])) {
    $vars['gstats_no_weekly'] = \LibreNMS\Config::get('graph_stat_no_weekly_default');
}

//
// do not display weekly max
//
if (isset($vars['gstats_no_weekly_max'])) {
    $vars['gstats_no_weekly_max'] = \LibreNMS\Config::get('graph_stat_no_weekly_max_default');
}

//
// do not display percentile
//
if (isset($vars['gstats_no_percentile'])) {
    $vars['gstats_no_percentile'] = \LibreNMS\Config::get('graph_stat_no_percentile_default');
}

//
// display percentile x0
//
if (isset($vars['gstats_percentile_x0'])) {
    $vars['gstats_percentile_x0'] = \LibreNMS\Config::get('graph_stat_percentile_x0_default');
}

//
// percential x0 value, default 90
//
if (isset($vars['gstats_percentile_x0_val'])) {
    $vars['gstats_percentile_x0_val'] = \LibreNMS\Config::get('graph_stat_percentile_x0_val_default');
}

//
// display percentile x1
//
if (isset($vars['gstats_percentile_x1'])) {
    $vars['gstats_percentile_x1'] = \LibreNMS\Config::get('graph_stat_percentile_x1_default');
}

//
// percential x1 value, default 95
//
if (!isset($vars['gstats_percentile_x1_val'])) {
    $vars['gstats_percentile_x1_val'] = \LibreNMS\Config::get('graph_stat_percentile_x1_val_default');
}
