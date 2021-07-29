source: API/Devices.md
path: blob/master/doc/

### `del_device`

Delete a given device.

Route: `/api/v0/devices/:hostname`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:

```json
{
    "status": "ok",
    "message": "Removed device localhost",
    "devices": [
        {
            "device_id": "1",
            "hostname": "localhost",
            ...
            "serial": null,
            "icon": null
        }
    ]
}
```

### `get_device`

Get details of a given device.

Route: `/api/v0/devices/:hostname`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:

```json
{
    "status": "ok",
    "devices": [
        {
            "device_id": "1",
            "hostname": "localhost",
            ...
            "serial": null,
            "icon": null
        }
    ]
}
```

### `discover_device`

Trigger a discovery of given device.

Route: `/api/v0/devices/:hostname/discover`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/discover
```

Output:

```json
{
    "status": "ok",
    "result": {
        "status": 0,
        "message": "Device will be rediscovered"
    },
    "count": 2
}
```

### `availability`

Get calculated availabilities of given device.

Route: `/api/v0/devices/:hostname/availability`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/availability
```

Output:

```json
{
    "status": "ok",
    "availability": [
        {
            "duration": 86400,
            "availability_perc": "100.000000"
        },
        {
            "duration": 604800,
            "availability_perc": "100.000000"
        },
        {
            "duration": 2592000,
            "availability_perc": "99.946000"
        },
        {
            "duration": 31536000,
            "availability_perc": "99.994000"
        }
    ],
    "count": 4
}
```

### `outages`

Get detected outages of given device.

Route: `/api/v0/devices/:hostname/outages`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/outages
```

Output:

```json
{
    "status": "ok",
    "outages": [
        {
            "going_down": 1593194031,
            "up_again": 1593194388
        },
        {
            "going_down": 1593946507,
            "up_again": 1593946863
        },
        {
            "going_down": 1594628616,
            "up_again": 1594628968
        },
        {
            "going_down": 1594628974,
            "up_again": 1594629339
        },
        {
            "going_down": 1594638668,
            "up_again": 1594638992
        }
    ],
    "count": 5
}
```

### `get_graphs`

Get a list of available graphs for a device, this does not include ports.

Route: `/api/v0/devices/:hostname/graphs`

- hostname can be either the device hostname or id

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/graphs
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 3,
    "graphs": [
        {
            "desc": "Poller Time",
            "name": "device_poller_perf"
        },
        {
            "desc": "Ping Response",
            "name": "device_ping_perf"
        },
        {
            "desc": "System Uptime",
            "name": "uptime"
        }
    ]
}
```

### `list_available_health_graphs`

This function allows to do three things:

- Get a list of overall health graphs available.
- Get a list of health graphs based on provided class.
- Get the health sensors information based on ID.

Route: `/api/v0/devices/:hostname/health(/:type)(/:sensor_id)`

- hostname can be either the device hostname or id
- type (optional) is health type / sensor class
- sensor_id (optional) is the sensor id to retrieve specific information.

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/health
```

Output:

```
{
    "status": "ok",
    "message": "",
    "count": 2,
    "graphs": [
        {
            "desc": "Airflow",
            "name": "device_airflow"
        },
        {
            "desc": "Voltage",
            "name": "device_voltage"
        }
    ]
}
```

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/health/device_voltage
```

Output:

