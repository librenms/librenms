# Network Map

## LibreNMS has the ability to show you a network mab based on:

- xDP Discovery
- MAC addresses

By default, both are are included but you can enable / disable either one using the following config option:

```php
$config['network_map_items'] = array('mac','xdp');
```

Either remove mac or xdp depending on which you want.

A global map will be drawn from the information in the database, it is worth noting that this could lead to a large network map. 
Network maps for individual devices are available showing the relationship with other devices.
