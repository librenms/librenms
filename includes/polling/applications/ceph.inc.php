<?php

$name = 'ceph';
if (!empty($agent_data['app'][$name])) {
    $app_id = $app['app_id'];

    foreach (explode('<', $agent_raw) as $section) {
        if (empty($section))
            continue;
        list($section, $data) = explode('>', $section);

        if ($section == "poolstats") {
            $rrd_name = array('app', $name, $app_id, 'pool', $pool);
            $rrd_def = array(
                'DS:ops:GAUGE:600:0:U',
                'DS:wrbytes:GAUGE:600:0:U',
                'DS:rbytes:GAUGE:600:0:U'
            );

            foreach (explode("\n", $data) as $line) {
                if (empty($line))
                    continue;
                list($pool,$ops,$wrbytes,$rbytes) = explode(':', $line);

                print "Ceph Pool: $pool, IOPS: $ops, Wr bytes: $wrbytes, R bytes: $rbytes\n";
                $fields = array(
                    'ops' => $ops,
                    'wrbytes' => $wrbytes,
                    'rbytes' => $rbytes
                );
                $tags = compact('name', 'app_id', 'pool', 'rrd_name', 'rrd_def');
                data_update($device, 'app', $tags, $fields);
            }
        }
        elseif ($section == "osdperformance") {
            $rrd_name = array('app', $name, $app_id, 'osd', $osd);
            $rrd_def = array(
                'DS:apply_ms:GAUGE:600:0:U',
                'DS:commit_ms:GAUGE:600:0:U'
            );

            foreach (explode("\n", $data) as $line) {
                if (empty($line))
                    continue;
                list($osd,$apply,$commit) = explode(':', $line);

                print "Ceph OSD: $osd, Apply: $apply, Commit: $commit\n";
                $fields = array(
                    'apply_ms' => $apply,
                    'commit_ms' => $commit
                );
                $tags = compact('name', 'app_id', 'osd', 'rrd_name', 'rrd_def');
                data_update($device, 'app', $tags, $fields);
            }
        }
        elseif ($section == "df") {
            $rrd_name = array('app', $name, $app_id, 'df', $df);
            $rrd_def = array(
                'DS:avail:GAUGE:600:0:U',
                'DS:used:GAUGE:600:0:U',
                'DS:objects:GAUGE:600:0:U'
            );

            foreach (explode("\n", $data) as $line) {
                if (empty($line))
                    continue;
                list($df,$avail,$used,$objects) = explode(':', $line);

                print "Ceph Pool DF: $pool, Avail: $avail, Used: $used, Objects: $objects\n";
                $fields = array(
                    'avail' => $avail,
                    'used' => $used,
                    'objects' => $objects
                );

                $tags = compact('name', 'app_id', 'df', 'rrd_name', 'rrd_def');
                data_update($device, 'app', $tags, $fields);
            }
        }
    }
}