```
{
    "status": "ok",
    "message": "",
    "count": 2,
    "graphs": [
        {
            "sensor_id": "1",
            "desc": "Input Feed A"
        },
        {
            "sensor_id": "2",
            "desc": "Output Feed"
        }
    ]
}
```

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/health/device_voltage/1
```

Output:

```
{
    "status": "ok",
    "message": "",
    "count": 1,
    "graphs": [
        {
            "sensor_id": "1",
            "sensor_deleted": "0",
            "sensor_class": "voltage",
            "device_id": "1",
            "poller_type": "snmp",
            "sensor_oid": ".1.3.6.1.4.1.318.1.1.27.1.1.0",
            "sensor_index": "1",
            "sensor_type": "apc",
            "sensor_descr": "Input",
            "sensor_divisor": "1",
            "sensor_multiplier": "1",
            "sensor_current": "1",
            "sensor_limit": "1.15",
            "sensor_limit_warn": null,
            "sensor_limit_low": "0.85",
            "sensor_limit_low_warn": null,
            "sensor_alert": "1",
            "sensor_custom": "No",
            "entPhysicalIndex": null,
            "entPhysicalIndex_measured": null,
            "lastupdate": "2017-01-13 13:50:26",
            "sensor_prev": "1"
        }
    ]
}
```

### `list_available_wireless_graphs`

This function allows to do three things:

- Get a list of overall wireless graphs available.
- Get a list of wireless graphs based on provided class.
- Get the wireless sensors information based on ID.

Route: `/api/v0/devices/:hostname/wireless(/:type)(/:sensor_id)`

- hostname can be either the device hostname or id
- type (optional) is wireless type / wireless class
- sensor_id (optional) is the sensor id to retrieve specific information.

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/wireless
```

Output:

```
{
    "status": "ok",
    "graphs": [
        {
            "desc": "Ccq",
            "name": "device_wireless_ccq"
        },
        {
            "desc": "Clients",
            "name": "device_wireless_clients"
        }
    ],
    "count": 2
}
```

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/wireless/device_wireless_ccq
```

Output:

```
{
    "status": "ok",
    "graphs": [
        {
            "sensor_id": "791",
            "desc": "SSID: bast (ng)"
        },
        {
            "sensor_id": "792",
            "desc": "SSID: bast (na)"
        }
    ],
    "count": 2
}
```

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/health/device_wireless_ccq/1
```

Output:

```
{
    "status": "ok",
    "graphs": [
        {
            "sensor_id": "791",
            "sensor_deleted": "0",
            "sensor_class": "ccq",
            "device_id": "381",
            "sensor_index": "0",
            "sensor_type": "unifi",
            "sensor_descr": "SSID: bast (ng)",
            "sensor_divisor": "10",
            "sensor_multiplier": "1",
            "sensor_aggregator": "sum",
            "sensor_current": "100",
            "sensor_prev": "100",
            "sensor_limit": null,
            "sensor_limit_warn": null,
            "sensor_limit_low": null,
            "sensor_limit_low_warn": null,
            "sensor_alert": "1",
            "sensor_custom": "No",
            "entPhysicalIndex": null,
            "entPhysicalIndex_measured": null,
            "lastupdate": "2017-12-06 21:26:29",
            "sensor_oids": "[\".1.3.6.1.4.1.41112.1.6.1.2.1.3.0\"]",
            "access_point_id": null
        }
    ],
    "count": 1
}
```

### `get_health_graph`

Get a particular health class graph for a device, if you provide a
sensor_id as well then a single sensor graph will be provided. If no
sensor_id value is provided then you will be sent a stacked sensor graph.

Route: `/api/v0/devices/:hostname/graphs/health/:type(/:sensor_id)`

