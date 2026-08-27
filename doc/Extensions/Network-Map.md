# Network Map

LibreNMS shows a dynamic network map from the data of your devices.
These menu options open the maps:

 - Overview -> Maps -> Network
 - Overview -> Maps -> Device Group Maps
 - The Neighbours -> Map tab when viewing a single device
   The Neighbours tab appears only for a device with xDP neighbours

A network map uses one of these sources:

- xDP Discovery
- MAC addresses (ARP entries matching interface IP and MAC)

By default, LibreNMS uses both sources. This configuration option
enables and disables each source:

```bash
lnms config:set network_map_items '["mac","xdp"]'
```

Remove `mac` or `xdp` from the list. xDP uses FDP, CDP, or LLDP,
from the device type.

The global map can become large. A large map draws slowly and responds
slowly. On a large network, use the network map of the device neighbour
page. You can also build device groups and use the device group maps.

## Settings
The [Vis JS Options](VisJS-Config.md) configure the map display.
