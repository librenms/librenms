<?php

print_optionbar_start();

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'nfsen',
    );

echo generate_link('General', $link_array, array('nfsen' => 'general'));

$printedChannel=false;
$nfsenHostname=str_replace('.', $config['nfsen_split_char'], $device['hostname']);
foreach ($config['nfsen_rrds'] as $nfsenDir) {
    $hostDir=$nfsenDir.'/'.$nfsenHostname.'/';
    if (is_dir($hostDir)) {
        $nfsenRRDchannelGlob=$hostDir.'*.rrd';
        foreach (glob($nfsenRRDchannelGlob) as $nfsenRRD) {
            $channel = str_replace(array($hostDir, '.rrd'), '', $nfsenRRD);

            if (!$printedChannel) {
                echo '|Channels:';
                $printedChannel=true;
            } else {
                echo ',';
            }

            if ($vars['channel'] == $channel) {
                $channelFilter=$hostDir.$channel.'-filter.txt';
            }

            echo generate_link($channel, $link_array, array('nfsen' => 'channel', 'channel' => $channel));
        }
    }
}

print_optionbar_end();

$nfsen_type = basename($vars['nfsen'] ?? 'general');
if (is_file("includes/html/pages/device/nfsen/$nfsen_type.inc.php")) {
    include "includes/html/pages/device/nfsen/$nfsen_type.inc.php";
} else {
    include 'includes/html/pages/device/nfsen/general.inc.php';
}

$pagetitle[] = 'Netflow';
