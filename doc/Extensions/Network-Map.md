source: Extensions/Network-Map.md
path: blob/master/doc/

# Network Map

LibreNMS has the ability to show you a network map based on:

- xDP Discovery
- MAC addresses

By default, both are are included but you can enable / disable either
one using the following config option:

```php
$config['network_map_items'] = array('mac','xdp');
```

Either remove mac or xdp depending on which you want.
XDP is based on FDP, CDP and LLDP support based on the device type.

A global map will be drawn from the information in the database, it is
worth noting that this could lead to a large network map. Network maps
for individual devices are available showing the relationship with
other devices. Also you can Build Device Groups and those Device
Groups can be drawn with Network Map.

# Network Map Configurator

[This link](https://visjs.github.io/vis-network/docs/network/) will
show you all the options and explain what they do.

You may also access the dynamic configuration interface [example
here](https://visjs.github.io/vis-network/examples/network/other/configuration.html)
from within LibreNMS by adding the following to config.php

```php
$config['network_map_vis_options'] = '{
  "configure": { "enabled": true},
}';
```

## Note

You may want to disable the automatic page refresh while you're
tweaking your configuration, as the refresh will reset the dynamic
configuration UI to the values currently saved in config.php This can
be done by clicking on the Settings Icon then Refresh Pause.

## Configurator Output

Once you've achieved your desired map appearance, click the generate
options button at the bottom to be given the necessary parameters to
add to your config.php file. You will need to paste the genrated
config into config.php the format will need to look something like
this. Note that the configurator will output the config with `var options`
you will need to strip them out and at the end of the config you need to
add an `}';` see the example below.

```php
$config['network_map_vis_options'] = '{
  "nodes": {
    "color": {
      "background": "rgba(20,252,18,1)"
    },
    "font": {
      "face": "tahoma"
    },
    "physics": false
  },
  "edges": {
    "smooth": {
      "forceDirection": "none"
    }
  },
  "interaction": {
    "hover": true,
    "multiselect": true,
    "navigationButtons": true
  },
  "manipulation": {
    "enabled": true
  },
  "physics": {
    "barnesHut": {
      "avoidOverlap": 0.11
    },
    "minVelocity": 0.75
  }
}';
```

![Example Network Map](/img/networkmap.png)
