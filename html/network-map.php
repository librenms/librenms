<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage map
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_reporting', E_ALL);

$links = 1;

include_once '../includes/defaults.inc.php';
include_once '../config.php';
include_once '../includes/definitions.inc.php';
include_once '../includes/functions.php';
include_once '../includes/dbFacile.php';
include_once 'includes/functions.inc.php';
include_once 'includes/authenticate.inc.php';

if (strpos($_SERVER['REQUEST_URI'], 'anon')) {
    $anon = 1;
}

if (is_array($config['branding'])) {
    if ($config['branding'][$_SERVER['SERVER_NAME']]) {
        foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval) {
            eval("\$config['" . $confitem . "'] = \$confval;");
        }
    } else {
        foreach ($config['branding']['default'] as $confitem => $confval) {
            eval("\$config['" . $confitem . "'] = \$confval;");
        }
    }
}

if (is_numeric($_GET['device']) && isset($_GET['device'])) {
    $where = 'WHERE device_id = '.mres($_GET['device']);
} else {
    $where = '';
}

// FIXME this shit probably needs tidied up.

if (isset($_GET['format']) && preg_match("/^[a-z]*$/", $_GET['format'])) {
    $map = '
            digraph G { bgcolor=transparent; splines=true; overlap=scale; concentrate=0; epsilon=0.001; rankdir=LR;
            node [ fontname="helvetica", fontstyle=bold, style=filled, color=white, fillcolor=lightgrey, overlap=false];
            edge [ bgcolor=white, fontname="helvetica", fontstyle=bold, arrowhead=dot, arrowtail=dot];
            graph [bgcolor=transparent];
';

    if (!$_SESSION['authenticated']) {
        $map .= "\"Not authenticated\" [fontsize=20 fillcolor=\"lightblue\", URL=\"/\" shape=box3d]\n";
    } else {
        $loc_count = 1;

        foreach (dbFetch("SELECT * from devices ".$where) as $device) {
            if ($device) {
                $links = dbFetch("SELECT * from ports AS I, links AS L WHERE I.device_id = ? AND L.local_port_id = I.port_id ORDER BY L.remote_hostname", array($device['device_id']));
                if (count($links)) {
                    if ($anon) {
                        $device['hostname'] = md5($device['hostname']);
                    }
                    if (!isset($locations[$device['location']])) {
                        $locations[$device['location']] = $loc_count;
                        $loc_count++;
                    }
                    $loc_id = $locations[$device['location']];

                    $map .= "\"".$device['hostname']."\" [fontsize=20, fillcolor=\"lightblue\", group=".$loc_id." URL=\"{$config['base_url']}/device/device=".$device['device_id']."/tab=map/\" shape=box3d]\n";
                }

                foreach ($links as $link) {
                    $local_port_id = $link['local_port_id'];
                    $remote_port_id = $link['remote_port_id'];

                    $i = 0;
                    $done = 0;
                    if ($linkdone[$remote_port_id][$local_port_id]) {
                        $done = 1;
                    }

                    if (!$done) {
                        $linkdone[$local_port_id][$remote_port_id] = true;

                        $links++;

                        if ($link['ifSpeed'] >= "10000000000") {
                            $info = "color=red3 style=\"setlinewidth(6)\"";
                        } elseif ($link['ifSpeed'] >= "1000000000") {
                            $info = "color=lightblue style=\"setlinewidth(4)\"";
                        } elseif ($link['ifSpeed'] >= "100000000") {
                            $info = "color=lightgrey style=\"setlinewidth(2)\"";
                        } elseif ($link['ifSpeed'] >= "10000000") {
                            $info = "style=\"setlinewidth(1)\"";
                        } else {
                            $info = "style=\"setlinewidth(1)\"";
                        }

                        $src = $device['hostname'];
                        if ($anon) {
                            $src = md5($src);
                        }
                        if ($remote_port_id) {
                            $dst = dbFetchCell("SELECT `hostname` FROM `devices` AS D, `ports` AS I WHERE I.port_id = ? AND D.device_id = I.device_id", array($remote_port_id));
                            $dst_host = dbFetchCell("SELECT D.device_id FROM `devices` AS D, `ports` AS I WHERE I.port_id = ?  AND D.device_id = I.device_id", array($remote_port_id));
                        } else {
                            unset($dst_host);
                            $dst = $link['remote_hostname'];
                        }

                        if ($anon) {
                            $dst = md5($dst);
                            $src = md5($src);
                        }

                        $sif = ifNameDescr(dbFetchRow("SELECT * FROM ports WHERE `port_id` = ?", array($link['local_port_id'])), $device);
                        if ($remote_port_id) {
                            $dif = ifNameDescr(dbFetchRow("SELECT * FROM ports WHERE `port_id` = ?", array($link['remote_port_id'])));
                        } else {
                            $dif['label'] = $link['remote_port'];
                            $dif['port_id'] = $link['remote_hostname'] . '/' . $link['remote_port'];
                        }

                        if ($where == "") {
                            if (!$ifdone[$dst][$dif['port_id']] && !$ifdone[$src][$sif['port_id']]) {
                                $map .= "\"$src\" -> \"" . $dst . "\" [weight=500000, arrowsize=0, len=0];\n";
                            }
                            $ifdone[$src][$sif['port_id']] = 1;
                        } else {
                            $map .= "\"" . $sif['port_id'] . "\" [label=\"" . $sif['label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"{$config['base_url']}/device/device=".$device['device_id']."/tab=port/port=$local_port_id/\"]\n";
                            if (!$ifdone[$src][$sif['port_id']]) {
                                $map .= "\"$src\" -> \"" . $sif['port_id'] . "\" [weight=500000, arrowsize=0, len=0];\n";
                                $ifdone[$src][$sif['port_id']] = 1;
                            }

                            if ($dst_host) {
                                $map .= "\"$dst\" [URL=\"{$config['base_url']}/device/device=$dst_host/tab=map/\", fontsize=20, shape=box3d]\n";
                            } else {
                                $map .= "\"$dst\" [ fontsize=20 shape=box3d]\n";
                            }

                            if ($dst_host == $device['device_id'] || $where == '') {
                                $map .= "\"" . $dif['port_id'] . "\" [label=\"" . $dif['label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"{$config['base_url']}/device/device=$dst_host/tab=port/port=$remote_port_id/\"]\n";
                            } else {
                                $map .= "\"" . $dif['port_id'] . "\" [label=\"" . $dif['label'] . " \", fontsize=12, fillcolor=lightgray";
                                if ($dst_host) {
                                    $map .= ", URL=\"{$config['base_url']}/device/device=$dst_host/tab=port/port=$remote_port_id/\"";
                                }
                                $map .= "]\n";
                            }

                            if (!$ifdone[$dst][$dif['port_id']]) {
                                $map .= "\"" . $dif['port_id'] . "\" -> \"$dst\" [weight=500000, arrowsize=0, len=0];\n";
                                $ifdone[$dst][$dif['port_id']] = 1;
                            }
                            $map .= "\"" . $sif['port_id'] . "\" -> \"" . $dif['port_id'] . "\" [weight=1, arrowhead=normal, arrowtail=normal, len=2, $info] \n";
                        }
                    }
                }
                $done = 0;
            }
        }
    }

    $map .= "\n};";

    if ($_GET['debug'] == 1) {
        echo '<pre>$map</pre>';
        exit();
    }

    switch ($_GET['format']) {
        case 'svg':
            break;
        case 'png':
            $_GET['format'] = 'png:gd';
            break;
        case 'dot':
            echo($map);
            exit();
        default:
            $_GET['format'] = 'png:gd';
    }

    if ($links > 30) {
        // Unflatten if there are more than 10 links. beyond that it gets messy
        $maptool = $config['dot'];
    } else {
        $maptool = $config['dot'];
    }

    if ($where == '') {
        $maptool = $config['sfdp'] . ' -Gpack -Goverlap=prism -Gcharset=latin1 -Gsize=20,20';
        $maptool = $config['dot'];
    }

    $descriptorspec = array(0 => array("pipe", "r"),1 => array("pipe", "w") );

    $mapfile = $config['temp_dir'] . "/"  . strgen() . ".png";

    $process = proc_open($maptool.' -T'.$_GET['format'], $descriptorspec, $pipes);

    if (is_resource($process)) {
        fwrite($pipes[0], "$map");
        fclose($pipes[0]);
        while (! feof($pipes[1])) {
            $img .= fgets($pipes[1]);
        }
        fclose($pipes[1]);
        $return_value = proc_close($process);
    }

    if ($_GET['format'] == "png:gd") {
        header("Content-type: image/png");
    } elseif ($_GET['format'] == "svg") {
        header("Content-type: image/svg+xml");
        $img = str_replace("<a ", '<a target="_parent" ', $img);
    }
    echo $img;
} else {
    if ($_SESSION['authenticated']) {
        // FIXME level 10 only?
        echo '<center>
                  <object width=1200 height=1000 data="'. $config['base_url'] . '/map.php?format=svg" type="image/svg+xml"></object>
              </center>
        ';
    }
}
