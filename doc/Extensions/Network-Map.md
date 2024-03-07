# Network Map

LibreNMS has the ability to show you a dynamic network map based on
data collected from devices.  These maps are accessed through the
following menu options:

 - Overview -> Maps -> Network
 - Overview -> Maps -> Device Group Maps
 - The Neighbours -> Map tab when viewing a single device
   (the Neighbours tab will only show if a device has xDP neighbours)

These network maps can be based on:

- xDP Discovery
- MAC addresses (ARP entries matching interface IP and MAC)

By default, both are are included but you can enable / disable either
one using the following config option:

```bash
lnms config:set 'network_map_items' "('mac','xdp')"
```

Either remove mac or xdp depending on which you want.
XDP is based on FDP, CDP and LLDP support based on the device type.

It is worth noting that the global map could lead to a large network
map that is slow to render and interact with. The network map on the
device neighbour page, or building device groups and using the device
group maps will be more usable on large networks.

## Settings
The map display can be configured by altering the [VisJS-Config.md](Vis JS Options)
