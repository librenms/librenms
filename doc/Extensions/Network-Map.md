source: Extensions/Network-Map.md

LibreNMS has the ability to show you a network map based on:

- xDP Discovery
- MAC addresses

By default, both are are included but you can enable / disable either one using the following config option:

```php
$config['network_map_items'] = array('mac','xdp');
```

Either remove mac or xdp depending on which you want.

A global map will be drawn from the information in the database, it is worth noting that this could lead to a large network map.
Network maps for individual devices are available showing the relationship with other devices.

One can also specify the parameters to be used for drawing and updating the network map.
Please see http://visjs.org/docs/network/ for details on the configuration parameters.
```php
$config['network_map_vis_options'] = '{
  layout:{
      randomSeed:2
  },
  "edges": {
    "smooth": {
        enabled: false
    },
    font: {
        size: 12,
        color: "red",
        face: "sans",
        background: "white",
        strokeWidth:3,
        align: "middle",
        strokeWidth: 2
    }
  },
  "physics": {
    "forceAtlas2Based": {
      "gravitationalConstant": -800,
      "centralGravity": 0.03,
      "springLength": 50,
      "springConstant": 0,
      "damping": 1,
      "avoidOverlap": 1
    },
    "maxVelocity": 50,
    "minVelocity": 0.01,
    "solver": "forceAtlas2Based",
    "timestep": 0.30
  }
}';
```

You may also access the dyanamic configuraiton interface (example here: http://visjs.org/examples/network/other/configuration.html) from within LibreNMS by adding the following as the first line of the network_map_vis_options parameters:
```php
$config['network_map_vis_options'] = '{
  "configure": { "enabled": true},
```

Note that you may want to disable the automatic page refresh while you're tweaking your configuration, as the refresh will reset the dynamic configuration UI to the values currently saved in librenms/config.php.

Once you've achieved your desired map appearance, click the generate options button at the bottom to be given the necessary parameters to add to your librenms/config.php file.