- hostname can be either the device hostname or id
- type is the name of the health graph as returned by [`list_available_health_graphs`](#function-list_available_health_graphs)
- sensor_id (optional) restricts the graph to return a particular health sensor graph.

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/graphs/health/device_voltage
```

Output:

Output is a stacked graph for the health type provided.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/graphs/health/device_voltage/1
```

Output:

Output is the graph of the particular health type sensor provided.

### `get_wireless_graph`

Get a particular wireless class graph for a device, if you provide a
sensor_id as well then a single sensor graph will be provided. If no
sensor_id value is provided then you will be sent a stacked wireless graph.

Route: `/api/v0/devices/:hostname/graphs/wireless/:type(/:sensor_id)`

- hostname can be either the device hostname or id
- type is the name of the wireless graph as returned by [`list_available_wireless_graphs`](#function-list_available_wireless_graphs)
- sensor_id (optional) restricts the graph to return a particular
  wireless sensor graph.

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/graphs/wireless/device_wireless_ccq
```

Output:

Output is a stacked graph for the wireless type provided.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/graphs/wireless/device_wireless_ccq/1
```

Output:

Output is the graph of the particular wireless type sensor provided.

### `get_graph_generic_by_hostname`

Get a specific graph for a device, this does not include ports.

Route: `/api/v0/devices/:hostname/:type`

- hostname can be either the device hostname or id
- type is the type of graph you want, use
  [`get_graphs`](#function-get_graphs to see the graphs
  available. Defaults to device uptime.

Input:

- from: This is the date you would like the graph to start - See
  [http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html](http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html)
  for more information.
- to: This is the date you would like the graph to end - See
  [http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html](http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html)
  for more information.
- width: The graph width, defaults to 1075.
- height: The graph height, defaults to 300.
- output: Set how the graph should be outputted (base64, display), defaults to display.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/device_poller_perf
```

Output:

Output is an image.

### `get_port_graphs`

Get a list of ports for a particular device.

Route: `/api/v0/devices/:hostname/ports`

- hostname can be either the device hostname or id

Input:

- columns: Comma separated list of columns you want returned.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ports
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 3,
    "ports": [
        {
            "ifName": "lo"
        },
        {
            "ifName": "eth0"
        },
        {
            "ifName": "eth1"
        }
    ]
}
```

### `get_device_fdb`

Get a list of FDB entries associated with a device.

Route: `/api/v0/devices/:hostname/fdb`

- hostname can be either the device hostname or id

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/fdb
```

Output:

```json
{
    "status": "ok",
    "ports_fdb": {
        "ports_fdb_id": 10,
        "port_id": 10000,
        "mac_address": "1aaa2bbb3ccc",
        "vlan_id": 20000,
        "device_id": 1,
        "created_at": "2019-01-1 01:01:01",
        "updated_at": "2019-01-1 01:01:01"
    }
}
```

### `get_device_ip_addresses`

Get a list of IP addresses (v4 and v6) associated with a device.

Route: `/api/v0/devices/:hostname/ip`

- hostname can be either the device hostname or id

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ip
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "addresses": [
        {
          "ipv4_address_id": "290",
          "ipv4_address": "192.168.99.292",
          "ipv4_prefixlen": "30",
          "ipv4_network_id": "247",
          "port_id": "323",
          "context_name": ""
        }
    ]
}
```

### `get_port_stack`

Get a list of port mappings for a device.  This is useful for showing
physical ports that are in a virtual port-channel.

Route: `/api/v0/devices/:hostname/port_stack`

- hostname can be either the device hostname or id

Input:

- valid_mappings: Filter the result by only showing valid mappings
  ("0" values not shown).

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/port_stack?valid_mappings
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "count": 2,
  "mappings": [
    {
      "device_id": "3742",
      "port_id_high": "1001000",
      "port_id_low": "51001",
      "ifStackStatus": "active"
    },
    {
      "device_id": "3742",
      "port_id_high": "1001000",
      "port_id_low": "52001",
      "ifStackStatus": "active"
    }
  ]
}
```

### `get_components`

Get a list of components for a particular device.

Route: `/api/v0/devices/:hostname/components`

- hostname can be either the device hostname or id

Input:

- type: Filter the result by type (Equals).
- id: Filter the result by id (Equals).
- label: Filter the result by label (Contains).
- status: Filter the result by status (Equals).
- disabled: Filter the result by disabled (Equals).
- ignore: Filter the result by ignore (Equals).

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 3,
    "components": {
        "2": {
            "TestAttribute-1": "Value1",
            "TestAttribute-2": "Value2",
            "TestAttribute-3": "Value3",
            "type": "TestComponent-1",
            "label": "This is a really cool blue component",
            "status": "1",
            "ignore": "0",
            "disabled": "0"
        },
        "20": {
            "TestAttribute-1": "Value4",
            "TestAttribute-2": "Value5",
            "TestAttribute-3": "Value6",
            "type": "TestComponent-1",
            "label": "This is a really cool red component",
            "status": "1",
            "ignore": "0",
            "disabled": "0"
        },
        "27": {
            "TestAttribute-1": "Value7",
            "TestAttribute-2": "Value8",
            "TestAttribute-3": "Value9",
            "type": "TestComponent-2",
            "label": "This is a really cool yellow widget",
            "status": "1",
            "ignore": "0",
            "disabled": "0"
        }
    }
}
```

### `add_components`

Create a new component of a type on a particular device.

Route: `/api/v0/devices/:hostname/components/:type`

- hostname can be either the device hostname or id
- type is the type of component to add

Example:

```curl
curl -X POST -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components/APITEST
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 1,
    "components": {
        "4459": {
            "type": "APITEST",
            "label": "",
            "status": 1,
            "ignore": 0,
            "disabled": 0,
            "error": ""
        }
    }
}
```

### `edit_components`

Edit an existing component on a particular device.

Route: `/api/v0/devices/:hostname/components`

- hostname can be either the device hostname or id

In this example we set the label and add a new field: TestField:

```curl
curl -X PUT -d '{"4459": {"type": "APITEST","label": "This is a test label","status": 1,"ignore": 0,"disabled": 0,"error": "","TestField": "TestData"}}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 1
}
```

Just take the JSON array from add_components or edit_components, edit
as you wish and submit it back to edit components.

### `delete_components`

Delete an existing component on a particular device.

Route: `/api/v0/devices/:hostname/components/:component`

- hostname can be either the device hostname or id
- component is the component ID to be deleted.

Example:

```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components/4459
```

Output:

```json
{
    "status": "ok",
    "message": ""
}
```

### `get_port_stats_by_port_hostname`

Get information about a particular port for a device.

Route: `/api/v0/devices/:hostname/ports/:ifname`

- hostname can be either the device hostname or id
- ifname can be any of the interface names for the device which can be
  obtained using
  [`get_port_graphs`](#function-get_port_graphs). Please ensure that
  the ifname is urlencoded if it needs to be (i.e Gi0/1/0 would need to be urlencoded.

Input:

- columns: Comma separated list of columns you want returned.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ports/eth0
```

Output:

```json
{
 "status": "ok",
 "port": {
  "port_id": "2",
  "device_id": "1",
  ...
  "poll_prev": "1418412902",
  "poll_period": "300"
 }
}
```

### `get_graph_by_port_hostname`

Get a graph of a port for a particular device.

Route: `/api/v0/devices/:hostname/ports/:ifname/:type`

- hostname can be either the device hostname or id
- ifname can be any of the interface names for the device which can be
  obtained using
  [`get_port_graphs`](#function-get_port_graphs). Please ensure that
  the ifname is urlencoded if it needs to be (i.e Gi0/1/0 would need
  to be urlencoded.
- type is the port type you want the graph for, you can request a list
  of ports for a device with [`get_port_graphs`](#function-get_port graphs).

Input:

- from: This is the date you would like the graph to start - See
  [http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html](http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html)
  for more information.
- to: This is the date you would like the graph to end - See
  [http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html](http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html)
  for more information.
- width: The graph width, defaults to 1075.
- height: The graph height, defaults to 300.
- ifDescr: If this is set to true then we will use ifDescr to lookup
  the port instead of ifName. Pass the ifDescr value you want to
  search as you would ifName.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ports/eth0/port_bits
```

Output:

Output is an image.

### `list_locations`

Return a list of locations.

Route: `/api/v0/resources/locations`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/locations
```

Output:

```json
{
    "status": "ok",
    "locations": [
        {
            "id": "1",
            "location": "Example location, Example city, Example Country",
            "lat": "-18.911436",
            "lng": "47.517446",
            "timestamp": "2017-04-01 02:40:05"
        },
        ...
    ],
    "count": 100
}
```

### `list_sensors`

Get a list of all Sensors.

Route: `/api/v0/resources/sensors`

Input:

  -

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/sensors
```

Output:

```json
{
    "status": "ok",
    "sensors": [
        {
            "sensor_id": 218810,
            "sensor_deleted": 0,
            "sensor_class": "dbm",
            "device_id": 136,
            "poller_type": "snmp",
            "sensor_oid": ".1.3.6.1.4.1.2636.3.60.1.1.1.1.7.919",
            "sensor_index": "tx-919",
            "sensor_type": "junos",
            "sensor_descr": "xe-2/1/4 Tx Power",
            "group": null,
            "sensor_divisor": 100,
            "sensor_multiplier": 1,
            "sensor_current": -1.81,
            "sensor_limit": 2,
            "sensor_limit_warn": 0.5,
            "sensor_limit_low": -9.7,
            "sensor_limit_low_warn": -8.21,
            "sensor_alert": 1,
            "sensor_custom": "No",
            "entPhysicalIndex": "919",
            "entPhysicalIndex_measured": "ports",
            "lastupdate": "2019-02-18 02:47:09",
            "sensor_prev": -1.77,
            "user_func": null
        },
        ...
    ],
    "count": 100
}
```

### `list_devices`

Return a list of devices.

Route: `/api/v0/devices`

Input:

- order: How to order the output, default is by hostname. Can be
  prepended by DESC or ASC to change the order.
- type: can be one of the following to filter or search by:
  - all: All devices
  - active: Only not ignored and not disabled devices
  - ignored: Only ignored devices
  - up: Only devices that are up
  - down: Only devices that are down
  - disabled: Disabled devices
  - os: search by os type
  - mac: search by mac address
  - ipv4: search by IPv4 address
  - ipv6: search by IPv6 address (compressed or uncompressed)
  - location: search by location
  - hostname: search by hostname
- query: If searching by, then this will be used as the input.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices?order=hostname%20DESC&type=down
```

Output:

```json
{
 "status": "ok",
 "count": 1,
 "devices": [
  {
   "device_id": "1",
   "hostname": "localhost",
   ...
   "serial": null,
   "icon": null
  }
 ]
}
```

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices?type=mac&query=00000c9ff013
```

Output:

```json
{
 "status": "ok",
 "count": 1,
 "devices": [
  {
   "device_id": "1",
   "hostname": "localhost",
   ...
   "serial": null,
   "icon": null
  }
 ]
}
```

### `add_device`

Add a new device.

Route: `/api/v0/devices`

Input (JSON):

- hostname: device hostname
- overwrite_ip: alternate polling IP. Will be use instead of hostname (optional)
- port: SNMP port (defaults to port defined in config).
- transport: SNMP protocol (defaults to transport defined in config).
- version: SNMP version to use, v1, v2c or v3. Defaults to v2c.
- poller_group: This is the poller_group id used for distributed
  poller setup. Defaults to 0.
- force_add: Force the device to be added regardless of it being able
  to respond to snmp or icmp.

For SNMP v1 or v2c

- community: Required for SNMP v1 or v2c.

For SNMP v3

- authlevel: SNMP authlevel (noAuthNoPriv, authNoPriv, authPriv).
- authname: SNMP Auth username
- authpass: SNMP Auth password
- authalgo: SNMP Auth algorithm (MD5, SHA)
- cryptopass: SNMP Crypto Password
- cryptoalgo: SNMP Crypto algorithm (AES, DES)

For ICMP only

- snmp_disable: Boolean, set to true for ICMP only.
- os: OS short name for the device (defaults to ping).
- hardware: Device hardware.

Example:

```curl
curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices
```

Output:

```json
{
    "status": "ok",
    "message": "Device localhost.localdomain (57) has been added successfully"
}
```

### `list_oxidized`

List devices for use with Oxidized. If you have group support enabled
then a group will also be returned based on your config.

> LibreNMS will automatically map the OS to the Oxidized model name if
> they don't match.

Route: `/api/v0/oxidized(/:hostname)`

Input (JSON):

  -

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/oxidized
```

Output:

```json
[
    {
        "hostname": "localhost",
        "os": "linux"
    },
    {
        "hostname": "otherserver",
        "os": "linux"
    }
]
```

### `update_device_field`

Update devices field in the database.

Route: `/api/v0/devices/:hostname`

- hostname can be either the device hostname or id

Input (JSON):

- field: The column name within the database (can be an array of fields)
- data: The data to update the column with (can be an array of data))

Examples:

```curl
curl -X PATCH -d '{"field": "notes", "data": "This server should be kept online"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:

```json
[
    {
        "status": "ok",
        "message": "Device notes has been updated"
    }
]
```

```curl
curl -X PATCH -d '{"field": ["notes","purpose"], "data": ["This server should be kept online", "For serving web traffic"]}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:

```json
[
    {
        "status": "ok",
        "message": "Device fields have been updated"
    }
]
```

### `rename_device`

Rename device.

Route: `/api/v0/devices/:hostname/rename/:new_hostname`

- hostname can be either the device hostname or id

Input:

  -

Examples:

```curl
curl -X PATCH  -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/rename/localhost2
```

Output:

```json
[
    {
        "status": "ok",
        "message": "Device has been renamed"
    }
]
```

### `get_device_groups`

List the device groups that a device is matched on.

Route: `/api/v0/devices/:hostname/groups`

- hostname can be either the device hostname or id

Input (JSON):

  -

Examples:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/groups
```

Output:

```json
[
    {
        "status": "ok",
        "message": "Found 1 device groups",
        "count": 1,
        "groups": [
        {
            "id": "1",
            "name": "Testing",
            "desc": "Testing",
            "pattern": "%devices.status = \"1\" &&"
        }
        ]
    }
]
```


### `search_oxidized`

search all oxidized device configs for a string.

Route: `api/v0/oxidized/config/search/:searchstring`

  - searchstring is the specific string you would like to search for.

Input:

-

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/oxidized/config/search/vlan10
```

Output:
```json
{
    "status": "ok",
    "nodes": [
        {
            "node": "asr9k.librenms.org",
            "full_name": "cisco\/ASR9K.Librenms.org"
        },
        {
            "node": "ios.Librenms.org",
            "full_name": "cisco\/ios.Librenms.org"
        }
    ],
    "count": 2
}
```

### `get_oxidized_config`

Returns a specific device's config from oxidized.

Route: `api/v0/oxidized/config/:device_name`

  - device_name is the full dns name of the device used when adding the device to librenms.

Input:

-

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/oxidized/config/router.corp.com
```

Output:
```json
{
    "status": "ok",
    "config": "DEVICE CONFIG HERE"
}
```

### `add_parents_to_host`

Add one or more parents to a host.

Route: `/api/v0/devices/:device/parents`

Input (JSON):

- parent_ids: one or more parent ids or hostnames

Example:
```curl
curl -X POST -d '{"parent_ids":"15,16,17"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/1/parents
```

Output:
```json
{
    "status": "ok",
    "message": "Device dependencies have been saved"
}
```

### `delete_parents_from_host`

Deletes some or all the parents from a host.

Route: `/api/v0/devices/:device/parents`

Input (JSON):

- parent_ids: One or more parent ids or hostnames, if not specified deletes all parents from host.

Example:
```curl
curl -X DELETE -d '{"parent_ids":"15,16,17"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/1/parents
```

Output:
```json
{
    "status": "ok",
    "message": "All device dependencies have been removed"
}
```
