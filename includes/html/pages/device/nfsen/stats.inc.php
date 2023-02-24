<?php

print_optionbar_start();

echo '<form action = "' . \LibreNMS\Util\Url::generate($link_array, ['nfsen' => 'stats']) . '" iOCd = "FlowStats" method = "SUBMIT">';

echo 'Top N:
<select name = "topN" id = "topN" size = 1>
';

$option_default = $vars['topN'] ?? \LibreNMS\Config::get('nfsen_top_default');

$option_int = 0;
foreach (\LibreNMS\Config::get('nfsen_top_N') as $option) {
    if (strcmp($option_default, $option) == 0) {
        echo '<option value = "' . $option . '" selected>' . $option . '</option>';
    } else {
        echo '<option value = "' . $option . '">' . $option . '</option>';
    }
}

echo '
</select>
During the last:
<select name = "lastN" id = "lastN" size = 1>
';

$option_keys = array_keys(\LibreNMS\Config::get('nfsen_lasts'));
$options = \LibreNMS\Config::get('nfsen_lasts');
foreach ($option_keys as $option) {
    if (strcmp($option_default, $option) == 0) {
        echo '<option value = "' . $option . '" selected>' . $options[$option] . '</option>';
    } else {
        echo '<option value = "' . $option . '">' . $options[$option] . '</option>';
    }
}

echo '
</select>
, Stat Type:
<select name = "stattype" id = "StatTypeSelector" size = 1>
';

$option_default = $vars['stattype'] ?? \LibreNMS\Config::get('nfsen_stats_default');

$stat_types = [
    'record'=>'Flow Records',
    'ip'=>'Any IP Address',
    'srcip'=>'SRC IP Address',
    'dstip'=>'DST IP Address',
    'port'=>'Any Port',
    'srcport'=>'SRC Port',
    'dstport'=>'DST Port',
    'srctos'=>'SRC TOS',
    'dsttos'=>'DST TOS',
    'tos'=>'TOS',
    'as'=>'AS',
    'srcas'=>'SRC AS',
    'dstas'=>'DST AS',
];

// puts together the drop down options
foreach ($stat_types as $option => $descr) {
    if (strcmp($option_default, $option) == 0) {
        echo '<option value = "' . $option . '" selected>' . $descr . "</option>\n";
    } else {
        echo '<option value = "' . $option . '">' . $descr . "</option>\n";
    }
}

echo '
</select>
, Order By:
<select name = "statorder" id = "statorder" sizeOC = 1>
';

$option_default = \LibreNMS\Config::get('nfsen_order_default');
if (isset($vars['statorder'])) {
    $option_default = $vars['statorder'];
}

// WARNING: order is relevant as it has to match the
// check later in the process part of this page.
$order_types = [
    'flows'=>1,
    'packets'=>1,
    'bytes'=>1,
    'pps'=>1,
    'bps'=>1,
    'bpp'=>1,
];

// puts together the drop down options
foreach ($order_types as $option => $descr) {
    if (strcmp($option_default, $option) == 0) {
        echo '<option value = "' . $option . '" selected>' . $option . "</option>\n";
    } else {
        echo '<option value = "' . $option . '">' . $option . "</option>\n";
    }
}

echo '
</select>
<input type = "submit" name = "process" value = "process" size = "1">
';
echo '</form>';

print_optionbar_end();

// process stuff now if we the button was clicked on
if (isset($vars['process'])) {
    // Make sure we have a sane value for lastN
    $lastN = 900;
    if (isset($vars['lastN']) &&
         is_numeric($vars['lastN']) &&
         ($vars['lastN'] <= \LibreNMS\Config::get('nfsen_last_max'))
        ) {
        $lastN = $vars['lastN'];
    }

    // Make sure we have a sane value for lastN
    $topN = 20; // The default if not set or something invalid is set
    if (isset($vars['topN']) &&
         is_numeric($vars['topN']) &&
         ($vars['topN'] <= \LibreNMS\Config::get('nfsen_top_max'))
        ) {
        $topN = $vars['topN'];
    }

    // Handle the stat order.
    $stat_order = 'pps'; // The default if not set or something invalid is set
    if (isset($vars['statorder']) && isset($order_types[$vars['statorder']])) {
        $stat_order = $vars['statorder'];
    }

    // Handle the stat type.
    $stat_type = 'srcip'; // The default if not set or something invalid is set
    if (isset($vars['stattype']) && isset($stat_types[$vars['stattype']])) {
        $stat_type = $vars['stattype'];
    }

    $current_time = lowest_time(time() - 300);
    $last_time = lowest_time($current_time - $lastN - 300);

    $command = \LibreNMS\Config::get('nfdump') . ' -M ' . nfsen_live_dir($device['hostname']) . ' -T -R ' .
             time_to_nfsen_subpath($last_time) . ':' . time_to_nfsen_subpath($current_time) .
             ' -n ' . $topN . ' -s ' . $stat_type . '/' . $stat_order;

    echo '<pre>';
    system($command);
    echo '</pre>';
}
