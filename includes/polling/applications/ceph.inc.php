<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'ceph';
$metrics = [];

// Fetch data using Unix Agent or SNMP Extend
if (! empty($agent_data['app'][$name])) {
    echo "\nUsing Unix Agent data.\n";
    $ceph_data = $agent_data['app'][$name];
} else {
    echo "\nUsing SNMP Extend data.\n";
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.4.99.101.112.104'; // OID for SNMP Extend
    $ceph_data = snmp_get($device, $oid, $options);
    $ceph_data = preg_replace('/^<<<app-ceph>>>\n/', '', $ceph_data); // Remove header
}

// Exit if no data is available
if (empty($ceph_data)) {
    echo "No Ceph data available.\n";

    return;
}

// Enhanced section parsing
$lines = explode("\n", $ceph_data);
$current_section = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) {
        continue;
    }

    // Detect section headers
    if (preg_match('/^<<<(.+?)>>>$/', $line, $matches)) {
        $current_section = $matches[1];
        echo "\nDetected section: $current_section\n";
        continue;
    }
    if (strpos($line, '<') === 0 && strpos($line, '>') !== false) {
        $current_section = trim($line, '<>');
        echo "\nDetected subsection: $current_section\n";
        continue;
    }

    // Process data based on the current section
    switch ($current_section) {
        case 'poolstats':
            // Handling for Pool Stats
            $rrd_def = RrdDefinition::make()
                ->addDataset('ops', 'GAUGE', 0)
                ->addDataset('wrbytes', 'GAUGE', 0)
                ->addDataset('rbytes', 'GAUGE', 0);

            [$pool, $ops, $wrbytes, $rbytes] = explode(':', $line);
            echo "Ceph Pool: $pool, IOPS: $ops, Wr bytes: $wrbytes, R bytes: $rbytes\n";

            $fields = [
                'ops' => $ops,
                'wrbytes' => $wrbytes,
                'rbytes' => $rbytes,
            ];

            $tags = [
                'name' => $name,
                'pool' => $pool,
                'rrd_name' => ['app', $name, $app->app_id, 'pool', $pool],
                'rrd_def' => $rrd_def,
            ];

            save_to_datastore_and_rrd($device, $tags, $fields, $rrd_def);
            break;

        case 'osdperformance':
            // OSD Performance
            $rrd_def = RrdDefinition::make()
                ->addDataset('apply_ms', 'GAUGE', 0)
                ->addDataset('commit_ms', 'GAUGE', 0);

            [$osd, $apply, $commit] = explode(':', $line);
            echo "Ceph OSD: $osd, Apply Latency: $apply ms, Commit Latency: $commit ms\n";

            $fields = [
                'apply_ms' => $apply,
                'commit_ms' => $commit,
            ];

            $tags = [
                'name' => $name,
                'osd' => $osd,
                'rrd_name' => ['app', $name, $app->app_id, 'osd', $osd],
                'rrd_def' => $rrd_def,
            ];

            save_to_datastore_and_rrd($device, $tags, $fields, $rrd_def);
            break;

        case 'df':
            // Disk Usage
            $rrd_def = RrdDefinition::make()
                ->addDataset('avail', 'GAUGE', 0)
                ->addDataset('used', 'GAUGE', 0)
                ->addDataset('objects', 'GAUGE', 0);

            [$df, $avail, $used, $objects] = explode(':', $line);
            echo "Ceph DF: $df, Avail: $avail, Used: $used, Objects: $objects\n";

            $fields = [
                'avail' => $avail,
                'used' => $used,
                'objects' => $objects,
            ];

            $tags = [
                'name' => $name,
                'df' => $df,
                'rrd_name' => ['app', $name, $app->app_id, 'df', $df],
                'rrd_def' => $rrd_def,
            ];

            save_to_datastore_and_rrd($device, $tags, $fields, $rrd_def);
            break;

        default:
            echo "\nUnknown section: $current_section\n";
            break;
    }
}

// Helper function to save data to Datastore and RRD
function save_to_datastore_and_rrd($device, $tags, $fields, $rrd_def)
{
    echo " Saving to Datastore and RRD...\n";
    echo '  Tags: ' . json_encode($tags) . "\n";
    echo '  Fields: ' . json_encode($fields) . "\n";

    // Save to Datastore
    app('Datastore')->put($device, 'app', $tags, $fields);

    // Debug RRD path
    $rrd_path = '/opt/librenms/rrd/' . $device['hostname'] . '/' . implode('-', $tags['rrd_name']) . '.rrd';
    echo " RRD path: $rrd_path\n";

    if (! file_exists($rrd_path)) {
        echo " Creating RRD file: $rrd_path\n";
    }
}

// Update the application
update_application($app, $ceph_data, $metrics);
unset($ceph_data, $metrics);
