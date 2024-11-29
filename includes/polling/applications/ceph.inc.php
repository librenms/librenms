<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'ceph';

if (! empty($agent_data['app'][$name])) {
    $ceph_data = $agent_data['app'][$name];

    $metrics = [];
    foreach (explode('<', $ceph_data) as $section) {
        if (empty($section)) {
            continue;
        }
        [$section, $data] = explode('>', $section);

        if ($section == 'poolstats') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('ops', 'GAUGE', 0)
                ->addDataset('wrbytes', 'GAUGE', 0)
                ->addDataset('rbytes', 'GAUGE', 0);

            foreach (explode("\n", $data) as $line) {
                if (empty($line)) {
                    continue;
                }
                [$pool,$ops,$wrbytes,$rbytes] = explode(':', $line);
                echo "Ceph Pool: $pool, IOPS: $ops, Wr bytes: $wrbytes, R bytes: $rbytes\n";
                $fields = [
                    'ops' => $ops,
                    'wrbytes' => $wrbytes,
                    'rbytes' => $rbytes,
                ];
                $metrics["pool_$pool"] = $fields;
                $tags = [
                    'name' => $name,
                    'app_id' => $app->app_id,
                    'pool' => $pool,
                    'rrd_name' => ['app', $name, $app->app_id, 'pool', $pool],
                    'rrd_def' => $rrd_def,
                ];
                data_update($device, 'app', $tags, $fields);
            }
        } elseif ($section == 'osdperformance') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('apply_ms', 'GAUGE', 0)
                ->addDataset('commit_ms', 'GAUGE', 0);

            foreach (explode("\n", $data) as $line) {
                if (empty($line)) {
                    continue;
                }
                [$osd,$apply,$commit] = explode(':', $line);
                echo "Ceph OSD: $osd, Apply: $apply, Commit: $commit\n";
                $fields = [
                    'apply_ms' => $apply,
                    'commit_ms' => $commit,
                ];
                $metrics["osd_$osd"] = $fields;
                $tags = [
                    'name' => $name,
                    'app_id' => $app->app_id,
                    'osd' => $osd,
                    'rrd_name' => ['app', $name, $app->app_id, 'osd', $osd],
                    'rrd_def' => $rrd_def,
                ];
                data_update($device, 'app', $tags, $fields);
            }
        } elseif ($section == 'df') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('avail', 'GAUGE', 0)
                ->addDataset('used', 'GAUGE', 0)
                ->addDataset('objects', 'GAUGE', 0);

            foreach (explode("\n", $data) as $line) {
                if (empty($line)) {
                    continue;
                }
                [$df,$avail,$used,$objects] = explode(':', $line);
                echo "Ceph Pool DF: $df, Avail: $avail, Used: $used, Objects: $objects\n";
                $fields = [
                    'avail' => $avail,
                    'used' => $used,
                    'objects' => $objects,
                ];
                $metrics["df_$df"] = $fields;
                $tags = [
                    'name' => $name,
                    'app_id' => $app->app_id,
                    'df' => $df,
                    'rrd_name' => ['app', $name, $app->app_id, 'df', $df],
                    'rrd_def' => $rrd_def,
                ];
                data_update($device, 'app', $tags, $fields);
            }
        }
    }
    update_application($app, $ceph_data, $metrics);
}

unset($ceph_data, $metrics);
