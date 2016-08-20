<?php


function printEntPhysical($ent, $level, $class)
{
    global $device;

    $ents = dbFetchRows('SELECT * FROM `entPhysical` WHERE device_id = ? AND entPhysicalContainedIn = ? ORDER BY entPhysicalContainedIn,entPhysicalIndex', array($device['device_id'], $ent));
    foreach ($ents as $ent) {
        echo "
 <li class='$class'>";

        if ($ent['entPhysicalClass'] == 'chassis') {
            echo "<img src='images/16/server.png' style='vertical-align:middle'/> ";
        }

        if ($ent['entPhysicalClass'] == 'module') {
            echo "<img src='images/16/drive.png' style='vertical-align:middle'/> ";
        }

        if ($ent['entPhysicalClass'] == 'port') {
            echo "<img src='images/16/connect.png' style='vertical-align:middle'/> ";
        }

        if ($ent['entPhysicalClass'] == 'container') {
            echo "<img src='images/16/box.png' style='vertical-align:middle'/> ";
        }

        if ($ent['entPhysicalClass'] == 'sensor') {
            echo "<img src='images/16/contrast.png' style='vertical-align:middle'/> ";
            $sensor = dbFetchRow('SELECT * FROM `sensors` WHERE `device_id` = ? AND (`entPhysicalIndex` = ? OR `sensor_index` = ?)', array($device['device_id'], $ent['entPhysicalIndex'], $ent['entPhysicalIndex']));
            if (count($sensor)) {
                $link = " href='device/device=".$device['device_id'].'/tab=health/metric='.$sensor['sensor_class']."/' onmouseover=\"return overlib('<img src=\'graph.php?id=".$sensor['sensor_id'].'&amp;type=sensor_'.$sensor['sensor_class'].'&amp;from=-2d&amp;to=now&amp;width=400&amp;height=150&amp;a='.$ent['entPhysical_id']."\'><img src=\'graph.php?id=".$sensor['sensor_id'].'&amp;type=sensor_'.$sensor['sensor_class'].'&amp;from=-2w&amp;to=now&amp;width=400&amp;height=150&amp;a='.$ent['entPhysical_id']."\'>', LEFT,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"";
            }
        } else {
            unset($link);
        }

        if ($ent['entPhysicalClass'] == 'backplane') {
            echo "<img src='images/16/brick.png' style='vertical-align:middle'/> ";
        }

        if ($ent['entPhysicalParentRelPos'] > '-1') {
            echo '<strong>'.$ent['entPhysicalParentRelPos'].'.</strong> ';
        }

        if ($link) {
            echo "<a $link>";
        }

        if ($ent['ifIndex']) {
            $interface              = dbFetchRow('SELECT * FROM `ports` WHERE ifIndex = ? AND device_id = ?', array($ent['ifIndex'], $device['device_id']));
            $ent['entPhysicalName'] = generate_port_link($interface);
        }

        if ($ent['entPhysicalModelName'] && $ent['entPhysicalName']) {
            echo '<strong>'.$ent['entPhysicalModelName'].'</strong> ('.$ent['entPhysicalName'].')';
        } elseif ($ent['entPhysicalModelName']) {
            echo '<strong>'.$ent['entPhysicalModelName'].'</strong>';
        } elseif (is_numeric($ent['entPhysicalName']) && $ent['entPhysicalVendorType']) {
            echo '<strong>'.$ent['entPhysicalName'].' '.$ent['entPhysicalVendorType'].'</strong>';
        } elseif ($ent['entPhysicalName']) {
            echo '<strong>'.$ent['entPhysicalName'].'</strong>';
        } elseif ($ent['entPhysicalDescr']) {
            echo '<strong>'.$ent['entPhysicalDescr'].'</strong>';
        }

        if ($ent['entPhysicalClass'] == 'sensor') {
            echo ' ('.$ent['entSensorValue'].' '.$ent['entSensorType'].')';
        }

        echo "<br /><div class='interface-desc' style='margin-left: 20px;'>".$ent['entPhysicalDescr'];

        if ($link) {
            echo '</a>';
        }

        if ($ent['entPhysicalSerialNum']) {
            echo " <br /><span style='color: #000099;'>Serial No. ".$ent['entPhysicalSerialNum'].'</span> ';
        }

        echo '</div>';

        $count = dbFetchCell("SELECT COUNT(*) FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entPhysicalContainedIn = '".$ent['entPhysicalIndex']."'");
        if ($count) {
            echo '<ul>';
            printEntPhysical($ent['entPhysicalIndex'], ($level + 1), '');
            echo '</ul>';
        }

        echo '</li>';
    }//end foreach
}//end printEntPhysical()


echo "<div style='float: right;'>
       <a href='#' class='button' onClick=\"expandTree('enttree');return false;\"><img src='images/16/bullet_toggle_plus.png'>Expand All Nodes</a>
       <a href='#' class='button' onClick=\"collapseTree('enttree');return false;\"><img src='images/16/bullet_toggle_minus.png'>Collapse All Nodes</a>
     </div>";

echo "<div style='clear: both;'><UL CLASS='mktree' id='enttree'>";
$level                   = '0';
$ent['entPhysicalIndex'] = '0';
printEntPhysical($ent['entPhysicalIndex'], $level, 'liOpen');
echo '</ul></div>';

$pagetitle = 'Inventory';
