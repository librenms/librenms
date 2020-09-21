<?php

$no_refresh = true;

// FIXME - do this in a function and/or do it in graph-realtime.php
if (! isset($vars['interval'])) {
    if ($device['os'] == 'linux') {
        $vars['interval'] = '15';
    } else {
        $vars['interval'] = '2';
    }
}

print_optionbar_start();

echo 'Polling Interval: ';

foreach ([0.25, 1, 2, 5, 15, 60] as $interval) {
    echo $thinger;
    if ($vars['interval'] == $interval) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($interval . 's', $link_array, ['view' => 'realtime', 'interval' => $interval]);
    if ($vars['interval'] == $interval) {
        echo '</span>';
    }

    $thinger = ' | ';
}

print_optionbar_end();

?>

<div align="center" style="margin: 30px;">
<object data="graph-realtime.php?type=bits&id=<?php echo $port['port_id'] . '&interval=' . $vars['interval']; ?>" type="image/svg+xml" width="1000" height="400">
<param name="src" value="graph.php?type=bits&id=<?php echo $port['port_id'] . '&interval=' . $vars['interval']; ?>" />
Your browser does not support the type SVG! You need to either use Firefox or download the Adobe SVG plugin.
</object>
</div>
