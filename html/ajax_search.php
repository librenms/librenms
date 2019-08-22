<?php

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!Auth::check()) {
    die('Unauthorized');
}

set_debug($_REQUEST['debug']);

$device = array();
$ports  = array();
$bgp    = array();
$limit  = (int)\LibreNMS\Config::get('webui.global_search_result_limit');

if (isset($_REQUEST['search'])) {
    $search = mres($_REQUEST['search']);
    header('Content-type: application/json');
    if (strlen($search) > 0) {
        $found = 0;

        if ($_REQUEST['type'] == 'group') {
            foreach (dbFetchRows("SELECT id,name FROM device_groups WHERE name LIKE ?", ["%$search%"]) as $group) {
                if ($_REQUEST['map']) {
                    $results[] = array(
                        'name'     => 'g:'.$group['name'],
                        'group_id' => $group['id'],
                    );
                } else {
                    $results[] = array('name' => $group['name']);
                }
            }

            die(json_encode($results));
        } elseif ($_REQUEST['type'] == 'alert-rules') {
            foreach (dbFetchRows("SELECT name FROM alert_rules WHERE name LIKE ?", ["%$search%"]) as $rules) {
                $results[] = array('name' => $rules['name']);
            }

            die(json_encode($results));
        } elseif ($_REQUEST['type'] == 'device') {
            // Device search
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT * FROM `devices` LEFT JOIN `locations` ON `locations`.`id` = `devices`.`location_id` WHERE `devices`.`hostname` LIKE ? OR `locations`.`location` LIKE ? OR `devices`.`sysName` LIKE ? OR `devices`.`purpose` LIKE ? OR `devices`.`notes` LIKE ? ORDER BY `devices`.hostname LIMIT " . $limit,
                    ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT * FROM `devices` AS `D` INNER JOIN `devices_perms` AS `P` ON `P`.`device_id` = `D`.`device_id` LEFT JOIN `locations` ON `locations`.`id` = `D`.`location_id` WHERE `P`.`user_id` = ? AND (D.`hostname` LIKE ? OR D.`sysName` LIKE ? OR `locations`.`location` LIKE ?) ORDER BY hostname LIMIT " . $limit,
                    [Auth::id(), "%$search%", "%$search%", "%$search%"]
                );
            }

            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['hostname'];
                    if ($_REQUEST['map'] != 1 && $result['sysName'] != $name && !empty($result['sysName'])) {
                        $name .= ' ('.$result['sysName'].') ';
                    }
                    if ($result['disabled'] == 1) {
                        $highlight_colour = '#808080';
                    } elseif ($result['ignored'] == 1 && $result['disabled'] == 0) {
                        $highlight_colour = '#000000';
                    } elseif ($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#ff0000';
                    } elseif ($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#008000';
                    }

                    if (Auth::user()->hasGlobalRead()) {
                        $num_ports = dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE device_id = ?', [$result['device_id']]);
                    } else {
                        $num_ports = dbFetchCell('SELECT COUNT(*) FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` AND `I`.`device_id` = `D`.`device_id` AND D.device_id = ?', [Auth::id(), $result['device_id']]);
                    }

                    $device[] = array(
                        'name'            => $name,
                        'device_id'       => $result['device_id'],
                        'url'             => generate_device_url($result),
                        'colours'         => $highlight_colour,
                        'device_ports'    => $num_ports,
                        'device_image'    => getIcon($result),
                        'device_hardware' => $result['hardware'],
                        'device_os' => \LibreNMS\Config::getOsSetting($result['os'], 'text'),
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        } elseif ($_REQUEST['type'] == 'ports') {
            // Search ports
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT `ports`.*,`devices`.* FROM `ports` LEFT JOIN `devices` ON  `ports`.`device_id` =  `devices`.`device_id` WHERE `ifAlias` LIKE ? OR `ifDescr` LIKE ? OR `ifName` LIKE ? ORDER BY ifDescr LIMIT ".$limit,
                    ["%$search%", "%$search%", "%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT DISTINCT(`I`.`port_id`), `I`.*, `D`.`hostname` FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P`, `ports_perms` AS `PP` WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id`) OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` AND `I`.`device_id` = `D`.`device_id`)) AND `D`.`device_id` = `I`.`device_id` AND (`ifAlias` LIKE ? OR `ifDescr` LIKE ? OR `ifName` LIKE ?) ORDER BY ifDescr LIMIT ".$limit,
                    [Auth::id(), Auth::id(), "%$search%", "%$search%", "%$search%"]
                );
            }

            if (count($results)) {
                $found = 1;

                foreach ($results as $result) {
                    $name        = $result['ifDescr'] == $result['ifAlias'] ? $result['ifName'] : $result['ifDescr'];
                    $description = display($result['ifAlias']);

                    if ($result['deleted'] == 0 && ($result['ignore'] == 0 || $result['ignore'] == 0) && ($result['ifInErrors_delta'] > 0 || $result['ifOutErrors_delta'] > 0)) {
                        // Errored ports
                        $port_colour = '#ffa500';
                    } elseif ($result['deleted'] == 0 && ($result['ignore'] == 1 || $result['ignore'] == 1)) {
                        // Ignored ports
                        $port_colour = '#000000';
                    } elseif ($result['deleted'] == 0 && $result['ifAdminStatus'] == 'down' && $result['ignore'] == 0 && $result['ignore'] == 0) {
                        // Shutdown ports
                        $port_colour = '#808080';
                    } elseif ($result['deleted'] == 0 && $result['ifOperStatus'] == 'down' && $result['ifAdminStatus'] == 'up' && $result['ignore'] == 0 && $result['ignore'] == 0) {
                        // Down ports
                        $port_colour = '#ff0000';
                    } elseif ($result['deleted'] == 0 && $result['ifOperStatus'] == 'up' && $result['ignore'] == 0 && $result['ignore'] == 0) {
                        // Up ports
                        $port_colour = '#008000';
                    }//end if

                    $ports[] = array(
                        'count'       => count($results),
                        'url'         => generate_port_url($result),
                        'name'        => $name,
                        'description' => $description,
                        'colours'     => $port_colour,
                        'hostname'    => $result['hostname'],
                        'port_id'     => $result['port_id'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($ports);
            die($json);
        } elseif ($_REQUEST['type'] == 'bgp') {
            // Search bgp peers
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT `bgpPeers`.*,`devices`.* FROM `bgpPeers` LEFT JOIN `devices` ON  `bgpPeers`.`device_id` =  `devices`.`device_id` WHERE `astext` LIKE ? OR `bgpPeerIdentifier` LIKE ? OR `bgpPeerRemoteAs` LIKE ? ORDER BY `astext` LIMIT " . $limit,
                    ["%$search%", "%$search%", "%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT `bgpPeers`.*,`D`.* FROM `bgpPeers`, `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` AND  `bgpPeers`.`device_id`=`D`.`device_id` AND  (`astext` LIKE ? OR `bgpPeerIdentifier` LIKE ? OR `bgpPeerRemoteAs` LIKE ?) ORDER BY `astext` LIMIT ".$limit,
                    [Auth::id(), "%$search%", "%$search%", "%$search%"]
                );
            }

            if (count($results)) {
                $found = 1;

                foreach ($results as $result) {
                    $name        = $result['bgpPeerIdentifier'];
                    $description = $result['astext'];
                    $remoteas    = $result['bgpPeerRemoteAs'];
                    $localas     = $result['bgpLocalAs'];

                    if ($result['bgpPeerAdminStatus'] == 'start' && $result['bgpPeerState'] != 'established') {
                        // Session active but errored
                        $port_colour = '#ffa500';
                    } elseif ($result['bgpPeerAdminStatus'] != 'start') {
                        // Session inactive
                        $port_colour = '#000000';
                    } elseif ($result['bgpPeerAdminStatus'] == 'start' && $result['bgpPeerState'] == 'established') {
                        // Session Up
                        $port_colour = '#008000';
                    }

                    if ($result['bgpPeerRemoteAs'] == $result['bgpLocalAs']) {
                        $bgp_image = '<i class="fa fa-square fa-lg icon-theme" aria-hidden="true"></i>';
                    } else {
                        $bgp_image = '<i class="fa fa-external-link-square fa-lg icon-theme" aria-hidden="true"></i>';
                    }

                    $bgp[] = array(
                        'count'       => count($results),
                        'url'         => generate_peer_url($result),
                        'name'        => $name,
                        'description' => $description,
                        'localas'     => $localas,
                        'bgp_image'   => $bgp_image,
                        'remoteas'    => $remoteas,
                        'colours'     => $port_colour,
                        'hostname'    => $result['hostname'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($bgp);
            die($json);
        } elseif ($_REQUEST['type'] == 'applications') {
            // Device search
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT * FROM `applications` INNER JOIN `devices` ON devices.device_id = applications.device_id WHERE `app_type` LIKE ? OR `hostname` LIKE ? ORDER BY hostname LIMIT ".$limit,
                    ["%$search%", "%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT * FROM `applications` INNER JOIN `devices` AS `D` ON `D`.`device_id` = `applications`.`device_id` INNER JOIN `devices_perms` AS `P` ON `P`.`device_id` = `D`.`device_id` WHERE `P`.`user_id` = ? AND (`app_type` LIKE ? OR `hostname` LIKE ?) ORDER BY hostname LIMIT ".$limit,
                    [Auth::id(), "%$search%", "%$search%"]
                );
            }

            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['app_type'];
                    if ($result['disabled'] == 1) {
                        $highlight_colour = '#808080';
                    } elseif ($result['ignored'] == 1 && $result['disabled'] == 0) {
                        $highlight_colour = '#000000';
                    } elseif ($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#ff0000';
                    } elseif ($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#008000';
                    }

                    $device[] = array(
                        'name'            => $name,
                        'hostname'        => $result['hostname'],
                        'app_id'          => $result['app_id'],
                        'device_id'       => $result['device_id'],
                        'colours'         => $highlight_colour,
                        'device_image'    => getIcon($result),
                        'device_hardware' => $result['hardware'],
                        'device_os' => \LibreNMS\Config::getOsSetting($result['os'], 'text'),
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        } elseif ($_REQUEST['type'] == 'munin') {
            // Device search
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT * FROM `munin_plugins` INNER JOIN `devices` ON devices.device_id = munin_plugins.device_id WHERE `mplug_type` LIKE ? OR `mplug_title` LIKE ? OR `hostname` LIKE ? ORDER BY hostname LIMIT ".$limit,
                    ["%$search%", "%$search%", "%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT * FROM `munin_plugins` INNER JOIN `devices` AS `D` ON `D`.`device_id` = `munin_plugins`.`device_id` INNER JOIN `devices_perms` AS `P` ON `P`.`device_id` = `D`.`device_id` WHERE `P`.`user_id` = ? AND (`mplug_type` LIKE ? OR `mplug_title` LIKE ? OR `hostname` LIKE ?) ORDER BY hostname LIMIT ".$limit,
                    [Auth::id(), "%$search%", "%$search%", "%$search%"]
                );
            }

            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['mplug_title'];
                    if ($result['disabled'] == 1) {
                        $highlight_colour = '#808080';
                    } elseif ($result['ignored'] == 1 && $result['disabled'] == 0) {
                        $highlight_colour = '#000000';
                    } elseif ($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#ff0000';
                    } elseif ($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#008000';
                    }

                    $device[] = array(
                        'name'            => $name,
                        'hostname'        => $result['hostname'],
                        'device_id'       => $result['device_id'],
                        'colours'         => $highlight_colour,
                        'device_image'    => getIcon($result),
                        'device_hardware' => $result['hardware'],
                        'device_os' => \LibreNMS\Config::getOsSetting($result['os'], 'text'),
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                        'plugin'          => $result['mplug_type'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        } elseif ($_REQUEST['type'] == 'iftype') {
            // Device search
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT `ports`.ifType FROM `ports` WHERE `ifType` LIKE ? GROUP BY ifType ORDER BY ifType LIMIT ".$limit,
                    ["%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT `I`.ifType FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P`, `ports_perms` AS `PP` WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id`) OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` AND `I`.`device_id` = `D`.`device_id`)) AND `D`.`device_id` = `I`.`device_id` AND (`ifType` LIKE ?) GROUP BY ifType ORDER BY ifType LIMIT ".$limit,
                    [Auth::id(), Auth::id(), "%$search%"]
                );
            }
            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $device[] = array(
                        'filter'            => $result['ifType'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        } elseif ($_REQUEST['type'] == 'bill') {
            // Device search
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    "SELECT `bills`.bill_id, `bills`.bill_name FROM `bills` WHERE `bill_name` LIKE ? OR `bill_notes` LIKE ? LIMIT ".$limit,
                    ["%$search%", "%$search%"]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT `bills`.bill_id, `bills`.bill_name FROM `bills` INNER JOIN `bill_perms` ON `bills`.bill_id = `bill_perms`.bill_id WHERE `bill_perms`.user_id = ? AND (`bill_name` LIKE ? OR `bill_notes` LIKE ?) LIMIT ".$limit,
                    [Auth::id(), "%$search%", "%$search%"]
                );
            }
            $json = json_encode($results);
            die($json);
        }//end if
    }//end if
}//end if
