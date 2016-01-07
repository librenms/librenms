# Globe Frontpage Configuration

LibreNMS comes with a configurable geochart based frontpage to visualize where your gear is located geographically.

### Experimentall map

An new experimental map is available, this requires you to have properly formatted addresses in sysLocation or sysLocation override. As part of the standard poller these addresses will be Geocoded by Google and stored in the database. To enable this please set the following config:

```php
$config['front_page']       = "pages/front/map.php";
$config['geoloc']['latlng'] = true;
$config['geoloc']['engine'] = "google";//Only one available at present
```

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

### Standard Globe map

To enable it, set `$config['front_page'] = "pages/front/globe.php";` in your `config.php`.

You can use any of these config-parameters to adjust some aspects of it:

- `$config['frontpage_globe']['markers']`    is used to change what is being shown on the Markers of the map. It can be either `devices` or `ports`
- `$config['frontpage_globe']['region']`     defines the Region to chart. Any region supported by Google's GeoChart API is allowed (https://developers.google.com/chart/interactive/docs/gallery/geochart#continent-hierarchy-and-codes)
- `$config['frontpage_globe']['resolution']` can be 'countries', 'provinces' or 'metros' (latter two are mostly US only due to google-limits).


