source: Extensions/Globe-Frontpage.md
# Globe Frontpage Configuration

LibreNMS comes with a configurable geochart based widget to visualize where your equipment is located geographically.

### World Map

An new map is available, this requires you to have properly formatted addresses in sysLocation or sysLocation override. As part of the standard poller these addresses will be Geocoded by Google and stored in the database. To enable this please set the following config:

```php
$config['geoloc']['latlng'] = true;
$config['geoloc']['engine'] = "google";//Only one available at present
```

Location resolution happens as follows (when `$config['geoloc']['latlng'] == true;`):
 1. If `device['location']` contains `[lat, lng]` (note the square brackets), that is used
 1. If there is a location overide for the device in the WebUI and it contains `[lat, lng]` (note the square brackets), that is used.
 1. Attempt to resolve lat, lng using `$config['geoloc']['engine']`

We have two current mapping engines available:

- Leaflet (default)
- Jquery-Mapael


### Leaflet config

This is a simple engine to use yet is quite powerful, here you can see how to enable this engine and zoom to a default place.

```php
$config['map']['engine']                                = "leaflet";
$config['leaflet']['default_lat']                       = "50.898482";
$config['leaflet']['default_lng']                       = "-3.401402";
$config['leaflet']['default_zoom']                       = 8;
```

If you can't access OpenStreet map directly you can run a local [tile server](http://wiki.openstreetmap.org/wiki/Tile_servers). To specify a different url you can set:

```php
$config['leaflet']['tile_url'] = 'localhost.com';
```

### Jquery-Mapael config
Further custom options are available to load different maps of the world, set default coordinates of where the map will zoom and the zoom level by default. An example of
this is:

```php
$config['map']['engine']                                = "jquery-mapael";
$config['mapael']['default_map'] = 'mapael-maps/united_kingdom/united_kingdom.js';
$config['mapael']['map_width'] = 400;
$config['mapael']['default_lat'] = '50.898482';
$config['mapael']['default_lng'] = '-3.401402';
$config['mapael']['default_zoom'] = 20;
```

A list of maps can be found in html/js/maps/ or html/js/mapael-maps/.
