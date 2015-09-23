# Configuring interface descriptions for parsing.

LibreNMS includes the ability to parse your interface descriptions for set information to diplay and segement in the WebUI.

The following information is used from interface descriptions:

 - Type. This is currently Cust (Customer), Peering, Transit and Core
 - Circuit information
 - Notes
 - Speed

When setting the description, you use the type followed directly with : and then the information on that port. Some examples based on 
configuring a Cisco 2960 interface

#### Customer port
description Cust: Customer A

#### Transport port
description Transit: ISP A

#### Peering port
description Peering: Local Peer

#### Core port
description Core: Agg connection

Having these set will then enable the menu options in Ports within the top navigation.

The following config options can be set to enable more custom types:

```php
$config['customers_descr']         = 'cust'; // The description to look for in ifDescr. Can be an array as well array('cust','cid');
$config['transit_descr']           = ""; // Add custom transit descriptions (can be an array)
$config['peering_descr']           = ""; // Add custom peering descriptions (can be an array)
$config['core_descr']              = ""; // Add custom core descriptions (can be an array)
$config['custom_descr']            = ""; // Add custom interface descriptions (can be an array)
```

To further enhance the use of port descriptions with Circuit info, Notes and Speed then these can be done as follows:

#### Circuit information

{}

i.e:
description: Cust: Customer A {ID4321}

#### Notes

()

i.e:
description Cust: Customer A (This customer is gold)

#### Speed

[]

i.e:
description Cust: Customer A [100Mbs]

You can use any of these additional options like:

description Cust: Customer A (this customer is gold) [10Gbps]
description Cust: Customer A {ID4321} [1Gbps]

This information is then held within the ports table within the database, as an example:

description Core: Nas bond [1Gbps]
```sh
port_descr_type: core
port_descr_descr: Nas bond
port_descr_circuit: NULL
port_descr_speed: 1Gbps
port_descr_notes: NULL
```

### Custom interface parser

It's also possible to write your own parser, the existing one is: includes/port-descr-parser.inc.php

Once you've created your own then you can enable it with:

```php
$config['port_descr_parser'] = "includes/my-port-descr-parser.inc.php";
```
