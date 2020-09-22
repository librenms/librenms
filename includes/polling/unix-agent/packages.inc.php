<?php

$pkgs_id = [];
$pkgs_db_id = [];

// RPM
if (! empty($agent_data['rpm'])) {
    echo "\nRPM Packages: ";
    // Build array of existing packages
    $manager = 'rpm';

    $pkgs_db_db = dbFetchRows('SELECT * FROM `packages` WHERE `device_id` = ?', [$device['device_id']]);
    foreach ($pkgs_db_db as $pkg_db) {
        $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['id'] = $pkg_db['pkg_id'];
        $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['status'] = $pkg_db['status'];
        $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['size'] = $pkg_db['size'];
        $pkgs_db_id[$pkg_db['pkg_id']]['text'] = $pkg_db['manager'] . '-' . $pkg_db['name'] . '-' . $pkg_db['arch'] . '-' . $pkg_db['version'] . '-' . $pkg_db['build'];
        $pkgs_db_id[$pkg_db['pkg_id']]['manager'] = $pkg_db['manager'];
        $pkgs_db_id[$pkg_db['pkg_id']]['name'] = $pkg_db['name'];
        $pkgs_db_id[$pkg_db['pkg_id']]['arch'] = $pkg_db['arch'];
        $pkgs_db_id[$pkg_db['pkg_id']]['version'] = $pkg_db['version'];
        $pkgs_db_id[$pkg_db['pkg_id']]['build'] = $pkg_db['build'];
    }

    foreach (explode("\n", $agent_data['rpm']) as $package) {
        [$name, $version, $build, $arch, $size] = explode(' ', $package);
        $pkgs[$manager][$name][$arch][$version][$build]['manager'] = $manager;
        $pkgs[$manager][$name][$arch][$version][$build]['name'] = $name;
        $pkgs[$manager][$name][$arch][$version][$build]['arch'] = $arch;
        $pkgs[$manager][$name][$arch][$version][$build]['version'] = $version;
        $pkgs[$manager][$name][$arch][$version][$build]['build'] = $build;
        $pkgs[$manager][$name][$arch][$version][$build]['size'] = $size;
        $pkgs[$manager][$name][$arch][$version][$build]['status'] = '1';
        $text = $manager . '-' . $name . '-' . $arch . '-' . $version . '-' . $build;
        $pkgs_id[] = $pkgs[$manager][$name][$arch][$version][$build];
    }
}//end if

// DPKG
if (! empty($agent_data['dpkg'])) {
    echo "\nDEB Packages: ";
    // Build array of existing packages
    $manager = 'deb';

    $pkgs_db_db = dbFetchRows('SELECT * FROM `packages` WHERE `device_id` = ?', [$device['device_id']]);
    foreach ($pkgs_db_db as $pkg_db) {
        $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['id'] = $pkg_db['pkg_id'];
        $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['status'] = $pkg_db['status'];
        $pkgs_db[$pkg_db['manager']][$pkg_db['name']][$pkg_db['arch']][$pkg_db['version']][$pkg_db['build']]['size'] = $pkg_db['size'];
        $pkgs_db_id[$pkg_db['pkg_id']]['text'] = $pkg_db['manager'] . '-' . $pkg_db['name'] . '-' . $pkg_db['arch'] . '-' . $pkg_db['version'] . '-' . $pkg_db['build'];
        $pkgs_db_id[$pkg_db['pkg_id']]['manager'] = $pkg_db['manager'];
        $pkgs_db_id[$pkg_db['pkg_id']]['name'] = $pkg_db['name'];
        $pkgs_db_id[$pkg_db['pkg_id']]['arch'] = $pkg_db['arch'];
        $pkgs_db_id[$pkg_db['pkg_id']]['version'] = $pkg_db['version'];
        $pkgs_db_id[$pkg_db['pkg_id']]['build'] = $pkg_db['build'];
    }

    foreach (explode("\n", $agent_data['dpkg']) as $package) {
        [$name, $version, $arch, $size] = explode(' ', $package);
        $build = '';
        $pkgs[$manager][$name][$arch][$version][$build]['manager'] = $manager;
        $pkgs[$manager][$name][$arch][$version][$build]['name'] = $name;
        $pkgs[$manager][$name][$arch][$version][$build]['arch'] = $arch;
        $pkgs[$manager][$name][$arch][$version][$build]['version'] = $version;
        $pkgs[$manager][$name][$arch][$version][$build]['build'] = $build;
        $pkgs[$manager][$name][$arch][$version][$build]['size'] = ($size * 1024);
        $pkgs[$manager][$name][$arch][$version][$build]['status'] = '1';
        $text = $manager . '-' . $name . '-' . $arch . '-' . $version . '-' . $build;
        $pkgs_id[] = $pkgs[$manager][$name][$arch][$version][$build];
    }
}//end if

