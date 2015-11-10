<?php

if (!empty($agent_data['app']['ceph'])) {

    $ceph_rrddir = join('/', array($config['rrd_dir'], $device['hostname']));

    foreach (explode('<', $agent_raw) as $section) {
        if (empty($section))
            continue;
        list($section, $data) = explode('>', $section);
        if ($section == "poolstats") {
            foreach (explode("\n", $data) as $line) {
                if (empty($line))
                    continue;
                list($pool,$ops,$wrbytes,$rbytes) = explode(':', $line);
                $ceph_rrd = $ceph_rrddir.'/app-ceph-'.$app['app_id'].'-pool-'.$pool.'.rrd';
                if (!is_file($ceph_rrd)) {
                    rrdtool_create(
                        $ceph_rrd,
                        '--step 300 \
                        DS:ops:GAUGE:600:0:U \
                        DS:wrbytes:GAUGE:600:0:U \
                        DS:rbytes:GAUGE:600:0:U '.$config['rrd_rra']
                    );
                }

                print "Ceph Pool: $pool, IOPS: $ops, Wr bytes: $wrbytes, R bytes: $rbytes\n";
                rrdtool_update($ceph_rrd, array("ops" => $ops, "wrbytes" => $wrbytes, "rbytes" => $rbytes));
            }
        }
        elseif ($section == "osdperformance") {
            foreach (explode("\n", $data) as $line) {
                if (empty($line))
                    continue;
                list($osd,$apply,$commit) = explode(':', $line);
                $ceph_rrd = $ceph_rrddir.'/app-ceph-'.$app['app_id'].'-osd-'.$osd.'.rrd';
                if (!is_file($ceph_rrd)) {
                    rrdtool_create(
                        $ceph_rrd,
                        '--step 300 \
                        DS:apply_ms:GAUGE:600:0:U \
                        DS:commit_ms:GAUGE:600:0:U '.$config['rrd_rra']
                    );
                }

                print "Ceph OSD: $osd, Apply: $apply, Commit: $commit\n";
                rrdtool_update($ceph_rrd, array("apply_ms" => $apply, "commit_ms" => $commit));
            }
        }
        elseif ($section == "df") {
            foreach (explode("\n", $data) as $line) {
                if (empty($line))
                    continue;
                list($pool,$avail,$used,$objects) = explode(':', $line);
                $ceph_rrd = $ceph_rrddir.'/app-ceph-'.$app['app_id'].'-df-'.$pool.'.rrd';
                if (!is_file($ceph_rrd)) {
                    rrdtool_create(
                        $ceph_rrd,
                        '--step 300 \
                        DS:avail:GAUGE:600:0:U \
                        DS:used:GAUGE:600:0:U \
                        DS:objects:GAUGE:600:0:U '.$config['rrd_rra']
                    );
                }

                print "Ceph Pool DF: $pool, Avail: $avail, Used: $used, Objects: $objects\n";
                rrdtool_update($ceph_rrd, array("avail" => $avail, "used" => $used, "objects" => $objects));
            }
        }
    }
}
