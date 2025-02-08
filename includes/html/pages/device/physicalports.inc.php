<?php

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
		echo "<!-- Found hardware '{$hardware}' in '{$brand}' hints  -->\n";
		// echo print_r(substr($line, strlen("{$hardware}:")), true);

		// if json does not decode, the return is false and we catch later
		$transformedPorts = generateTransformPorts(json_decode(substr(trim($line), strlen($hardware)+1), true));
		if (!isset($transformedPorts)) {
			echo "<!--  Failed to decode json string, is it valid? -->\n";
		}
	}
}

if(!isset($transformedPorts)) {
	if (empty($transformedPorts)) {
		echo "<!-- No hints found for '{$brand}' model '{$hardware}', auto generating -->\n";
		$transformedPorts = transformPortsAuto($data['ports'], 2);
	}
}

if(isset($data['ports'])) {
	$indexports = transformPortsIndexedByIfName($data['ports']);
	$switches = findSwitchesRange($indexports);
	echo "<!-- Used hint </br>'". trim(substr($line, strlen($hardware)+1)) ."'</br>produced </br>". json_encode($transformedPorts) ." -->\n";
	echo "<!-- Found switches: ". print_r($switches, true) ." -->\n";
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
echo generateVisualTableWithAttributes($switches, $transformedPorts, $indexports);


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

// Ports indexed by ifName, find switch range
function findSwitchesRange($indexports)
{
	// Only works on ports deliminated with /, alternative preg_split()
	// Cisco, Aruba, Juniper
	$switches = array();
	foreach($indexports as $ifName => $port) {
		$parts = explode('/', $ifName);
		switch(count($parts)) {
			case 3:
				preg_match("/([0-9]+)/", $parts[0], $switchmatch);
				$switches[$switchmatch[0]] = true;
				break;
			default:
				continue;
		}
		// echo "found highest switch port $ifName";		
	}
	if(empty($switches))
		$switches[1] = true;
	
	return $switches;
}

// Walk the ports array and by magic create switches, modules, blocks and columns in something that resembles an array. Physical location might well be wrong.
// For an accurate representation of the physical device we have device hints which we can use
// This is to generate something automatically for everything else, and not yet defined.
// The result array is used in the actual presentation function
function transformPortsAuto($ports, $rowHeight = 2): array
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
			
			// Couldn't determine if this was a cu, sfp, qsfp on Aruba
			// if($portNumber == 52)
			// echo print_r($port, true);

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


function generateVisualTableWithAttributes($switches = array(1 => true), array $transform, $indexports): string
{
    $html = '<div class="visual-table">';
    foreach ($switches as $switch => $id) {
        // Create a container for the switch
        $html .= '<div class="switch-container">';
		//$html .= "Switch: $switch</br>";

		foreach ($transform as $module => $blocks) {
			// Create a container for the module, but only if there is more then 1.
			if(count($transform) > 1) {
				$html .= '<div class="module-container">';
				//$html .= "Module: $module</br>";
			}

			foreach ($blocks as $block => $columns) {
				// Create a container for the block, but only if there is more then 1.
				if(count($blocks) > 1) {
					$html .= '<div class="block-container">';
					//$html .= "Block: $block</br>";
				}
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
							   // add tooltip helper here, doesn't work yet.
								$html .= "<td class='port-cell $borderClass $bgClass' >
							<div class='port-name'>{$portparts[0]}</div>
											<div class='tooltip'>tooltipContentholder</div>
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
				if(count($blocks) > 1) 
					$html .= '</div>'; // End block container
			}
			$html .= '</table>';
			if(count($transform) > 1)
				$html .= '</div>'; // End module container
		}
        $html .= '</div>'; // End switch container
	}
    $html .= '</div>';

    return $html;
}