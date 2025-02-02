This folder holds the hints files for physical port layouts. This makes it possible to assign physical port layouts for which the automatic generation is ill-suited.

Examples are the Aruba CX6100 with 49-52 on the left side of the chassis, or the CX8325 which is 3 rows of ports. Or the fs S5850 with ports in the middle.

- The file naming is {$brand}.hints where brand is based on the icon name assigned in LibreNMS e.g. "ubiquiti.svg" becomes ubiquiti.hints.

- The file contents are a SNMP readable identifier and a JSON port mapping delimited by a colon. The Identifier should ideally be a SKU, with fallback to sysName.

- The JSON is based on a multidimensonal PHP array which basically reads the front of the chassis from left to right.
	For multi module/blade chassis, you can add the ports into seperate "modules"
	You can group the ports as part of a "block" of ports which are most common part of a speed/interface type.
	You then add ports to each column of switch ports, each interface has $ifName as the key and a phytype as the value.
	Supported phy types differentiated are rj45, sfp, qsfp.
	Ports with a interface name can exist multiple times for combo ports.

	FIXME: Apply portmap to multiple switches in a stack. 
	PHP Array layout
	$portmap[$module][$block][$column][][$ifName] = $phy;

	Example: 1 module, 2 blocks, TenGig ports are combo in the same block next column
	$portmap[0][0][1][][Gigabitethernet1/0/1] = "rj45"
	$portmap[0][0][1][][Gigabitethernet1/0/2] = "rj45"
	$portmap[0][1][2][][TenGigabitethernet1/0/1] = "rj45"
	$portmap[0][1][2][][TenGigabitethernet1/0/2] = "rj45"
	$portmap[0][1][3][][TenGigabitethernet1/0/1] = "sfp"
	$portmap[0][1][3][][TenGigabitethernet1/0/2] = "sfp"
	
	Example: 1 module, 2 blocks, TenGig ports are combo in the same column, sfp top, rj45 bottom.
	$portmap[0][0][1][][Gigabitethernet1/0/1] = "rj45"
	$portmap[0][0][1][][Gigabitethernet1/0/2] = "rj45"
	$portmap[0][1][2][][TenGigabitethernet1/0/1] = "sfp"
	$portmap[0][1][2][][TenGigabitethernet1/0/1] = "rj45"
	$portmap[0][1][3][][TenGigabitethernet1/0/2] = "sfp"
	$portmap[0][1][3][][TenGigabitethernet1/0/2] = "rj45"

	Then convert this PHP array to a single json string which can be included in the appropriate hints file.