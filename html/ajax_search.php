<?php

require_once '../includes/defaults.inc.php';
set_debug($_REQUEST['debug']);
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once 'includes/functions.inc.php';
require_once '../includes/functions.php';
require_once 'includes/authenticate.inc.php';

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

$device = array();
$ports  = array();

if (isset($_REQUEST['search'])) {
    $search = mres($_REQUEST['search']);

    if (strlen($search) > 0) {
        $found = 0;

        if ($_REQUEST['type'] == 'group') {
            include_once '../includes/device-groups.inc.php';
            foreach (dbFetchRows("SELECT id,name FROM device_groups WHERE name LIKE '%".$search."%'") as $group) {
                if ($_REQUEST['map']) {
                    $results[] = array(
                        'name'     => 'g:'.$group['name'],
                        'group_id' => $group['id'],
                    );
                }
                else {
                    $results[] = array('name' => $group['name']);
                }
            }

            die(json_encode($results));
        }
        else if ($_REQUEST['type'] == 'alert-rules') {
            foreach (dbFetchRows("SELECT name FROM alert_rules WHERE name LIKE '%".$search."%'") as $rules) {
                $results[] = array('name' => $rules['name']);
            }

            die(json_encode($results));
        }
        else if ($_REQUEST['type'] == 'device') {
            // Device search
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT * FROM `devices` WHERE `hostname` LIKE '%".$search."%' OR `location` LIKE '%".$search."%' ORDER BY hostname LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT * FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` AND (`hostname` LIKE '%".$search."%' OR `location` LIKE '%".$search."%') ORDER BY hostname LIMIT 8", array($_SESSION['user_id']));
            }

            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['hostname'];
                    if ($result['disabled'] == 1) {
                        $highlight_colour = '#808080';
                    }
                    else if ($result['ignored'] == 1 && $result['disabled'] == 0) {
                        $highlight_colour = '#000000';
                    }
                    else if ($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#ff0000';
                    }
                    else if ($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#008000';
                    }

                    if (is_admin() === true || is_read() === true) {
                        $num_ports = dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE device_id = ?', array($result['device_id']));
                    }
                    else {
                        $num_ports = dbFetchCell('SELECT COUNT(*) FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` AND `I`.`device_id` = `D`.`device_id` AND device_id = ?', array($_SESSION['user_id'], $result['device_id']));
                    }

                    $device[] = array(
                        'name'            => $name,
                        'device_id'       => $result['device_id'],
                        'url'             => generate_device_url($result),
                        'colours'         => $highlight_colour,
                        'device_ports'    => $num_ports,
                        'device_image'    => getImageSrc($result),
                        'device_hardware' => $result['hardware'],
                        'device_os'       => $config['os'][$result['os']]['text'],
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        }
        else if ($_REQUEST['type'] == 'ports') {
            // Search ports
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT `ports`.*,`devices`.* FROM `ports` LEFT JOIN `devices` ON  `ports`.`device_id` =  `devices`.`device_id` WHERE `ifAlias` LIKE '%".$search."%' OR `ifDescr` LIKE '%".$search."%' OR `ifName` LIKE '%".$search."%' ORDER BY ifDescr LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT DISTINCT(`I`.`port_id`), `I`.*, `D`.`hostname` FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P`, `ports_perms` AS `PP` WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id`) OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` AND `I`.`device_id` = `D`.`device_id`)) AND `D`.`device_id` = `I`.`device_id` AND (`ifAlias` LIKE '%".$search."%' OR `ifDescr` LIKE '%".$search."%' OR `ifName` LIKE '%".$search."%') ORDER BY ifDescr LIMIT 8", array($_SESSION['user_id'], $_SESSION['user_id']));
            }

            if (count($results)) {
                $found = 1;

                foreach ($results as $result) {
                    $name        = $result['ifDescr'] == $result['ifAlias'] ? $result['ifName'] : $result['ifDescr'];
                    $description = $result['ifAlias'];

                    if ($result['deleted'] == 0 && ($result['ignore'] == 0 || $result['ignore'] == 0) && ($result['ifInErrors_delta'] > 0 || $result['ifOutErrors_delta'] > 0)) {
                        // Errored ports
                        $port_colour = '#ffa500';
                    }
                    else if ($result['deleted'] == 0 && ($result['ignore'] == 1 || $result['ignore'] == 1)) {
                        // Ignored ports
                        $port_colour = '#000000';
                    }
                    else if ($result['deleted'] == 0 && $result['ifAdminStatus'] == 'down' && $result['ignore'] == 0 && $result['ignore'] == 0) {
                        // Shutdown ports
                        $port_colour = '#808080';
                    }
                    else if ($result['deleted'] == 0 && $result['ifOperStatus'] == 'down' && $result['ifAdminStatus'] == 'up' && $result['ignore'] == 0 && $result['ignore'] == 0) {
                        // Down ports
                        $port_colour = '#ff0000';
                    }
                    else if ($result['deleted'] == 0 && $result['ifOperStatus'] == 'up' && $result['ignore'] == 0 && $result['ignore'] == 0) {
                        // Up ports
                        $port_colour = '#008000';
                    }//end if

                    $ports[] = array(
                        'count'       => count($results),
                        'url'         => generate_port_url($result),
                        'name'        => $name,
                        'description' => $description,
                        'colours'     => $highlight_colour,
                        'hostname'    => $result['hostname'],
                        'port_id'     => $result['port_id'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($ports);
            die($json);
        }
        else if ($_REQUEST['type'] == 'bgp') {
            // Search bgp peers
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT `bgpPeers`.*,`devices`.* FROM `bgpPeers` LEFT JOIN `devices` ON  `bgpPeers`.`device_id` =  `devices`.`device_id` WHERE `astext` LIKE '%".$search."%' OR `bgpPeerIdentifier` LIKE '%".$search."%' OR `bgpPeerRemoteAs` LIKE '%".$search."%' ORDER BY `astext` LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT `bgpPeers`.*,`D`.* FROM `bgpPeers`, `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` AND  `bgpPeers`.`device_id`=`D`.`device_id` AND  (`astext` LIKE '%".$search."%' OR `bgpPeerIdentifier` LIKE '%".$search."%' OR `bgpPeerRemoteAs` LIKE '%".$search."%') ORDER BY `astext` LIMIT 8", array($_SESSION['user_id']));
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
                    }
                    else if ($result['bgpPeerAdminStatus'] != 'start') {
                        // Session inactive
                        $port_colour = '#000000';
                    }
                    else if ($result['bgpPeerAdminStatus'] == 'start' && $result['bgpPeerState'] == 'established') {
                        // Session Up
                        $port_colour = '#008000';
                    }

                    if ($result['bgpPeerRemoteAs'] == $result['bgpLocalAs']) {
                        $bgp_image = 'images/16/brick_link.png';
                    }
                    else {
                        $bgp_image = 'images/16/world_link.png';
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
        }
        else if ($_REQUEST['type'] == 'applications') {
            // Device search
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT * FROM `applications` INNER JOIN `devices` ON devices.device_id = applications.device_id WHERE `app_type` LIKE '%".$search."%' OR `hostname` LIKE '%".$search."%' ORDER BY hostname LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT * FROM `applications` INNER JOIN `devices` AS `D` ON `D`.`device_id` = `applications`.`device_id` INNER JOIN `devices_perms` AS `P` ON `P`.`device_id` = `D`.`device_id` WHERE `P`.`user_id` = ? AND (`app_type` LIKE '%".$search."%' OR `hostname` LIKE '%".$search."%') ORDER BY hostname LIMIT 8", array($_SESSION['user_id']));
            }

            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['app_type'];
                    if ($result['disabled'] == 1) {
                        $highlight_colour = '#808080';
                    }
                    else if ($result['ignored'] == 1 && $result['disabled'] == 0) {
                        $highlight_colour = '#000000';
                    }
                    else if ($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#ff0000';
                    }
                    else if ($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#008000';
                    }

                    $device[] = array(
                        'name'            => $name,
                        'hostname'        => $result['hostname'],
                        'app_id'          => $result['app_id'],
                        'device_id'       => $result['device_id'],
                        'colours'         => $highlight_colour,
                        'device_image'    => getImageSrc($result),
                        'device_hardware' => $result['hardware'],
                        'device_os'       => $config['os'][$result['os']]['text'],
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        }
        else if ($_REQUEST['type'] == 'munin') {
            // Device search
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT * FROM `munin_plugins` INNER JOIN `devices` ON devices.device_id = munin_plugins.device_id WHERE `mplug_type` LIKE '%".$search."%' OR `mplug_title` LIKE '%".$search."%' OR `hostname` LIKE '%".$search."%' ORDER BY hostname LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT * FROM `munin_plugins` INNER JOIN `devices` AS `D` ON `D`.`device_id` = `munin_plugins`.`device_id` INNER JOIN `devices_perms` AS `P` ON `P`.`device_id` = `D`.`device_id` WHERE `P`.`user_id` = ? AND (`mplug_type` LIKE '%".$search."%' OR `mplug_title` LIKE '%".$search."%' OR `hostname` LIKE '%".$search."%') ORDER BY hostname LIMIT 8", array($_SESSION['user_id']));
            }

            if (count($results)) {
                $found   = 1;
                $devices = count($results);

                foreach ($results as $result) {
                    $name = $result['mplug_title'];
                    if ($result['disabled'] == 1) {
                        $highlight_colour = '#808080';
                    }
                    else if ($result['ignored'] == 1 && $result['disabled'] == 0) {
                        $highlight_colour = '#000000';
                    }
                    else if ($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#ff0000';
                    }
                    else if ($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0) {
                        $highlight_colour = '#008000';
                    }

                    $device[] = array(
                        'name'            => $name,
                        'hostname'        => $result['hostname'],
                        'device_id'       => $result['device_id'],
                        'colours'         => $highlight_colour,
                        'device_image'    => getImageSrc($result),
                        'device_hardware' => $result['hardware'],
                        'device_os'       => $config['os'][$result['os']]['text'],
                        'version'         => $result['version'],
                        'location'        => $result['location'],
                        'plugin'          => $result['mplug_type'],
                    );
                }//end foreach
            }//end if

            $json = json_encode($device);
            die($json);
        }
        else if ($_REQUEST['type'] == 'iftype') {
            // Device search
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT `ports`.ifType FROM `ports` WHERE `ifType` LIKE '%".$search."%' GROUP BY ifType ORDER BY ifType LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT `I`.ifType FROM `ports` AS `I`, `devices` AS `D`, `devices_perms` AS `P`, `ports_perms` AS `PP` WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id`) OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` AND `I`.`device_id` = `D`.`device_id`)) AND `D`.`device_id` = `I`.`device_id` AND (`ifType` LIKE '%".$search."%') GROUP BY ifType ORDER BY ifType LIMIT 8", array($_SESSION['user_id'], $_SESSION['user_id']));
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
        }
        else if ($_REQUEST['type'] == 'bill') {
            // Device search
            if (is_admin() === true || is_read() === true) {
                $results = dbFetchRows("SELECT `bills`.bill_id, `bills`.bill_name FROM `bills` WHERE `bill_name` LIKE '%".$search."%' OR `bill_notes` LIKE '%".$search."%' LIMIT 8");
            }
            else {
                $results = dbFetchRows("SELECT `bills`.bill_id, `bills`.bill_name FROM `bills` INNER JOIN `bill_perms` ON `bills`.bill_id = `bill_perms`.bill_id WHERE `bill_perms`.user_id = ? AND (`bill_name` LIKE '%".$search."%' OR `bill_notes` LIKE '%".$search."%') LIMIT 8", array($_SESSION['user_id']));
            }
            $json = json_encode($results);
            die($json);
        }//end if
    }//end if
}//end if
