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
        } elseif ($ent['entPhysicalClass']) {
            echo '<strong>' . $ent['entPhysicalClass'] . '</strong>';
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
            printEntPhysical($device, $ent['entPhysicalIndex'], $level + 1, 'liClosed');
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

echo "<pre>";
echo "Hello!";

// Set default column height to something sensible
$columnHeight = 2;
// Find hardware, model name or systemname for hints
//echo print_r($device, true);
$hardware  = $device['hardware'] ?? null;

// Deduce hints file from icon name
$brand = substr(basename($device['icon'] ?? null), 0, -4);
$filePath = "resources/views/device/hints/{$brand}.hints";
// echo getcwd();
if(file_exists($filePath)) {
	$line = fastFindLine($filePath, $hardware);
	if(!empty($line)) {
		echo "<br/>Found hardware '{$hardware}' in '{$brand}' hints ";
		// echo print_r(substr($line, strlen("{$hardware}:")), true);

		// if json does not decode, the return is false and we catch later
		$transformedPorts = generateTransformPorts(json_decode(substr(trim($line), strlen($hardware)+1), true));
		if (!isset($transformedPorts)) {
			echo "<br/>Failed to decode json string, is it valid?";
		}
	}
}

if(!isset($transformedPorts)) {
	if (empty($transformedPorts)) {
		echo "<br/>No hints found for '{$brand}' model '{$hardware}', auto generating";
		$transformedPorts = transformPorts($data['ports'], 2);
	}
}

if(isset($data['ports'])) {
	$indexports = transformPortsIndexedByIfName($data['ports']);
	$switchstart = findLowestSwitchIndex($indexports);
	$switchend = findHighestSwitchIndex($indexports);
	echo "<br/>Used hint </br>'". trim(substr($line, strlen($hardware)+1)) ."'</br>produced </br>". json_encode($transformedPorts);
	echo "<br/>Found lowest switch: '{$switchstart}' and highest '{$switchend}'";
}

echo "</pre>";

// We need some CSS

// Add CSS for physical port layout
echo <<<CSS
<style>
    .switch-container {
        border: 2px solid black;
        margin: 10px;
        padding: 10px;
        display: inline-block;
    }

    .module-container {
        border: 1px solid gray;
        margin: 5px;
        padding: 10px;
        display: inline-block;
    }

    .block-container {
        border: 1px solid gray;
        margin: 5px;
        padding: 10px;
        display: inline-block;
    }

    .port-table {
        border-collapse: collapse;
        width: 100%;
    }

    .port-cell {
        border: 4px solid;
        text-align: center;
        padding: 10px;
		width: 50px;
    }

    .border-up {
        border-color: MediumSeaGreen;
    }

    .border-down {
        border-color: darkblue;
    }

    .border-disabled {
        border-color: darkgrey;
    }

    .bg-up {
        background-color: lightgreen;
    }

    .bg-down {
        background-color: darkgrey;
    }

    .port-name {
        font-weight: bold;
    }

    .vlan-info {
        font-size: 12px;
    }

    .empty-cell {
        border: 2px solid transparent;
        background-color: transparent;
    }
	
    /* Tooltip container */
    .port-cell .tooltip {
        display: none;
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        text-align: left;
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 10;
        white-space: nowrap;
    }

    .port-cell:hover .tooltip {
        display: block;
    }
</style>
CSS;

// Example usage with your updated array:
echo generateVisualTableWithAttributes($switchstart, $switchend, $transformedPorts, $indexports);


// Functions below

// Generate switch layout from hints file

function generateTransformPorts($json, $side = 'front')
{
	if(!is_array($json))
		return false;

	$result = array();
	// Example asr920 string here, yes, it's long.
//  {"front":[{"height":"1","inverse":"0","block":"0","count":"1","type":"ac","start":"ac1"},{"height":"1","inverse":"0","block":"1","count":"1","type":"ac","start":"ac2"},{"height":"1","inverse":"0","block":"2","count":"1","type":"cu","start":"Gi0"},{"height":"2","inverse":"1","block":"3","count":"4","type":"sfp","start":"Gi0\/0\/0"},{"height":"2","inverse":"1","block":"4","count":"8","type":"sfp","start":"Gi0\/0\/4"},{"height":"2","inverse":"1","block":"4","count":"8","type":"cu","start":"Gi0\/0\/4"},{"height":"2","inverse":"1","block":"5","count":"2","type":"sfp","start":"Te0\/0\/12"}],"rear":[[]]}

	// we assume it is just module 0 for now, and each module will fire this
	// During output we overwrite switchno and moduleno using the actual port info
	$module = 0;
	if(isset($json['front'])) {
		//echo print_r($json['front'], true);
		foreach($json['front'] as $item) {
			$i = 0;
			$column = 1;
			// echo print_r($item, true);
			
			while($i < floatval($item['count'])) {
				$column = floor($i / $item['height']);
				// $portmap[$module][$block][$column][][$ifName] = $phy;
				// Explode and implode to increase if number
				$parts = array_reverse(explode("/",$item['start']));
				$ifName = $item['start'];
				// Check for multi module switch/module/port
				if(count($parts) > 1) {
					if(is_numeric($parts[0])) {
						$parts[0] = $parts[0] + $i;
					}
					$ifName = implode("/", array_reverse($parts));
				} else {
					// Just a plain string, test for number?
					preg_match("/([a-z-_ ]+)([0-9]+)/i", $parts[0], $matches);
					if(isset($matches[2]))
						$ifName = "{$matches[1]}". floatval($matches[2] + $i);
				}
				$result[$module][$item['block']][$column][][$ifName] = $item['type'];
				$i++;
			}
			
		}
	}
	
	if(isset($json['rear'])) {
		if(empty($json['rear']))
			return array();
		
	}
	return $result;
}


// Find line in hints file with /prefix/: and return line without prefix
// This should return a json object
function fastFindLine($filePath, $prefix)
{
    $file = new SplFileObject($filePath);
	$prefix .= ":";
    while (!$file->eof()) {
        $line = $file->fgets();
        if (strncmp($line, $prefix, strlen($prefix)) === 0) {
            return $line;
        }
    }
    return null;
}

// If you do not have the ports object indexed by ifName, this transposes it
function transformPortsIndexedByIfName($ports)
{
    $result = [];
    foreach ($ports->items() as $port_id => $port) {
		$ifName = $port->ifName;
		$result[$ifName] = $port;
	}
	return $result;
}

// Ports indexed by ifName, find highest switch index
function findHighestSwitchIndex($indexports)
{
	// Only works on ports deliminated with /
	// Cisco, Aruba
	$switch = 1;
	arsort($indexports);
	foreach($indexports as $ifName => $port) {
		$parts = explode('/', $ifName);
		switch(count($parts)) {
			case 3:
				preg_match("/([0-9]+)/", $parts[0], $switchmatch);
				$switch = $switchmatch[0];
				break;
		}
		// echo "found highest switch port $ifName";
		return $switch;
		
	}
	return $switch;
}

// Ports indexed by ifName, find lowest switch index
function findLowestSwitchIndex($indexports)
{
	// Only works on ports deliminated with /
	// Cisco, Aruba
	$switch = 1;
	//arsort($indexports);
	foreach($indexports as $ifName => $port) {
		$parts = explode('/', $ifName);
		switch(count($parts)) {
			case 3:
				preg_match("/([0-9]+)/", $parts[0], $switchmatch);
				$switch = $switchmatch[0];
				break;
		}
		// echo "found starting switch port $ifName";
		return $switch;
		
	}
	return $switch;
}

// Walk the ports array and by magic create switches, modules, blocks and columns in something that resembles an array. Physical location might well be wrong.
// For an accurate representation of the physical device we have device hints which we can use
// This is to generate something automatically for everything else, and not yet defined.
// The result array is used in the actual presentation function
function transformPorts($ports, $rowHeight = 2): array
{
    $result = [];

	$i = 0;
	$col = 0;
	$block = 0;
    foreach ($ports->items() as $port_id => $port) {
        // Extract ifName and split into components
		foreach(array("ifType", "ifName", "ifOperStatus", "ifAdminStatus", "ifVlan") as $field)
			$$field = $port[$field] ?? null;
			
        if ($ifName) {
			// only physical ports, ignore wifi, subinterfaces, loopback
			if(preg_match("/(lo|br|wifi|[.][0-9]+|ovpn|tun|tap|sit|enc)/i", $ifName))
				continue;
			if($ifType != "ethernetCsmacd")
				continue;

			// See if we have a prefix like GigabitEthernet
			preg_match("/^([a-z-_ ]+)/i", $ifName, $prefix);

			// Check if ascii prefix has changed, if so, increment module
			if((isset($prevprefix) && ($prevprefix != $prefix[0])))
				$block = $block + 1;

			// figure out the layout, count parts, split accordingly
            $parts = explode('/', $ifName);
			$partscount = count($parts);
			
			if((isset($prevpartscount) && ($prevpartscount != $partscount)))
				$block = $block + 1;
			
			// reset block count on new module, switch
			if((isset($prevmodule)) && ($prevmodule != $module))
				$block = 0;
			if((isset($prevswitch)) && ($prevswitch != $switch)) {
				$block = 0;
				// if the switchcount exceeds one, assume VSF stack and use that layout.
				return $result;

			}
			
			switch(count($parts)) {
					case 3:
						preg_match("/([0-9]+)/", $parts[0], $switchmatch);
						$switch = $switchmatch[0];
						$module = floatval($parts[1]) ?? null;
						$portNumber = $parts[2] ?? '1';
						$Height = $rowHeight;
						break;
					case 2:
						$switch = 1;
						$module = floatval($parts[0]);
						$portNumber = $parts[1] ?? '1';
						$Height = $rowHeight;
						break;
					default:
						/// Just the 1 item?
						$switch = 1;
						$module = 0;
						$portNumber = $parts[0] ?? '1';
						$Height = 1;
						break;
			}
			
			$col = floor($i / $Height);
            // Build the multi-dimensional array
			$result[$module][$block][$col][][$ifName] = "rj45";
			
			$prevmodule = $module;
			$prevswitch = $switch;
			if(isset($prefix[0]))
				$prevprefix = $prefix[0];

			if(isset($partscount))
				$prevpartscount = $partscount;

			$i++;
        }
    }

    return $result;
}


function generateVisualTableWithAttributes($switchstart = 1, $switchend = 1, array $transform, $indexports): string
{
    $html = '<div class="visual-table">';
	$switch = $switchstart;
    while ($switch <= $switchend) {
        // Create a container for the switch
        $html .= '<div class="switch-container">';
        //$html .= "<h3>Switch: $switch</h3>";
        $html .= "Switch: $switch</br>";

		foreach ($transform as $module => $blocks) {
			// Create a container for the module
			$html .= '<div class="module-container">';
			//$html .= "<h4>Module: $module</h4>";
			$html .= "Module: $module</br>";

			foreach ($blocks as $block => $columns) {
				// Create a container for the block
				$html .= '<div class="block-container">';
				//$html .= "<h4>Module: $module</h4>";
				$html .= "Block: $block</br>";
				$html .= '<table class="port-table">';
				// Get the maximum rows across columns
				$rowCount = max(array_map('count', $columns));
				for ($row = 0; $row < $rowCount; $row++) {
					$html .= '<tr>';
					foreach ($columns as $col => $ports) {
						$portData = $ports[$row] ?? null; // Retrieve port data if it exists						
						if ($portData) {
							
							foreach ($portData as $portName => $phy) {
								// Get last part for just number
								$portparts = array_reverse(explode("/", strval($portName)));
								// We only want the specific switch if the port has this info
								switch(count($portparts)) {
										case 3:
											preg_match("/([0-9]+)/", $portparts[2], $switchmatch);
											if($switchmatch[0 ]!= $switch)
												continue 3;
											break;
								}
								// Lookup port by port_id from transform array
								$attr = $indexports[$portName];
								$adminStatus = $attr->ifAdminStatus ?? 'down';
								$operStatus = $attr->ifOperStatus ?? 'down';
								$vlan = $attr->ifVlan ?? 'Unknown';

								// Determine the CSS classes based on statuses
								$borderClass = match ($adminStatus) {
									'up' => 'border-up',
									'disabled' => 'border-disabled',
									default => 'border-down',
								};

								$bgClass = ($operStatus === 'up') ? 'bg-up' : 'bg-down';


							   // Add the port cell with custom tooltip
							   $tooltipContent = <<<TOOLTIP
	<div>Port: $portName</div>
	<div>Admin Status: $adminStatus</div>
	<div>Oper Status: $operStatus</div>
	<div>VLAN: $vlan</div>
	TOOLTIP;

								$html .= "<td class='port-cell $borderClass $bgClass' >
							<div class='port-name'>{$portparts[0]}</div>
											<div class='tooltip'>$tooltipContent</div>
										  </td>";							
								
							}
						} else {
							// Empty cell
							$html .= "<td class='port-cell empty-cell'></td>";
						}
					}
					$html .= '</tr>';
				}
				$html .= '</table>';
				$html .= '</div>'; // End block container
			}
			$html .= '</table>';
			$html .= '</div>'; // End module container
		}
        $html .= '</div>'; // End switch container
		$switch++;
	}
    $html .= '</div>';

    return $html;
}