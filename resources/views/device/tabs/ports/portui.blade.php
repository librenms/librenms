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
function findHighestSwitchPort($indexports) {
	// Only works on ports deliminated with /
	// Cisco, Aruba
	$switch = 1;
	$indexports = array_reverse($indexports);
	foreach($indexports as $ifName => $port) {
		$parts = explode('/', $ifName);
		switch(count($parts)) {
			case 3:
				$switch = $parts[0];
				break;
		}
		// echo "found highest switch port $ifName";
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
			
			// figure out the layout, count parts, split accordingly
            $parts = explode('/', $ifName);
			switch(count($parts)) {
					case 3:
						$switch = $parts[0];
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
			// Check if ascii prefix has changed, if so, increment module
			if((isset($prevprefix) && ($prevprefix != $prefix[0])))
				$block = $block + 1;
			
			// reset block count on new module, switch
			if((isset($prevmodule)) && ($prevmodule != $module))
				$block = 0;
			if((isset($prevswitch)) && ($prevswitch != $switch))
				$block = 0;
			
			$col = floor($i / $Height);
            // Build the multi-dimensional array
			//foreach(array("ifType", "ifName", "ifOperStatus", "ifAdminStatus", "ifVlan") as $field)
			//	$tmp[$field] = $$field;

			$result[$module][$block][$col][][$ifName] = "rj45";
			
			$prevmodule = $module;
			$prevswitch = $switch;
			if(isset($prefix[0]))
				$prevprefix = $prefix[0];

			$i++;
        }
    }

    return $result;
}


function generateVisualTableWithAttributes($switches = 1, array $transform, $sourcedata): string
{
    $html = '<div class="visual-table">';

    for ($switch = 1; $switch <= $switches; $switch++) {
        // Create a container for the switch
        $html .= '<div class="switch-container">';
        //$html .= "<h3>Switch: $switch</h3>";
        $html .= "Switch: $switch</br>";
		//foreach($transform as $modules) {

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
									$portnum = array_reverse(explode("/", strval($portName)));
									// Lookup port by port_id from transform array
									$attr = $sourcedata[$portName];
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
								<div class='port-name'>{$portnum[0]}</div>
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
		//}
        $html .= '</div>'; // End switch container
	}
    $html .= '</div>';

    return $html;
}


$columnHeight = 2;
$indexports = transformPortsIndexedByIfName($data['ports']);
$switches = findHighestSwitchPort($indexports);

// Find model name or systemname
$prefix = $device->sysName;
//echo "Found model $prefix from device, ";
// Deduce hints file from icon
$brand = substr(basename($device->icon), 0, -4);
// echo print_r($device, true);
$filePath = "../resources/views/device/tabs/ports/hints/{$brand}.hints";
if(file_exists($filePath)) {
	$line = fastFindLine($filePath, $prefix);
	if(!empty($line)) {
		echo "Found prefix $prefix in {$brand} hints, ";
		echo print_r(substr($line, strlen($prefix)+1), true);
		$transformedPorts = json_decode(substr($line, strlen($prefix)+1), true);
	}
}

if(!isset($transformedPorts)) {
	if (empty($transformedPorts)) {
		echo "No hints file found, auto generating";
		$transformedPorts = transformPorts($data['ports'], 2);
	}
}

// $transform = $transformedPorts;
// echo json_encode($transform);



echo "</pre>";
?>

		<?php


		// Example usage with your updated array:
		echo generateVisualTableWithAttributes($switches, $transformedPorts, $indexports);

		?>

<x-panel body-class="!tw-p-0">
    <table id="ports-ui" class="table table-condensed table-hover table-striped tw-mt-1 !tw-mb-0">
        <thead>
        </thead>
		</table>
</x-panel>
