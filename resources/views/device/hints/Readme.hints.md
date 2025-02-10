This folder holds the hints files for physical port layouts. This makes it possible to assign physical port layouts for which the automatic generation is ill-suited.

Examples are the Aruba CX6100 with 49-52 on the left side of the chassis, or the CX8325 which is 3 rows of ports. Or the fs S5850 with ports in the middle.

- The file naming is {$brand}.hints where brand is based on the icon name assigned in LibreNMS e.g. "ubiquiti.svg" becomes ubiquiti.hints.

- The file contents are a SNMP readable interface identifier (hardware) colon seperates a JSON interface port mapping delimited by a colon. The Identifier should ideally be a unique SKU.

- The JSON is based on a JSON meta array which basically reads the front of the chassis from left to right.
	- For multi module/blade chassis, you can add the ports into seperate "modules"
	- Group the ports as part of a "block" of ports which are most common part of a speed/interface type.
	- Port interface names can exist multiple times for combo ports
	- Each column of switch ports, each interface has $ifName as the key and a phytype as the value.
	- Supported phy types differentiated are cu, sfp, qsfp, support for parsing the 'entPhysicalVendorType' attribute is vendor dependent.

	Example hardware id colon seperate JSON string below.
	6100 48G Class4 PoE 4SFP+ 370W Switch:{"front":[{"height":"2","inverse":"0","block":"0","count":"4","type":"sfp","start":"1\/1\/49"},{"height":"2","inverse":"0","block":"1","count":"48","type":"cu","start":"1\/1\/1"}]}

	You can pretty print this for easier reading, but make sure to flatten it to a single line for the hints file.
	The Meta language is as follows, for easier reading.

	 - height, defines rowheight for each column
	 - invers, ordering top to bottom
     - block number, does not require sequential, same block is combined ports
	 - count, number of ports in sequence
	 - type, cu, sfp, qsfp
	 - start, interface number and formatting
	 
	{
     "front": [
      {
         "height": "2",
         "inverse": "0",
         "block": "0",
         "count": "4",
         "type": "sfp",
         "start": "1/1/49"
       },
       {
         "height": "2",
         "inverse": "0",
         "block": "1",
         "count": "48",
         "type": "cu",
         "start": "1/1/1"
       }
     ]
	}
	