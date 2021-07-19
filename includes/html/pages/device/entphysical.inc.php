<?php

use Illuminate\Database\Eloquent\Builder;

function printEntPhysical($device, $ent, $level, $class)
{
    $ents = dbFetchRows('SELECT * FROM `entPhysical` WHERE device_id = ? AND entPhysicalContainedIn = ? ORDER BY entPhysicalContainedIn,entPhysicalIndex', [$device['device_id'], $ent]);

    foreach ($ents as $ent) {
        //Let's find if we have any sensors attached to the current entity;
        //We hit this code for every type of entity because not all vendors have 1 'sensor' entity per sensor
        $sensors = DeviceCache::getPrimary()->sensors()->where(function (Builder $query) use ($ent) {
            return $query->where('entPhysicalIndex', $ent['entPhysicalIndex'])
                ->orWhere('sensor_index', $ent['entPhysicalIndex']);
        })->get();
        echo "
 <li class='$class'>";

        if ($ent['entPhysicalClass'] == 'chassis') {
            echo '<i class="fa fa-server fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'module') {
            echo '<i class="fa fa-database fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'port') {
            echo '<i class="fa fa-link fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'container') {
            echo '<i class="fa fa-square fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'sensor') {
            echo '<i class="fa fa-heartbeat fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'backplane') {
            echo '<i class="fa fa-bars fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'stack') {
            echo '<i class="fa fa-list-ol fa-lg icon-theme" aria-hidden="true"></i> ';
        } elseif ($ent['entPhysicalClass'] == 'powerSupply') {
            echo '<i class="fa fa-bolt fa-lg icon-theme" aria-hidden="true"></i> ';
        }

        if ($ent['entPhysicalParentRelPos'] > '-1') {
            echo '<strong>' . $ent['entPhysicalParentRelPos'] . '.</strong> ';
        }

        $display_entPhysicalName = $ent['entPhysicalName'];
        if ($ent['ifIndex']) {
            $interface = get_port_by_ifIndex($device['device_id'], $ent['ifIndex']);
            $interface = cleanPort($interface);
            $display_entPhysicalName = generate_port_link($interface);
        }

        if ($ent['entPhysicalModelName'] && $display_entPhysicalName) {
            echo '<strong>' . $ent['entPhysicalModelName'] . '</strong> (' . $display_entPhysicalName . ')';
        } elseif ($ent['entPhysicalModelName']) {
            echo '<strong>' . $ent['entPhysicalModelName'] . '</strong>';
        } elseif (is_numeric($ent['entPhysicalName']) && $ent['entPhysicalVendorType']) {
            echo '<strong>' . $ent['entPhysicalName'] . ' ' . $ent['entPhysicalVendorType'] . '</strong>';
        } elseif ($display_entPhysicalName) {
            echo '<strong>' . $display_entPhysicalName . '</strong>';
        } elseif ($ent['entPhysicalDescr']) {
            echo '<strong>' . $ent['entPhysicalDescr'] . '</strong>';
        }

        // Display matching sensor value (without descr, as we have only one)
        if ($sensors->count() == 1) {
            foreach ($sensors as $sensor) {
                echo "<a href='graphs/id=" . $sensor->sensor_id . '/type=sensor_' . $sensor->sensor_class . "/' onmouseover=\"return overlib('<img src=\'graph.php?id=" . $sensor->sensor_id . '&amp;type=sensor_' . $sensor->sensor_class . '&amp;from=-2d&amp;to=now&amp;width=400&amp;height=150&amp;a=' . $ent['entPhysical_id'] . "\'><img src=\'graph.php?id=" . $sensor->sensor_id . '&amp;type=sensor_' . $sensor->sensor_class . '&amp;from=-2w&amp;to=now&amp;width=400&amp;height=150&amp;a=' . $ent['entPhysical_id'] . "\'>', LEFT,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\">";
                //echo "<span style='color: #000099;'>" . $sensor->sensor_class . ': ' . $sensor->sensor_descr . '</span>';
                echo ' ';
                echo $sensor->sensor_class == 'state' ? get_state_label($sensor->toArray()) : get_sensor_label_color($sensor->toArray());
                echo '</a>';
            }
        }

        // display entity state
        $entState = dbFetchRow(
            'SELECT * FROM `entityState` WHERE `device_id`=? && `entPhysical_id`=?',
            [$device['device_id'], $ent['entPhysical_id']]
        );

        if (! empty($entState)) {
            $display_states = [
                //                'entStateAdmin',
                'entStateOper',
                'entStateUsage',
                'entStateStandby',
            ];
            foreach ($display_states as $state_name) {
                $value = $entState[$state_name];
                $display = parse_entity_state($state_name, $value);
                echo " <span class='label label-{$display['color']}' data-toggle='tooltip' title='$state_name ($value)'>";
                echo $display['text'];
                echo '</span> ';
            }

            // ignore none and unavailable alarms
            if ($entState['entStateAlarm'] != '00' && $entState['entStateAlarm'] != '80') {
                $alarms = parse_entity_state_alarm($entState['entStateAlarm']);
                echo '<br />';
                echo "<span style='margin-left: 20px;'>Alarms: ";
                foreach ($alarms as $alarm) {
                    echo " <span class='label label-{$alarm['color']}'>{$alarm['text']}</span>";
                }
                echo '</span>';
            }
        }

        echo "<br /><div class='interface-desc' style='margin-left: 20px;'>" . $ent['entPhysicalDescr'];

        if ($ent['entPhysicalAlias'] && $ent['entPhysicalAssetID']) {
            echo ' <br />Alias: ' . $ent['entPhysicalAlias'] . ' - AssetID: ' . $ent['entPhysicalAssetID'];
        } elseif ($ent['entPhysicalAlias']) {
            echo ' <br />Alias: ' . $ent['entPhysicalAlias'];
        } elseif ($ent['entPhysicalAssetID']) {
            echo ' <br />AssetID: ' . $ent['entPhysicalAssetID'];
        }

        if ($ent['entPhysicalSerialNum']) {
            echo " <br /><span style='color: #000099;'>Serial No. " . $ent['entPhysicalSerialNum'] . '</span> ';
        }

        // Display sensors values with their descr, as we have more than one attached to this entPhysical
        if ($sensors->count() > 1) {
            echo "<br>Sensors:<div class='interface-desc' style='margin-left: 20px;'>";
            foreach ($sensors as $sensor) {
                $disp_name = str_replace([$ent['entPhysicalDescr'], $ent['entPhysicalName']], ['', ''], $sensor->sensor_descr);
                echo "<a href='graphs/id=" . $sensor->sensor_id . '/type=sensor_' . $sensor->sensor_class . "/' onmouseover=\"return overlib('<img src=\'graph.php?id=" . $sensor->sensor_id . '&amp;type=sensor_' . $sensor->sensor_class . '&amp;from=-2d&amp;to=now&amp;width=400&amp;height=150&amp;a=' . $ent['entPhysical_id'] . "\'><img src=\'graph.php?id=" . $sensor->sensor_id . '&amp;type=sensor_' . $sensor->sensor_class . '&amp;from=-2w&amp;to=now&amp;width=400&amp;height=150&amp;a=' . $ent['entPhysical_id'] . "\'>', LEFT,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\">";
                echo "<span style='color: #000099;'>" . $disp_name . ' ' . $sensor->sensor_class . '</span>';
                echo ' ';
                echo $sensor->sensor_class == 'state' ? get_state_label($sensor->toArray()) : get_sensor_label_color($sensor->toArray());
                echo '</a><br>';
            }
            echo '</div>';
        }
        echo '</div>';

        $count = dbFetchCell("SELECT COUNT(*) FROM `entPhysical` WHERE device_id = '" . $device['device_id'] . "' AND entPhysicalContainedIn = '" . $ent['entPhysicalIndex'] . "'");
        if ($count) {
            echo '<ul>';
            printEntPhysical($device, $ent['entPhysicalIndex'], ($level + 1), 'liClosed');
            echo '</ul>';
        }

        echo '</li>';
    }//end foreach
}//end printEntPhysical()

echo "<div style='float: right;'>
       <a href='#' class='button' onClick=\"expandTree('enttree');return false;\"><i class='fa fa-plus fa-lg icon-theme'  aria-hidden='true'></i>Expand All Nodes</a>
       <a href='#' class='button' onClick=\"collapseTree('enttree');return false;\"><i class='fa fa-minus fa-lg icon-theme'  aria-hidden='true'></i>Collapse All Nodes</a>
     </div>";

echo "<div style='clear: both;'><UL CLASS='mktree' id='enttree'>";
$level = '0';
$ent['entPhysicalIndex'] = '0';
printEntPhysical($device, $ent['entPhysicalIndex'], $level, 'liOpen');
echo '</ul></div>';

$pagetitle = 'Inventory';
