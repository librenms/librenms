<?php

use LibreNMS\Config;

if (! Auth::user()->hasGlobalRead()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    $link_array = [
        'page'     => 'routing',
        'protocol' => 'vrf',
    ];

    print_optionbar_start();

    echo "<span style='font-weight: bold;'>VRFs</span> &#187; ";

    $menu_options = ['basic' => 'Basic',
    ];

    if (! $vars['view']) {
        $vars['view'] = 'basic';
    }

    $sep = '';
    foreach ($menu_options as $option => $text) {
        if ($vars['view'] == $option) {
            echo "<span class='pagemenu-selected'>";
        }

        echo generate_link($text, $link_array, ['view' => $option]);
        if ($vars['view'] == $option) {
            echo '</span>';
        }

        echo ' | ';
    }

    unset($sep);

    echo ' Graphs: ';

    $graph_types = [
        'bits'      => 'Bits',
        'upkts'     => 'Unicast Packets',
        'nupkts'    => 'Non-Unicast Packets',
        'errors'    => 'Errors',
        'etherlike' => 'Etherlike',
    ];

    foreach ($graph_types as $type => $descr) {
        echo "$type_sep";
        if ($vars['graph'] == $type) {
            echo "<span class='pagemenu-selected'>";
        }

        echo generate_link($descr, $link_array, ['view' => 'graphs', 'graph' => $type]);
        if ($vars['graph'] == $type) {
            echo '</span>';
        }

        $type_sep = ' | ';
    }

    print_optionbar_end();

    if ($vars['view'] == 'basic' || $vars['view'] == 'graphs') {
        // Pre-Cache in arrays
        // That's heavier on RAM, but much faster on CPU (1:40)
        // Specifying the fields reduces a lot the RAM used (1:4) .
        $vrf_fields = 'vrf_id, mplsVpnVrfRouteDistinguisher, mplsVpnVrfDescription, vrf_name';
        $dev_fields = 'D.device_id as device_id, hostname, os, hardware, version, features, location_id, status, `ignore`, disabled';
        $port_fields = 'port_id, ifvrf, device_id, ifDescr, ifAlias, ifName';

        foreach (dbFetchRows("SELECT $vrf_fields, $dev_fields FROM `vrfs` AS V, `devices` AS D WHERE D.device_id = V.device_id") as $vrf_device) {
            if (empty($vrf_devices[$vrf_device['vrf_name']][$vrf_device['mplsVpnVrfRouteDistinguisher']])) {
                $vrf_devices[$vrf_device['vrf_name']][$vrf_device['mplsVpnVrfRouteDistinguisher']][0] = $vrf_device;
            } else {
                array_push($vrf_devices[$vrf_device['vrf_name']][$vrf_device['mplsVpnVrfRouteDistinguisher']], $vrf_device);
            }
        }

        unset($ports);
        foreach (dbFetchRows("SELECT $port_fields FROM `ports` WHERE ifVrf<>0") as $port) {
            if (empty($ports[$port['ifvrf']][$port['device_id']])) {
                $ports[$port['ifvrf']][$port['device_id']][0] = $port;
            } else {
                array_push($ports[$port['ifvrf']][$port['device_id']], $port);
            }
        }

        echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
        $i = '1';
        foreach (dbFetchRows('SELECT `vrf_name`, `mplsVpnVrfRouteDistinguisher`, `mplsVpnVrfDescription` FROM `vrfs` GROUP BY `mplsVpnVrfRouteDistinguisher`, `mplsVpnVrfDescription`,`vrf_name`') as $vrf) {
            if (($i % 2)) {
                $bg_colour = Config::get('list_colour.even');
            } else {
                $bg_colour = Config::get('list_colour.odd');
            }

            echo "<tr valign=top bgcolor='$bg_colour'>";
            echo '<td width=240>';
            echo '<a class=list-large href=' . \LibreNMS\Util\Url::generate($vars, ['view' => 'detail', 'vrf' => $vrf['vrf_name']]) . '>';
            echo $vrf['vrf_name'] . '</a><br />';
            echo '<span class=box-desc>' . $vrf['mplsVpnVrfDescription'] . '</span></td>';
            echo '<td width=100 class=box-desc>' . $vrf['mplsVpnVrfRouteDistinguisher'] . '</td>';
            echo '<td><table border=0 cellspacing=0 cellpadding=5 width=100%>';
            $x = 1;
            foreach ($vrf_devices[$vrf['vrf_name']][$vrf['mplsVpnVrfRouteDistinguisher']] as $device) {
                if (($i % 2)) {
                    if (($x % 2)) {
                        $dev_colour = Config::get('list_colour.even_alt');
                    } else {
                        $dev_colour = Config::get('list_colour.even_alt2');
                    }
                } else {
                    if (($x % 2)) {
                        $dev_colour = Config::get('list_colour.odd_alt2');
                    } else {
                        $dev_colour = Config::get('list_colour.odd_alt');
                    }
                }

                echo "<tr bgcolor='$dev_colour'><td width=150><a href='";
                echo \LibreNMS\Util\Url::generate(['page' => 'device'], ['device' => $device['device_id'], 'tab' => 'routing', 'view' => 'basic', 'proto' => 'vrf']);
                echo "'>" . $device['hostname'] . '</a> ';

                if ($device['vrf_name'] != $vrf['vrf_name']) {
                    echo "<a href='#' onmouseover=\" return overlib('Expected Name : " . $vrf['vrf_name'] . '<br />Configured : ' . $device['vrf_name'] . "', CAPTION, '<span class=list-large>VRF Inconsistency</span>' ,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"> <i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i></a>";
                }

                echo '</td><td>';
                unset($seperator);

                foreach ($ports[$device['vrf_id']][$device['device_id']] as $port) {
                    $port = cleanPort($port);
                    $port = array_merge($device, $port);

                    switch ($vars['graph']) {
                        case 'bits':
                        case 'upkts':
                        case 'nupkts':
                        case 'errors':
                            $port['width'] = '130';
                            $port['height'] = '30';
                            $port['from'] = Config::get('time.day');
                            $port['to'] = Config::get('time.now');
                            $port['bg'] = '#' . $bg;
                            $port['graph_type'] = 'port_' . $vars['graph'];
                            echo "<div style='display: block; padding: 3px; margin: 3px; min-width: 135px; max-width:135px; min-height:75px; max-height:75px;
                            text-align: center; float: left; background-color: " . Config::get('list_colour.odd_alt2') . ";'>
                                <div style='font-weight: bold;'>" . makeshortif($port['ifDescr']) . '</div>';
                            print_port_thumbnail($port);
                            echo "<div style='font-size: 9px;'>" . substr(short_port_descr($port['ifAlias']), 0, 22) . '</div>
                                </div>';
                            break;

                        default:
                            echo $seperator . generate_port_link($port, makeshortif($port['ifDescr']));
                            $seperator = ', ';
                            break;
                    }//end switch
                }//end foreach

                echo '</td></tr>';
                $x++;
            } //end foreach

            echo '</table></td>';
            $i++;
        }//end foreach
        echo '</table></div>';
    } elseif ($vars['view'] == 'detail') {
        echo 'Not Implemented';
    }//end if
} //end if
