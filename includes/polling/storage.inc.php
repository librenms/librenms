<?php

use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

$storage_cache = $storage_cache ?? [];
foreach (dbFetchRows('SELECT * FROM storage WHERE device_id = ?', [$device['device_id']]) as $storage) {
    $descr = $storage['storage_descr'];
    $mib = $storage['storage_mib'];

    echo 'Storage ' . $descr . ': ' . $mib . "\n\n\n\n";

    $rrd_name = ['storage', $mib, $descr];
    $rrd_def = RrdDefinition::make()
        ->addDataset('used', 'GAUGE', 0)
        ->addDataset('free', 'GAUGE', 0);

    $file = \LibreNMS\Config::get('install_dir') . '/includes/polling/storage/' . $mib . '.inc.php';
    if (is_file($file)) {
        include $file;
    }

    d_echo($storage);

    $percent = Number::calculatePercent($storage['used'], $storage['size'], 0);
    echo $percent . '% ';

    $fields = [
        'used' => $storage['used'],
        'free' => $storage['free'],
    ];

    $tags = compact('mib', 'descr', 'rrd_name', 'rrd_def');
    data_update($device, 'storage', $tags, $fields);

    // NOTE: casting to string for mysqli bug (fixed by mysqlnd)
    $update = dbUpdate(['storage_used' => (string) $storage['used'], 'storage_free' => (string) $storage['free'], 'storage_size' => (string) $storage['size'], 'storage_units' => (int) $storage['units'], 'storage_perc' => (int) $percent], 'storage', '`storage_id` = ?', [$storage['storage_id']]);

    echo "\n";
}//end foreach

unset($storage, $storage_cache);
