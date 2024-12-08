# World Map Configuration

LibreNMS comes with a configurable Geo Map based on World Map Widget
to visualize where your equipment is located geographically.

## World Map Widget

World Map Widget, requires you to have properly formatted addresses in
sysLocation or sysLocation override. As part of the standard poller
these addresses will be Geocoded by Google and stored in the database.

Location resolution happens as follows

1. If `device['location']` contains `[lat, lng]` (note the square
   brackets), that is used
1. If there is a location overide for the device in the WebUI and it
   contains `[lat, lng]` (note the square brackets), that is used.
1. Attempt to resolve lat, lng using `lnms config:set geoloc.engine`
1. Properly formatted addresses in sysLocation or sysLocation
   override, under device settings.

Example:

```
[40.424521, -86.912755]
```

or

```
1100 Congress Ave, Austin, TX 78701 (3rd floor cabinet)
```
*Information inside parentheses is ignored during GEO lookup*

We have two current mapping engines available:

- Leaflet (default)
- Jquery-Mapael

### World Map Widget Settings

- *Initial Latitude / Longitude*: The map will be centered on those
  coordinates.
- *Initial Zoom*: Initial zoom of the map. [More information about
  zoom levels](https://wiki.openstreetmap.org/wiki/Zoom_levels).
- *Grouping radius*: Markers are grouped by area. This value define
  the maximum size of grouping areas.
- *Show devices*: Show devices based on status.

Example Settings:

![Example World Map Settings](/img/world-map-widget-settings.png)

### Device Overview World Map Settings

If a device has a location with a valid latitude and logitude, the
device overview page will have a panel showing the device on a world
map.  The following settings affect this map:

```bash
# Does the world map start opened, or does the user need to clivk to view
lnms config:set device_location_map_open false
# Do we show all other devices on the map as well
lnms config:set device_location_map_show_devices false
# Do we show a network map based on device dependencies
lnms config:set device_location_map_show_device_dependencies false
```

## Offline OpenStreet Map

If you can't access OpenStreet map directly you can run a local [tile
server](http://wiki.openstreetmap.org/wiki/Tile_servers). To specify a
different url you can set:

```bash
lnms config:set leaflet.tile_url 'localhost.com'
```

## Additional Leaflet config

```bash
lnms config:set map.engine leaflet
lnms config:set leaflet.default_lat "51.981074"
lnms config:set leaflet.default_lng "5.350342"
lnms config:set leaflet.default_zoom 8
# Device grouping radius in KM default 80KM
lnms config:set leaflet.group_radius 1
# Enable network map on world map
lnms config:set network_map_show_on_worldmap true
# Use CDP/LLDP for network map, or device dependencies
lnms config:set network_map_worldmap_link_type xdp/depends
# Do not show devices that have notifications disabled
lnms config:set network_map_worldmap_show_disabled_alerts false
```

## Geocode engine config

!!! setting "external/location"
    ```bash
    lnms config:set geoloc.engine google
    lnms config:set geoloc.api_key 'abcdefghijklmnopqrstuvwxyz'
    ```

Google:  
Pros: fast, accurate  
Cons: requires a credit card even for a free account

MapQuest:  
Pros: free, no credit card required  
Cons: inaccurate: most addresses are returned as locations at the center of the US

Bing:  
Pros: free, no credit card required, accurate  
Cons: Microsoft (debatable)

## Jquery-Mapael config

Further custom options are available to load different maps of the
world, set default coordinates of where the map will zoom and the zoom
level by default. An example of this is:

```bash
lnms config:set map.engine jquery-mapael
lnms config:set mapael.default_map 'mapael-maps/united_kingdom/united_kingdom.js'
lnms config:set mapael.map_width 400
lnms config:set mapael.default_lat '50.898482'
lnms config:set mapael.default_lng '-3.401402'
lnms config:set mapael.default_zoom 20
```

A list of maps can be found in ```html/js/maps/``` or ```html/js/mapael-maps/```.