// This is run for all "packages" and is common to RPM/DEB/etc
foreach ($pkgs_id as $pkg) {
    $name = $pkg['name'];
    $version = $pkg['version'];
    $build = $pkg['build'];
    $arch = $pkg['arch'];
    $size = $pkg['size'];

    // echo(str_pad($name, 20)." ".str_pad($version, 10)." ".str_pad($build, 10)." ".$arch."\n");
    // echo($name." ");
    if (is_array($pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']])) {
        // FIXME - packages_history table
        $id = $pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]['id'];
        if ($pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]['status'] != '1') {
            $pkg_update['status'] = '1';
        }

        if ($pkgs_db[$pkg['manager']][$pkg['name']][$pkg['arch']][$pkg['version']][$pkg['build']]['size'] != $size) {
            $pkg_update['size'] = $size;
        }

        if (! empty($pkg_update)) {
            dbUpdate($pkg_update, 'packages', '`pkg_id` = ?', [$id]);
            echo 'u';
        } else {
            echo '.';
        }

        unset($pkgs_db_id[$id]);
    } else {
        if (count($pkgs[$manager][$name][$arch], 1) > '10' || count($pkgs_db[$manager][$name][$arch], 1) == '0') {
            dbInsert(
                [
                    'device_id' => $device['device_id'],
                    'name'      => $name,
                    'manager'   => $manager,
                    'status'    => 1,
                    'version'   => $version,
                    'build'     => $build,
                    'arch'      => $arch,
                    'size'      => $size,
                ],
                'packages'
            );
            if ($build != '') {
                $dbuild = '-' . $build;
            } else {
                $dbuild = '';
            }

            echo '+' . $name . '-' . $version . $dbuild . '-' . $arch;
            log_event('Package installed: ' . $name . ' (' . $arch . ') version ' . $version . $dbuild, $device, 'package', 3);
        } elseif (count($pkgs_db[$manager][$name][$arch], 1)) {
            $pkg_c = dbFetchRow('SELECT * FROM `packages` WHERE `device_id` = ? AND `manager` = ? AND `name` = ? and `arch` = ? ORDER BY version DESC, build DESC', [$device['device_id'], $manager, $name, $arch]);
            if ($pkg_c['build'] != '') {
                $pkg_c_dbuild = '-' . $pkg_c['build'];
            } else {
                $pkg_c_dbuild = '';
            }

            echo 'U(' . $pkg_c['name'] . '-' . $pkg_c['version'] . $pkg_c_dbuild . '|' . $name . '-' . $version . $dbuild . ')';
            $pkg_update = [
                'version' => $version,
                'build'   => $build,
                'status'  => '1',
                'size'    => $size,
            ];
            dbUpdate($pkg_update, 'packages', '`pkg_id` = ?', [$pkg_c['pkg_id']]);
            log_event('Package updated: ' . $name . ' (' . $arch . ') from ' . $pkg_c['version'] . $pkg_c_dbuild . ' to ' . $version . $dbuild, $device, 'package', 3);
            unset($pkgs_db_id[$pkg_c['pkg_id']]);
        }//end if
    }//end if
    unset($pkg_update);
}//end foreach

// Packages
foreach ($pkgs_db_id as $id => $pkg) {
    dbDelete('packages', '`pkg_id` =  ?', [$id]);
    echo '-' . $pkg['text'];
    log_event('Package removed: ' . $pkg['name'] . ' ' . $pkg['arch'] . ' ' . $pkg['version'] . ($pkg['build'] != '' ? '-' . $pkg['build'] : ''), $device, 'package', 3);
}

echo "\n";

unset($pkg);
unset($pkgs_db_id);
unset($pkg_c);
unset($pkgs);
unset($pkgs_db);
unset($pkgs_db_db);
