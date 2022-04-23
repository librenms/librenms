<?php

print_optionbar_start();

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'netflow',
];

echo generate_link('General', $link_array, ['nfsen' => 'general']);
echo '|';
echo generate_link('Stats', $link_array, ['nfsen' => 'stats']);

$printedChannel = false;
$nfsen_hostname = nfsen_hostname($device['hostname']);
foreach (\LibreNMS\Config::get('nfsen_rrds') as $nfsenDir) {
    $hostDir = $nfsenDir . '/' . $nfsen_hostname . '/';
    if (is_dir($hostDir)) {
        $nfsenRRDchannelGlob = $hostDir . '*.rrd';
        foreach (glob($nfsenRRDchannelGlob) as $nfsenRRD) {
            $channel = str_replace([$hostDir, '.rrd'], '', $nfsenRRD);

            if (! $printedChannel) {
                echo '|Channels:';
                $printedChannel = true;
            } else {
                echo ',';
            }

            if ($vars['channel'] == $channel) {
                $channelFilter = $hostDir . $channel . '-filter.txt';
            }

            echo generate_link($channel, $link_array, ['nfsen' => 'channel', 'channel' => $channel]);
        }
    }
}

print_optionbar_end();

if (! $vars['nfsen']) {
    $vars['nfsen'] = 'general';
}

if (is_file('includes/html/pages/device/nfsen/' . $vars['nfsen'] . '.inc.php')) {
    include 'includes/html/pages/device/nfsen/' . $vars['nfsen'] . '.inc.php';
} else {
    include 'includes/html/pages/device/nfsen/general.inc.php';
}

$pagetitle[] = 'Netflow';
