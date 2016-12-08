<?php

if ($_SESSION['userlevel'] >= '5') {
    if (!isset($_GET['optb'])) {
        $_GET['optb'] = 'all';
    }

    if (!isset($_GET['optc'])) {
        $_GET['optc'] = 'basic';
    }

    print_optionbar_start();

    echo '<span style="font-weight: bold;">VRF</span> &#187; ';

    if ($_GET['opta'] == 'vrf' && $_GET['optb'] == 'all') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/all/'.$_GET['optc'].'/">All</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optb'] == 'all') {
        echo '</span>';
    }

    echo ' | ';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'basic') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/'.$_GET['optb'].'/basic/">Basic</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'basic') {
        echo '</span>';
    }

    echo ' | ';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'details') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/'.$_GET['optb'].'/details/">Details</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'details') {
        echo '</span>';
    }

    echo ' | Graphs: ( ';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'bits') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/'.$_GET['optb'].'/bits/">Bits</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'bits') {
        echo '</span>';
    }

    echo ' | ';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'upkts') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/'.$_GET['optb'].'/upkts/">Packets</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'upkts') {
        echo '</span>';
    }

    echo ' | ';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'nupkts') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/'.$_GET['optb'].'/nupkts/">NU Packets</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'nupkts') {
        echo '</span>';
    }

    echo ' | ';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'errors') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="routing/vrf/'.$_GET['optb'].'/errors/">Errors</a>';
    if ($_GET['opta'] == 'vrf' && $_GET['optc'] == 'errors') {
        echo '</span>';
    }

    echo ' )';

    print_optionbar_end();

    if ($_GET['optb'] == 'all') {
        // Pre-Cache in arrays
        // That's heavier on RAM, but much faster on CPU (1:40)
        // Specifying the fields reduces a lot the RAM used (1:4) .
        $vrf_fields  = 'vrf_id, mplsVpnVrfRouteDistinguisher, mplsVpnVrfDescription, vrf_name';
        $dev_fields  = 'D.device_id as device_id, hostname, os, hardware, version, features, location, status, `ignore`, disabled';
        $port_fields = 'port_id, ifvrf, device_id, ifDescr, ifAlias, ifName';

        foreach (dbFetchRows("SELECT $vrf_fields, $dev_fields FROM `vrfs` AS V, `devices` AS D WHERE D.device_id = V.device_id") as $vrf_device) {
            if (empty($vrf_devices[$vrf_device['mplsVpnVrfRouteDistinguisher']])) {
                $vrf_devices[$vrf_device['mplsVpnVrfRouteDistinguisher']][0] = $vrf_device;
            } else {
                array_push($vrf_devices[$vrf_device['mplsVpnVrfRouteDistinguisher']], $vrf_device);
            }
        }

        foreach (dbFetchRows("SELECT $port_fields FROM `ports` WHERE ifVrf<>0") as $port) {
            if (empty($ports[$port['ifvrf']][$port['device_id']])) {
                $ports[$port['ifvrf']][$port['device_id']][0] = $port;
            } else {
                array_push($ports[$port['ifvrf']][$port['device_id']], $port);
            }
        }

        echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
        $i = '1';
        foreach (dbFetchRows('SELECT * FROM `vrfs` GROUP BY `mplsVpnVrfRouteDistinguisher`') as $vrf) {
            if (($i % 2)) {
                $bg_colour = $list_colour_a;
            } else {
                $bg_colour = $list_colour_b;
            }

            echo "<tr valign=top bgcolor='$bg_colour'>";
            echo "<td width=240><a class=list-large href='routing/vrf/".$vrf['mplsVpnVrfRouteDistinguisher'].'/'.$_GET['optc']."/'>".$vrf['vrf_name'].'</a><br /><span class=box-desc>'.$vrf['mplsVpnVrfDescription'].'</span></td>';
            echo '<td width=100 class=box-desc>'.$vrf['mplsVpnVrfRouteDistinguisher'].'</td>';
            // echo("<td width=200 class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</td>");
            echo '<td><table border=0 cellspacing=0 cellpadding=5 width=100%>';
            $x = 1;
            foreach ($vrf_devices[$vrf['mplsVpnVrfRouteDistinguisher']] as $device) {
                if (($i % 2)) {
                    if (($x % 2)) {
                        $dev_colour = $list_colour_a_a;
                    } else {
                        $dev_colour = $list_colour_a_b;
                    }
                } else {
                    if (($x % 2)) {
                        $dev_colour = $list_colour_b_b;
                    } else {
                        $dev_colour = $list_colour_b_a;
                    }
                }

                echo "<tr bgcolor='$dev_colour'><td width=150>".generate_device_link($device, shorthost($device['hostname']));

                if ($device['vrf_name'] != $vrf['vrf_name']) {
                    echo "<a href='#' onmouseover=\" return overlib('Expected Name : ".$vrf['vrf_name'].'<br />Configured : '.$device['vrf_name']."', CAPTION, '<span class=list-large>VRF Inconsistency</span>' ,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"> <img align=absmiddle src=images/16/exclamation.png></a>";
                }

                echo '</td><td>';
                unset($seperator);

                foreach ($ports[$device['vrf_id']][$device['device_id']] as $port) {
                    $port = array_merge($device, $port);

                    switch ($_GET['optc']) {
                        case 'bits':
                        case 'upkts':
                        case 'nupkts':
                        case 'errors':
                            $port['width']      = '130';
                            $port['height']     = '30';
                            $port['from']       = $config['time']['day'];
                            $port['to']         = $config['time']['now'];
                            $port['bg']         = '#'.$bg;
                            $port['graph_type'] = 'port_'.$_GET['optc'];
                            echo "<div style='display: block; padding: 3px; margin: 3px; min-width: 135px; max-width:135px; min-height:75px; max-height:75px;
                            text-align: center; float: left; background-color: ".$list_colour_b_b.";'>
                                <div style='font-weight: bold;'>".makeshortif($port['ifDescr']).'</div>';
                            print_port_thumbnail($port);
                            echo "<div style='font-size: 9px;'>".substr(short_port_descr($port['ifAlias']), 0, 22).'</div>
                                </div>';
                            break;

                        default:
                            echo $seperator.generate_port_link($port, makeshortif($port['ifDescr']));
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
    } else {
        echo "<div style='background: $list_colour_a; padding: 10px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
        $vrf = dbFetchRow('SELECT * FROM `vrfs` WHERE mplsVpnVrfRouteDistinguisher = ?', array($_GET['optb']));
        echo "<tr valign=top bgcolor='$bg_colour'>";
        echo "<td width=200 class=list-large><a href='routing/vrf/".$vrf['mplsVpnVrfRouteDistinguisher'].'/'.$_GET['optc']."/'>".$vrf['vrf_name'].'</a></td>';
        echo '<td width=100 class=box-desc>'.$vrf['mplsVpnVrfRouteDistinguisher'].'</td>';
        echo '<td width=200 class=box-desc>'.$vrf['mplsVpnVrfDescription'].'</td>';
        echo '</table></div>';

        $x = 0;

        $devices = dbFetchRows('SELECT * FROM `vrfs` AS V, `devices` AS D WHERE `mplsVpnVrfRouteDistinguisher` = ? AND D.device_id = V.device_id', array($vrf['mplsVpnVrfRouteDistinguisher']));
        foreach ($devices as $device) {
            $hostname = $device['hostname'];
            if (($x % 2)) {
                $device_colour = $list_colour_a;
            } else {
                $device_colour = $list_colour_b;
            }

            echo '<table cellpadding=10 cellspacing=0 class=devicetable width=100%>';

            include 'includes/device-header.inc.php';

            echo '</table>';
            unset($seperator);
            echo '<div style="margin: 0 0 0 60px;"><table cellspacing=0 cellpadding=7>';
            $i = 1;
            foreach (dbFetchRows('SELECT * FROM `ports` WHERE `ifVrf` = ? AND `device_id` = ?', array($device['vrf_id'], $device['device_id'])) as $interface) {
                if (($x % 2)) {
                    if (($i % 2) === 0) {
                        $int_colour = $list_colour_a_b;
                    } else {
                        $int_colour = $list_colour_a_a;
                    }
                } else {
                    if (($i % 2) === 0) {
                        $int_colour = $list_colour_b_a;
                    } else {
                        $int_colour = $list_colour_b_b;
                    }
                }

                include 'includes/print-interface.inc.php';

                $i++;
            }//end foreach

            $x++;
            echo '</table></div>';
            echo "<div style='height: 10px;'></div>";
        }//end foreach
    }//end if
} else {
    include 'includes/error-no-perm.inc.php';
} //end if
