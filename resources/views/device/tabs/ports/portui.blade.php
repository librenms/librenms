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

<?php
echo "<pre>";

function fastFindLine($filePath, $prefix) {
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

function transformPortsIndexedByIfName($portsPaginator) {
    $result = [];
    foreach ($portsPaginator->items() as $port_id => $port) {
		$ifName = $port->ifName;
		$result[$ifName] = $port;
	}
	return $result;
}

// Ports indexed by ifName
function findHighestSwitchIndex($indexports) {
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

// Ports indexed by ifName
function findLowestSwitchIndex($indexports) {
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
			preg_match("/^([a-z-_]+)/i", $ifName, $prefix);

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

function convert($size)
 {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 }

$columnHeight = 2;
// Find hardware, model name or systemname for hints
$hardware  = $device->hardware;
if(!isset($hardware))
	$hardware = $device->sysName; // Try using sysName

// Deduce hints file from icon
$brand = substr(basename($device->icon), 0, -4);
//echo print_r($device, true);
$filePath = "../resources/views/device/tabs/ports/hints/{$brand}.hints";
if(file_exists($filePath)) {
	$line = fastFindLine($filePath, $hardware);
	if(!empty($line)) {
		echo "Found SKU $hardware in {$brand} hints ";
		// echo print_r(substr($line, strlen("{$hardware}:")), true);
		$transformedPorts = json_decode(substr($line, strlen($hardware)+1), true);
	}
}

if(!isset($transformedPorts)) {
	if (empty($transformedPorts)) {
		echo "No valid hints found, auto generating";
		$transformedPorts = transformPorts($data['ports'], 2);
	}
}

$indexports = transformPortsIndexedByIfName($data['ports']);
$switchstart = findLowestSwitchIndex($indexports);
$switchend = findHighestSwitchIndex($indexports);

// echo "Found highest switch: ". print_r($switches, true);
// $transform = $transformedPorts;
// echo "<br/>". json_encode($transformedPorts);
// echo convert(memory_get_usage(true)); // 123 kb

echo "</pre>";
?>

		<?php


		// Example usage with your updated array:
		echo generateVisualTableWithAttributes($switchstart, $switchend, $transformedPorts, $indexports);
		// echo convert(memory_get_usage(true)); // 123 kb

		?>

<x-panel body-class="!tw-p-0">
    <table id="ports-ui" class="table table-condensed table-hover table-striped tw-mt-1 !tw-mb-0">
        <thead>
        </thead>
		</table>
</x-panel>
