<?php

$no_refresh = true;
if (! isset($vars['interval'])) {
    if ($device['os'] == 'linux') {
        $vars['interval'] = '15';
    } else {
        $vars['interval'] = '2';
    }
}

print_optionbar_start();

echo 'Polling Interval: ';

$sep = '';
foreach ([0.25, 1, 2, 5, 15, 60] as $interval) {
    echo $sep;
    if ($vars['interval'] == $interval) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($interval . 's', $link_array, ['view' => 'realtime', 'interval' => $interval]);
    if ($vars['interval'] == $interval) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

?>

<div align="center" style="margin: 30px;">
<object data="<?php echo route('realtime.graph', ['port' => $port['port_id'], 'interval' => $vars['interval']]); ?>" type="image/svg+xml" width="1000" height="400">
<param name="src" value="<?php echo route('realtime.graph', ['port' => $port['port_id'], 'interval' => $vars['interval']]); ?>" />
<a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options">Your webserver has header X-Frame-Options set to DENY. Please change to SAMEORIGIN for realtime graphs.</a>
</object>
</div>
