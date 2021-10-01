<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Config;
use LibreNMS\Util\Debug;

$links = 1;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

$options = getopt('d::');

if (Debug::set(isset($options['d']), false)) {
    echo "DEBUG!\n";
}

if (strpos($_SERVER['REQUEST_URI'], 'anon')) {
    $anon = 1;
}

if (is_array(Config::get('branding'))) {
    Config::set('branding', array_replace_recursive(Config::get('branding'), Config::get('branding.' . $_SERVER['SERVER_NAME']) ?: Config::get('branding.default')));
}

$where = '';
$param = [];
if (isset($_GET['device']) && is_numeric($_GET['device'])) {
    $where = '&& device_id = ?';
    $param[] = $_GET['device'];
}
// FIXME this shit probably needs tidied up.

if (isset($_GET['format']) && preg_match('/^[a-z]*$/', $_GET['format'])) {
    $map = '
            digraph G { bgcolor=transparent; splines=true; overlap=scale; concentrate=0; epsilon=0.001; rankdir=LR;
            node [ fontname="helvetica", fontstyle=bold, style=filled, color=white, fillcolor=lightgrey, overlap=false];
            edge [ bgcolor=white, fontname="helvetica", fontstyle=bold, arrowhead=dot, arrowtail=dot];
            graph [bgcolor=transparent];
';

    if (! Auth::check()) {
        $map .= "\"Not authenticated\" [fontsize=20 fillcolor=\"lightblue\", URL=\"/\" shape=box3d]\n";
    } else {
        $locations = getlocations();
        $loc_count = 1;
        foreach (dbFetch('SELECT *, locations.location FROM devices LEFT JOIN locations ON devices.location_id = locations.id WHERE 1 ' . $where, $param) as $device) {
            if ($device) {
                $links = dbFetch('SELECT * from ports AS I, links AS L WHERE I.device_id = ? AND L.local_port_id = I.port_id ORDER BY L.remote_hostname', [$device['device_id']]);
                if (count($links)) {
                    if ($anon) {
                        $device['hostname'] = md5($device['hostname']);
                    }
                    if (! isset($locations[$device['location']])) {
                        $locations[$device['location']] = $loc_count;
                        $loc_count++;
                    }
                    $loc_id = $locations[$device['location']];

                    $map .= '"' . $device['hostname'] . '" [fontsize=20, fillcolor="lightblue", group=' . $loc_id . ' URL="' . Config::get('base_url') . '/device/device=' . $device['device_id'] . "/tab=neighbours/selection=map/\" shape=box3d]\n";
                }

                foreach ($links as $link) {
                    $local_port_id = $link['local_port_id'];
                    $remote_port_id = $link['remote_port_id'];

                    $i = 0;
                    $done = 0;
                    if ($linkdone[$remote_port_id][$local_port_id]) {
                        $done = 1;
                    }

                    if (! $done) {
                        $linkdone[$local_port_id][$remote_port_id] = true;

                        $links++;

                        if ($link['ifSpeed'] >= '10000000000') {
                            $info = 'color=red3 style="setlinewidth(6)"';
                        } elseif ($link['ifSpeed'] >= '1000000000') {
                            $info = 'color=lightblue style="setlinewidth(4)"';
                        } elseif ($link['ifSpeed'] >= '100000000') {
                            $info = 'color=lightgrey style="setlinewidth(2)"';
                        } elseif ($link['ifSpeed'] >= '10000000') {
                            $info = 'style="setlinewidth(1)"';
                        } else {
                            $info = 'style="setlinewidth(1)"';
                        }

                        $src = $device['hostname'];
                        if ($anon) {
                            $src = md5($src);
                        }
                        if ($remote_port_id) {
                            $dst = dbFetchCell('SELECT `hostname` FROM `devices` AS D, `ports` AS I WHERE I.port_id = ? AND D.device_id = I.device_id', [$remote_port_id]);
                            $dst_host = dbFetchCell('SELECT D.device_id FROM `devices` AS D, `ports` AS I WHERE I.port_id = ?  AND D.device_id = I.device_id', [$remote_port_id]);
                        } else {
                            unset($dst_host);
                            $dst = $link['remote_hostname'];
                        }

                        if ($anon) {
                            $dst = md5($dst);
                            $src = md5($src);
                        }

                        $sif = cleanPort(dbFetchRow('SELECT * FROM ports WHERE `port_id` = ?', [$link['local_port_id']]), $device);
                        if ($remote_port_id) {
                            $dif = cleanPort(dbFetchRow('SELECT * FROM ports WHERE `port_id` = ?', [$link['remote_port_id']]));
                        } else {
                            $dif['label'] = $link['remote_port'];
                            $dif['port_id'] = $link['remote_hostname'] . '/' . $link['remote_port'];
                        }

                        if ($where == '') {
                            if (! $ifdone[$dst][$dif['port_id']] && ! $ifdone[$src][$sif['port_id']]) {
                                $map .= "\"$src\" -> \"" . $dst . "\" [weight=500000, arrowsize=0, len=0];\n";
                            }
                            $ifdone[$src][$sif['port_id']] = 1;
                        } else {
                            $map .= '"' . $sif['port_id'] . '" [label="' . $sif['label'] . '", fontsize=12, fillcolor=lightblue, URL="' . Config::get('base_url') . '/device/device=' . $device['device_id'] . "/tab=port/port=$local_port_id/\"]\n";
                            if (! $ifdone[$src][$sif['port_id']]) {
                                $map .= "\"$src\" -> \"" . $sif['port_id'] . "\" [weight=500000, arrowsize=0, len=0];\n";
                                $ifdone[$src][$sif['port_id']] = 1;
                            }

                            if ($dst_host) {
                                $map .= "\"$dst\" [URL=\"" . Config::get('base_url') . "/device/device=$dst_host/tab=neighbours/selection=map/\", fontsize=20, shape=box3d]\n";
                            } else {
                                $map .= "\"$dst\" [ fontsize=20 shape=box3d]\n";
                            }

                            if ($dst_host == $device['device_id'] || $where == '') {
                                $map .= '"' . $dif['port_id'] . '" [label="' . $dif['label'] . '", fontsize=12, fillcolor=lightblue, URL="' . Config::get('base_url') . "/device/device=$dst_host/tab=port/port=$remote_port_id/\"]\n";
                            } else {
                                $map .= '"' . $dif['port_id'] . '" [label="' . $dif['label'] . ' ", fontsize=12, fillcolor=lightgray';
                                if ($dst_host) {
                                    $map .= ', URL="' . Config::get('base_url') . "/device/device=$dst_host/tab=port/port=$remote_port_id/\"";
                                }
                                $map .= "]\n";
                            }

                            if (! $ifdone[$dst][$dif['port_id']]) {
                                $map .= '"' . $dif['port_id'] . "\" -> \"$dst\" [weight=500000, arrowsize=0, len=0];\n";
                                $ifdone[$dst][$dif['port_id']] = 1;
                            }
                            $map .= '"' . $sif['port_id'] . '" -> "' . $dif['port_id'] . "\" [weight=1, arrowhead=normal, arrowtail=normal, len=2, $info] \n";
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
            echo $map;
            exit();
        default:
            $_GET['format'] = 'png:gd';
    }

    if ($links > 30) {
        // Unflatten if there are more than 10 links. beyond that it gets messy
        $maptool = Config::get('dot');
    } else {
        $maptool = Config::get('dot');
    }

    if ($where == '') {
        $maptool = Config::get('sfdp') . ' -Gpack -Goverlap=prism -Gcharset=latin1 -Gsize=20,20';
        $maptool = Config::get('dot');
    }

    $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w']];

    $mapfile = Config::get('temp_dir') . '/' . strgen() . '.png';

    $process = proc_open($maptool . ' -T' . $_GET['format'], $descriptorspec, $pipes);

    if (is_resource($process)) {
        fwrite($pipes[0], "$map");
        fclose($pipes[0]);
        while (! feof($pipes[1])) {
            $img .= fgets($pipes[1]);
        }
        fclose($pipes[1]);
        $return_value = proc_close($process);
    }

    if ($_GET['format'] == 'png:gd') {
        header('Content-type: image/png');
    } elseif ($_GET['format'] == 'svg') {
        header('Content-type: image/svg+xml');
        $img = str_replace('<a ', '<a target="_parent" ', $img);
    }
    echo $img;
} else {
    if (Auth::check()) {
        // FIXME level 10 only?
        echo '<center>
                  <object width=1200 height=1000 data="' . Config::get('base_url') . '/network-map.php?format=svg" type="image/svg+xml"></object>
              </center>
        ';
    }
}
