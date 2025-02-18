# Notes On Physical Port UI Presentation

It is possible to add custom Physical Port layouts for devices by adding JSON output for said device in a hints file for when the automatic generation does not accurately match the physical presentation.
Examples are the Aruba CX6100 with 49-52 on the left side of the chassis, or the Aruba CX8325 which is 3 rows of ports.

These files live in `resources/views/device/portui/`
The naming is `{brand}/{modelname}.json` where brand is based on the icon name assigned in LibreNMS e.g. "ubiquiti.svg" becomes directory ubiquiti.
The contents are a identifier based on the "Hardware" tag, containing a JSON string with interface port mapping.

# PortUI JSON file format description

The JSON is based on a JSON meta array which basically reads the front of the chassis columns from left to right.
 - For multi module/blade chassis, you can add the ports into seperate "modules"
 - Group the ports as part of a "block" of ports which are most common part of a speed/interface type.
 - Blocks numbers and interface names can exist multiple times for combo cu/sfp ports
 - Each column of switch ports, each interface has $ifName as the key and a phytype as the value.
 - Supported phy types differentiated are cu, sfp, qsfp, support for parsing the 'entPhysicalVendorType' attribute is vendor dependent.
 - The Rear of the chassis is currently not rendered

The filename should look something like this, spaces are replaced with a underscore, lowercase only.

````6100_48g_class4_poe_4sfp+_370w_switch.json````

The Meta language is as follows, for easier reading.

 - height, defines rowheight for each column
 - invers, ordering top to bottom
 - block number, does not require sequential, same block is combined ports
 - count, number of ports in sequence
 - type, cu, sfp, qsfp
 - start, interface number and formatting

# Example JSON
	 
Example JSON string for Aruba 6100 below.
	
````json
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
````

A bit more Complex example of a Cisco ASR920 12CZ

````JSON
{
  "front": [
    {
      "height": "1",
      "inverse": "0",
      "block": "0",
      "count": "1",
      "type": "ac",
      "start": "ac1"
    },
    {
      "height": "1",
      "inverse": "0",
      "block": "1",
      "count": "1",
      "type": "ac",
      "start": "ac2"
    },
    {
      "height": "1",
      "inverse": "0",
      "block": "2",
      "count": "1",
      "type": "cu",
      "start": "Gi0"
    },
    {
      "height": "2",
      "inverse": "1",
      "block": "3",
      "count": "4",
      "type": "sfp",
      "start": "Gi0/0/0"
    },
    {
      "height": "2",
      "inverse": "1",
      "block": "4",
      "count": "8",
      "type": "sfp",
      "start": "Gi0/0/4"
    },
    {
      "height": "2",
      "inverse": "1",
      "block": "4",
      "count": "8",
      "type": "cu",
      "start": "Gi0/0/4"
    },
    {
      "height": "2",
      "inverse": "1",
      "block": "5",
      "count": "2",
      "type": "sfp",
      "start": "Te0/0/12"
    }
  ],
  "rear": [
    []
  ]
}
````
