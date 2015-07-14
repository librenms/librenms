# Globe Frontpage Configuration

LibreNMS comes with a configurable geochart based frontpage to visualize where your gear is located geographically.

To enable it, set `$config['front_page'] = "pages/front/globe.php";` in your `config.php`.

You can use any of these config-parameters to adjust some aspects of it:

- `$config['frontpage_globe']['markers']`    is used to change what is being shown on the Markers of the map. It can be either `devices` or `ports`
- `$config['frontpage_globe']['region']`     defines the Region to chart. Any region supported by Google's GeoChart API is allowed (https://developers.google.com/chart/interactive/docs/gallery/geochart#continent-hierarchy-and-codes)
- `$config['frontpage_globe']['resolution']` can be 'countries', 'provinces' or 'metros' (latter two are mostly US only due to google-limits).


