<?php

use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set($_REQUEST['debug']);

$device = [];
$ports = [];
$bgp = [];
$limit = (int) \LibreNMS\Config::get('webui.global_search_result_limit');

if (isset($_REQUEST['search'])) {
    $search = $_REQUEST['search'];
    header('Content-type: application/json');
    if (strlen($search) > 0) {
        $found = 0;

        if (! Auth::user()->hasGlobalRead()) {
            $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
            $perms_sql = '`D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids)) . ' AND ';
        } else {
            $device_ids = [];
            $perms_sql = '';
        }

        if ($_REQUEST['type'] == 'group') {
            foreach (dbFetchRows('SELECT id,name FROM device_groups WHERE name LIKE ?', ["%$search%"]) as $group) {
                if ($_REQUEST['map']) {
                    $results[] = [
                        'name'     => 'g:' . $group['name'],
                        'group_id' => $group['id'],
                    ];
                } else {
                    $results[] = ['name' => $group['name']];
                }
            }

            exit(json_encode($results));
        } elseif ($_REQUEST['type'] == 'alert-rules') {
            foreach (dbFetchRows('SELECT name FROM alert_rules WHERE name LIKE ?', ["%$search%"]) as $rules) {
                $results[] = ['name' => $rules['name']];
            }

            exit(json_encode($results));
        } elseif ($_REQUEST['type'] == 'device') {
            // Device search

            $query = 'SELECT *, `D`.`device_id` AS `device_id` FROM `devices` as `D`
                      LEFT JOIN `locations` AS `L` ON `L`.`id` = `D`.`location_id`';

            // user depending limitation
            if (! Auth::user()->hasGlobalRead()) {
                $query_args_list = $device_ids;
                $query_filter = $perms_sql;
            } else {
                $query_args_list = [];
                $query_filter = '';
            }

            // search filter
            $query_filter .= '(`D`.`hostname` LIKE ?
                              OR `L`.`location` LIKE ?
                              OR `D`.`sysName` LIKE ?
                              OR `D`.`purpose` LIKE ?
                              OR `D`.`notes` LIKE ?';
            $query_args_list = array_merge($query_args_list, ["%$search%", "%$search%", "%$search%",
                "%$search%", "%$search%", ]);

            if (\LibreNMS\Util\IPv4::isValid($search, false)) {
                $query .= ' LEFT JOIN `ports` AS `P` ON `P`.`device_id` = `D`.`device_id`
                                LEFT JOIN `ipv4_addresses` AS `V4` ON `V4`.`port_id` = `P`.`port_id`';
                $query_filter .= ' OR `V4`.`ipv4_address` LIKE ?
                                       OR `D`.`overwrite_ip` LIKE ?
                                       OR `D`.`ip` = ? ';
                $query_args_list = array_merge($query_args_list, ["%$search%", "%$search%", inet_pton($search)]);
            } elseif (\LibreNMS\Util\IPv6::isValid($search, false)) {
                $query .= ' LEFT JOIN `ports` AS `P` ON `P`.`device_id` = `D`.`device_id`
                                LEFT JOIN `ipv6_addresses` AS `V6` ON `V6`.`port_id` = `P`.`port_id`';
                $query_filter .= ' OR `V6`.`ipv6_address` LIKE ?
                                       OR `D`.`overwrite_ip` LIKE ?
                                       OR `D`.`ip` = ? ';
                $query_args_list = array_merge($query_args_list, ["%$search%", "%$search%", inet_pton($search)]);
            } elseif (ctype_xdigit($mac_search = str_replace([':', '-', '.'], '', $search))) {
                $query .= ' LEFT JOIN `ports` as `M` on `M`.`device_id` = `D`.`device_id`';
                $query_filter .= ' OR `M`.`ifPhysAddress` LIKE ? ';
                $query_args_list[] = "%$mac_search%";
            }

            $query_filter .= ')';

            // result limitation
            $query_args_list[] = $limit;
            $results = dbFetchRows($query .
                                   ' WHERE ' . $query_filter .
                                   ' GROUP BY `D`.`hostname`
                                     ORDER BY `D`.`hostname` LIMIT ?', $query_args_list);

            if (count($results)) {
                $found = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['hostname'];
                    if ($_REQUEST['map'] != 1 && $result['sysName'] != $name && ! empty($result['sysName'])) {
                        $name .= ' (' . $result['sysName'] . ') ';
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

                    $num_ports = dbFetchCell('SELECT COUNT(*) FROM `ports` AS `I`, `devices` AS `D` WHERE ' . $perms_sql . ' `I`.`device_id` = `D`.`device_id` AND `I`.`ignore` = 0 AND `I`.`deleted` = 0 AND `D`.`device_id` = ?', array_merge($device_ids, [$result['device_id']]));

                    $device[] = [
                        'name'            => $name,
                        'device_id'       => $result['device_id'],
                        'url'             => \LibreNMS\Util\Url::deviceUrl((int) $result['device_id']),
                        'colours'         => $highlight_colour,
                        'device_ports'    => $num_ports,
                        'device_image'    => getIcon($result),
                        'device_hardware' => $result['hardware'],
                        'device_os' => \LibreNMS\Config::getOsSetting($result['os'], 'text'),
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                    ];
                }//end foreach
            }//end if

            $json = json_encode($device);
            exit($json);
        } elseif ($_REQUEST['type'] == 'ports') {
            // Search ports
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    'SELECT `ports`.*,`devices`.* FROM `ports` LEFT JOIN `devices` ON  `ports`.`device_id` =  `devices`.`device_id` WHERE `ifAlias` LIKE ? OR `ifDescr` LIKE ? OR `ifName` LIKE ? ORDER BY ifDescr LIMIT ?',
                    ["%$search%", "%$search%", "%$search%", $limit]
                );
            } else {
                $results = dbFetchRows(
                    "SELECT DISTINCT(`I`.`port_id`), `I`.*, `D`.`hostname` FROM `ports` AS `I`, `devices` AS `D` WHERE $perms_sql `D`.`device_id` = `I`.`device_id` AND (`ifAlias` LIKE ? OR `ifDescr` LIKE ? OR `ifName` LIKE ?) ORDER BY ifDescr LIMIT ?",
                    array_merge($device_ids, ["%$search%", "%$search%", "%$search%", $limit])
                );
            }

            if (count($results)) {
                $found = 1;

                foreach ($results as $result) {
                    $name = $result['ifDescr'] == $result['ifAlias'] ? $result['ifName'] : $result['ifDescr'];
                    $description = \LibreNMS\Util\Clean::html($result['ifAlias'], []);

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

                    $ports[] = [
                        'count'       => count($results),
                        'url'         => generate_port_url($result),
                        'name'        => $name,
                        'description' => $description,
                        'colours'     => $port_colour,
                        'hostname'    => format_hostname($result),
                        'port_id'     => $result['port_id'],
                    ];
                }//end foreach
            }//end if

            $json = json_encode($ports);
            exit($json);
        } elseif ($_REQUEST['type'] == 'bgp') {
            // Search bgp peers
            $results = dbFetchRows(
                "SELECT `bgpPeers`.*,`D`.* FROM `bgpPeers`, `devices` AS `D` WHERE $perms_sql `bgpPeers`.`device_id`=`D`.`device_id` AND  (`astext` LIKE ? OR `bgpPeerIdentifier` LIKE ? OR `bgpPeerRemoteAs` LIKE ?) ORDER BY `astext` LIMIT ?",
                array_merge($device_ids, ["%$search%", "%$search%", "%$search%", $limit])
            );

            if (count($results)) {
                $found = 1;

                foreach ($results as $result) {
                    $name = $result['bgpPeerIdentifier'];
                    $description = $result['astext'];
                    $remoteas = $result['bgpPeerRemoteAs'];
                    $localas = $result['bgpLocalAs'];

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
                        $bgp_image = 'fa fa-square fa-lg icon-theme';
                    } else {
                        $bgp_image = 'fa fa-external-link-square fa-lg icon-theme';
                    }

                    $bgp[] = [
                        'count'       => count($results),
                        'url'         => \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $result['device_id'], 'tab' => 'routing', 'proto' => 'bgp'], []),
                        'name'        => $name,
                        'description' => $description,
                        'localas'     => $localas,
                        'bgp_image'   => $bgp_image,
                        'remoteas'    => $remoteas,
                        'colours'     => $port_colour,
                        'hostname'    => format_hostname($result),
                    ];
                }//end foreach
            }//end if

            $json = json_encode($bgp);
            exit($json);
        } elseif ($_REQUEST['type'] == 'applications') {
            // Device search
            $results = dbFetchRows(
                "SELECT * FROM `applications` INNER JOIN `devices` AS `D` ON `D`.`device_id` = `applications`.`device_id` WHERE $perms_sql (`app_type` LIKE ? OR `hostname` LIKE ?) ORDER BY hostname LIMIT ?",
                array_merge($device_ids, ["%$search%", "%$search%", $limit])
            );

            if (count($results)) {
                $found = 1;
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

                    $device[] = [
                        'name'            => $name,
                        'hostname'        => format_hostname($result),
                        'app_id'          => $result['app_id'],
                        'device_id'       => $result['device_id'],
                        'colours'         => $highlight_colour,
                        'device_image'    => getIcon($result),
                        'device_hardware' => $result['hardware'],
                        'device_os' => \LibreNMS\Config::getOsSetting($result['os'], 'text'),
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                    ];
                }//end foreach
            }//end if

            $json = json_encode($device);
            exit($json);
        } elseif ($_REQUEST['type'] == 'munin') {
            // Device search
            $results = dbFetchRows(
                "SELECT * FROM `munin_plugins` INNER JOIN `devices` AS `D` ON `D`.`device_id` = `munin_plugins`.`device_id` WHERE $perms_sql (`mplug_type` LIKE ? OR `mplug_title` LIKE ? OR `hostname` LIKE ?) ORDER BY hostname LIMIT ?",
                array_merge($device_ids, ["%$search%", "%$search%", "%$search%", $limit])
            );

            if (count($results)) {
                $found = 1;
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

                    $device[] = [
                        'name'            => $name,
                        'hostname'        => format_hostname($result),
                        'device_id'       => $result['device_id'],
                        'colours'         => $highlight_colour,
                        'device_image'    => getIcon($result),
                        'device_hardware' => $result['hardware'],
                        'device_os' => \LibreNMS\Config::getOsSetting($result['os'], 'text'),
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                        'plugin'          => $result['mplug_type'],
                    ];
                }//end foreach
            }//end if

            $json = json_encode($device);
            exit($json);
        } elseif ($_REQUEST['type'] == 'iftype') {
            // Device search
            $results = dbFetchRows(
                "SELECT `ports`.ifType FROM `ports` WHERE $perms_sql `ifType` LIKE ? GROUP BY ifType ORDER BY ifType LIMIT ?",
                array_merge($device_ids, ["%$search%", $limit])
            );

            if (count($results)) {
                $found = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $device[] = [
                        'filter'            => $result['ifType'],
                    ];
                }//end foreach
            }//end if

            $json = json_encode($device);
            exit($json);
        } elseif ($_REQUEST['type'] == 'bill') {
            // Device search
            if (Auth::user()->hasGlobalRead()) {
                $results = dbFetchRows(
                    'SELECT `bills`.bill_id, `bills`.bill_name FROM `bills` WHERE `bill_name` LIKE ? OR `bill_notes` LIKE ? LIMIT ?',
                    ["%$search%", "%$search%", $limit]
                );
            } else {
                $results = dbFetchRows(
                    'SELECT `bills`.bill_id, `bills`.bill_name FROM `bills` INNER JOIN `bill_perms` ON `bills`.bill_id = `bill_perms`.bill_id WHERE `bill_perms`.user_id = ? AND (`bill_name` LIKE ? OR `bill_notes` LIKE ?) LIMIT ?',
                    [Auth::id(), "%$search%", "%$search%", $limit]
                );
            }
            $json = json_encode($results);
            exit($json);
        }//end if
    }//end if
}//end if
