source: API/index.md
[TOC]
        
Describes the API structure.

# Structure

## Versioning

Versioning an API is a minefield which saw us looking at numerous options on how to do this. Paul wrote an excellent blog post which touches on this: https://blog.librenms.org/2014/09/restful-apis/

We have currently settled on using versioning within the API end point itself `/api/v0`. As the API itself is new and still in active development we also decided that v0 would be the best starting point to indicate it's in development.

## Tokens

To access any of the token end points you will be required to authenticate using a token. Tokens can be created directly from within the LibreNMS web interface by going to `/api-access/`.

- Click on 'Create API access token'.
- Select the user you would like to generate the token for.
- Enter an optional description.
- Click Create API Token.

## Endpoints

Whilst this documentation will describe and show examples of the end points, we've designed the API so you should be able to traverse through it without know any of the available API routes.

You can do this by first calling `/api/v0`:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0
```

Output:
```json
{
 "list_bgp": "https://librenms.org/api/v0/bgp",
  ...
 "edit_rule": "https://librenms.org/api/v0/rules"
}
```

## Input

Input to the API is done in three different ways, sometimes a combination two or three of these.

  - Passing parameters via the api route. For example when obtaining a devices details you will pass the hostname of the device in the route: `/api/v0/devices/:hostname`.
  - Passing parameters via the query string. For example you can list all devices on your install but limit the output to devices that are currently down: `/api/v0/devices?type=down`
  - Passing data in via JSON, this will mainly be used when adding or updating information via the API, for instance adding a new device:
```curl
curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}'-H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices
```

## Output

Output from the API currently is via two output types.

  - JSON Most API responses will output json. As show in the example for calling the API endpoint.
  - PNG This is for when the request is for an image such as a graph for a switch port.

# Endpoints

## Devices

### Function: `del_device`

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

### Function: `get_device`

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

### Function: `get_graphs`

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
    "err-msg": "",
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

### Function: `list_available_health_graphs`
This function allows to do three things:

  - Get a list of overall health graphs available.
  - Get a list of health graphs based on provided class.
  - Get the health sensors information based on ID.

Route: `/api/v0/devices/:hostname/health(/:type)(/:sensor_id)`

  - hostname can be either the device hostname or id
  - type (optional) is health type / sensor class
  - sensor_id (optional) is the sensor id to retreive specific information.

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
    "err-msg": "",
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
    "err-msg": "",
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
    "err-msg": "",
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

### Function: `get_health_graph`

Get a particular health class graph for a device, if you provide a sensor_id as well then a single sensor graph
will be provided. If no sensor_id value is provided then you will be sent a stacked sensor graph.

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



### Function: `get_graph_generic_by_hostname`

Get a specific graph for a device, this does not include ports.

Route: `/api/v0/devices/:hostname/:type`

  - hostname can be either the device hostname or id
  - type is the type of graph you want, use [`get_graphs`](#function-get_graphs to see the graphs available. Defaults to device_uptime.

Input:

  - from: This is the date you would like the graph to start - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - to: This is the date you would like the graph to end - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - width: The graph width, defaults to 1075.
  - height: The graph height, defaults to 300.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/device_poller_perf
```

Output:

Output is an image.

### Function: `get_port_graphs`

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
    "err-msg": "",
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
### Function: `get_device_ip_addresses`

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
    "err-msg": "",
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

### Function: `get_port_stack`

Get a list of port mappings for a device.  This is useful for showing physical ports that are in a virtual port-channel.

Route: `/api/v0/devices/:hostname/port_stack`

  - hostname can be either the device hostname or id

Input:

  - valid_mappings: Filter the result by only showing valid mappings ("0" values not shown).

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/port_stack?valid_mappings
```

Output:

```json
{
  "status": "ok",
  "err-msg": "",
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

### Function: `get_components`

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
    "err-msg": "",
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

### Function: `add_components`

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
    "err-msg": "",
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

### Function: `edit_components`

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
    "err-msg": "",
    "count": 1
}
```

Just take the JSON array from add_components or edit_components, edit as you wish and submit it back to edit_components.

### Function: `delete_components`

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
    "err-msg": ""
}
```

### Function: `get_port_stats_by_port_hostname`

Get information about a particular port for a device.

Route: `/api/v0/devices/:hostname/ports/:ifname`

  - hostname can be either the device hostname or id
  - ifname can be any of the interface names for the device which can be obtained using [`get_port_graphs`](#function-get_port_graphs). Please ensure that the ifname is urlencoded if it needs to be (i.e Gi0/1/0 would need to be urlencoded.

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

### Function: `get_graph_by_port_hostname`

Get a graph of a port for a particular device.

Route: `/api/v0/devices/:hostname/ports/:ifname/:type`

  - hostname can be either the device hostname or id
  - ifname can be any of the interface names for the device which can be obtained using [`get_port_graphs`](#function-get_port_graphs). Please ensure that the ifname is urlencoded if it needs to be (i.e Gi0/1/0 would need to be urlencoded.
  - type is the port type you want the graph for, you can request a list of ports for a device with [`get_port_graphs`](#function-get_port_graphs).

Input:

  - from: This is the date you would like the graph to start - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - to: This is the date you would like the graph to end - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - width: The graph width, defaults to 1075.
  - height: The graph height, defaults to 300.
  - ifDescr: If this is set to true then we will use ifDescr to lookup the port instead of ifName. Pass the ifDescr value you want to search as you would ifName.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ports/eth0/port_bits
```

Output:

Output is an image.

### Function: `list_devices`

Return a list of devices.

Route: `/api/v0/devices`

Input:

  - order: How to order the output, default is by hostname. Can be prepended by DESC or ASC to change the order.
  - type: can be one of the following to filter or search by:
    - all: All devices
    - ignored: Only ignored devices
    - up: Only devices that are up
    - down: Only devices that are down
    - disabled: Disabled devices
    - os: search by os type
    - mac: search by mac address
    - ipv4: search by IPv4 address
    - ipv6: search by IPv6 address (compressed or uncompressed)
    - location: search by location
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

### Function: `add_device`

Add a new device.

Route: `/api/v0/devices`

Input (JSON):

  - hostname: device hostname
  - port: SNMP port (defaults to port defined in config).
  - transport: SNMP protocol (defaults to transport defined in config).
  - version: SNMP version to use, v1, v2c or v3. Defaults to v2c.
  - poller_group: This is the poller_group id used for distributed poller setup. Defaults to 0.
  - force_add: Force the device to be added regardless of it being able to respond to snmp or icmp.

For SNMP v1 or v2c

  - community: Required for SNMP v1 or v2c.

For SNMP v3

  - authlevel: SNMP authlevel (NoAuthNoPriv, AuthNoPriv, AuthPriv).
  - authname: SNMP Auth username
  - authpass: SNMP Auth password
  - authalgo: SNMP Auth algorithm (MD5, SHA)
  - cryptopass: SNMP Crypto Password
  - cryptoalgo: SNMP Crypto algorithm (AES, DES)

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

### Function: `list_oxidized`

List devices for use with Oxidized. If you have group support enabled then a group will also be returned based on your config.

Route: `/api/v0/oxidized`

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

### Function: `update_device_field`

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

### Function `get_device_groups`

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

## Device Groups

### Function `get_devicegroups`

List all device groups.

Route: `/api/v0/devicegroups`

Input (JSON):

  -

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devicegroups
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

## Ports

### Function `get_all_ports`

Get info for all ports on all devices.
Strongly recommend that you use the `columns` parameter to avoid pulling too much data.

Route: `/api/v0/ports`

  -

Input:

  - columns: Comma separated list of columns you want returned.


Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/ports?columns=ifName%2Cport_id
```

Output:

```json
{
  "status": "ok",
  "err-msg": "",
  "ports": [
        {
          "ifName": "Gi0/0/0",
          "port_id": "1"
        },
        {
          "ifName": "Gi0/0/1",
          "port_id": "2"
        },
        ...
        {
          "ifName": "Vlan 3615",
          "port_id": "5488"
        }
    ]
}
```

### Function `get_port_info`

Get all info for a particular port.

Route: `/api/v0/ports/:portid`

  - portid must be an integer

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/ports/323
```

Output:

```json
{
  "status": "ok",
  "err-msg": "",
  "port": [
    {
      "port_id": "323",
      "device_id": "55",
      "port_descr_type": null,
      "port_descr_descr": null,
      "port_descr_circuit": null,
      "port_descr_speed": null,
      "port_descr_notes": null,
      "ifDescr": "GigabitEthernet0/0/0",
      "ifName": "Gi0/0/0",
      "portName": null,
      "ifIndex": "1",
      "ifSpeed": "1000000000",
      "ifConnectorPresent": "true",
      "ifPromiscuousMode": "false",
      "ifHighSpeed": "1000",
      "ifOperStatus": "up",
      "ifOperStatus_prev": null,
      "ifAdminStatus": "up",
      "ifAdminStatus_prev": null,
      "ifDuplex": "fullDuplex",
      "ifMtu": "1560",
      "ifType": "ethernetCsmacd",
      "ifAlias": "ASR Interconnect Trunk",
      "ifPhysAddress": "84bf20853e00",
      "ifHardType": null,
      "ifLastChange": "42407358",
      "ifVlan": "",
      "ifTrunk": "",
      "ifVrf": "0",
      "counter_in": null,
      "counter_out": null,
      "ignore": "0",
      "disabled": "0",
      "detailed": "0",
      "deleted": "0",
      "pagpOperationMode": null,
      "pagpPortState": null,
      "pagpPartnerDeviceId": null,
      "pagpPartnerLearnMethod": null,
      "pagpPartnerIfIndex": null,
      "pagpPartnerGroupIfIndex": null,
      "pagpPartnerDeviceName": null,
      "pagpEthcOperationMode": null,
      "pagpDeviceId": null,
      "pagpGroupIfIndex": null,
      "ifInUcastPkts": "128518576",
      "ifInUcastPkts_prev": "128517284",
      "ifInUcastPkts_delta": "1292",
      "ifInUcastPkts_rate": "4",
      "ifOutUcastPkts": "128510560",
      "ifOutUcastPkts_prev": "128509268",
      "ifOutUcastPkts_delta": "1292",
      "ifOutUcastPkts_rate": "4",
      "ifInErrors": "0",
      "ifInErrors_prev": "0",
      "ifInErrors_delta": "0",
      "ifInErrors_rate": "0",
      "ifOutErrors": "0",
      "ifOutErrors_prev": "0",
      "ifOutErrors_delta": "0",
      "ifOutErrors_rate": "0",
      "ifInOctets": "12827393730",
      "ifInOctets_prev": "12827276736",
      "ifInOctets_delta": "116994",
      "ifInOctets_rate": "387",
      "ifOutOctets": "14957481766",
      "ifOutOctets_prev": "14957301765",
      "ifOutOctets_delta": "180001",
      "ifOutOctets_rate": "596",
      "poll_time": "1483779150",
      "poll_prev": "1483778848",
      "poll_period": "302"
    }
  ]
}
```

### Function `get_port_info`

Get all IP info (v4 and v6) for a given port id.

Route: `/api/v0/ports/:portid/ip`

  - portid must be an integer

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/ports/323/ip
```

Output:

```json
{
  "status": "ok",
  "err-msg": "",
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

### Function `get_devices_by_group`

List all devices matching the group provided.

Route: `/api/v0/devicegroups/:name`

  - name Is the name of the device group which can be obtained using [`get_devicegroups`](#function-get_devicegroups). Please ensure that the name is urlencoded if it needs to be (i.e Linux Servers would need to be urlencoded.

Input (JSON):

  - full: set to any value to return all data for the devices in a given group

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devicegroups/LinuxServers
```

Output:
```json
[
     {
         "status": "ok",
         "message": "Found 3 in group LinuxServers",
         "count": 3,
         "devices": [
            {
                "device_id": "15"
            },
            {
                "device_id": "18"
            },
            {
                "device_id": "20"
            }
         ]
     }
]
```

## Port Groups

### Function: `get_graph_by_portgroup`

Get the graph based on the group type.

Route: `/api/v0/devices/portgroups/:group`

  - group is the type of port group graph you want, I.e Transit, Peering, etc. You can specify multiple types comma separated.

Input:

  - from: This is the date you would like the graph to start - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - to: This is the date you would like the graph to end - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - width: The graph width, defaults to 1075.
  - height: The graph height, defaults to 300.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/portgroups/transit,peering
```

Output:

Output is an image.

### Function: `get_graph_by_portgroup_multiport_bits`

Get the graph based on the multiple port id separated by commas `,`.

Route: `/api/v0/devices/portgroups/multiport/bits/:id`

  - id is a comma separated list of port ids you want, I.e 1,2,3,4, etc. You can specify multiple IDs comma separated.

Input:

  - from: This is the date you would like the graph to start - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - to: This is the date you would like the graph to end - See http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html for more information.
  - width: The graph width, defaults to 1075.
  - height: The graph height, defaults to 300.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/portgroups/multiport/bits/1,2,3
```

Output:

Output is an image.


## Routing

### Function: `list_bgp`

List the current BGP sessions.

Route: `/api/v0/bgp`

Input:

  - hostname = Either the devices hostname or id.
**OR**
  - asn = The ASN you would like to filter by

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 0,
 "bgp_sessions": [

 ]
}
```

### Function: `list_ipsec`

List the current IPSec tunnels which are active.

Route: `/api/v0/routing/ipsec/data/:hostname`

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/ipsec/data/localhost
```

Output:
```json
{
    "status": "ok",
    "err-msg": "",
    "count": 0,
    "ipsec": [
        "tunnel_id": "1",
        "device_id": "1",
        "peer_port": "0",
        "peer_addr": "127.0.0.1",
        "local_addr": "127.0.0.2",
        "local_port": "0",
        "tunnel_name": "",
        "tunnel_status": "active"
    ]
}
```
> Please note, this will only show active VPN sessions not all configured.

## Switching

### Function: `get_vlans`

Get a list of all VLANs for a given device.

Route: `/api/v0/devices/:hostname/vlans`

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/vlans
```

Output:
```json
{
    "status": "ok",
    "count": 0,
    "vlans": [
    {
   "vlan_vlan": "1",
   "vlan_domain": "1",
   "vlan_name": "default",
   "vlan_type": "ethernet",
   "vlan_mtu": null
    }
  ]
}
```

## Alerts

### Function: `get_alert`

Get details of an alert

Route: `/api/v0/alerts/:id`

  - id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#function-list_alerts).

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/1
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 7,
 "alerts": [
  {
   "hostname": "localhost",
   "id": "1",
   "device_id": "1",
   "rule_id": "1",
   "state": "1",
   "alerted": "1",
   "open": "1",
   "timestamp": "2014-12-11 14:40:02"
  },
}
```

### Function: `ack_alert`

Acknowledge an alert

Route: `/api/v0/alerts/:id`

  - id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#function-list_alerts).

Input:

  -

Example:
```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/1
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "message": "Alert has been acknowledged"
}
```

### Function: `unmute_alert`

Unmute an alert

Route: `/api/v0/alerts/unmute/:id`

  - id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#function-list_alerts).

Input:

  -

Example:
```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/unmute/1
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "message": "Alert has been unmuted"
}
```


### Function: `list_alerts`

List all alerts

Route: `/api/v0/alerts`

Input:

  - state: Filter the alerts by state, 0 = ok, 1 = alert, 2 = ack

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts?state=1
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 1,
 "alerts": [
  {
   "id": "1",
   "device_id": "1",
   "rule_id": "1",
   "state": "1",
   "alerted": "1",
   "open": "1",
   "timestamp": "2014-12-11 14:40:02"
  }
}
```

## Rules

### Function: `get_alert_rule`

Get the alert rule details.

Route: `/api/v0/rules/:id`

  - id is the rule id.

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules/1
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 1,
 "rules": [
  {
   "id": "1",
   "device_id": "1",
   "rule": "%devices.os != \"Juniper\"",
   "severity": "warning",
   "extra": "{\"mute\":true,\"count\":\"15\",\"delay\":null,\"invert\":false}",
   "disabled": "0",
   "name": "A test rule"
  }
 ]
}
```

### Function: `delete_rule`

Delete an alert rule by id

Route: `/api/v0/rules/:id`

  - id is the rule id.

Input:

  -

Example:
```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules/1
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "message": "Alert rule has been removed"
}
```

### Function: `list_alert_rules`

List the alert rules.

Route: `/api/v0/rules`

  -

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 1,
 "rules": [
  {
   "id": "1",
   "device_id": "-1",
   "rule": "%devices.os != \"Juniper\"",
   "severity": "critical",
   "extra": "{\"mute\":false,\"count\":\"15\",\"delay\":\"300\",\"invert\":false}",
   "disabled": "0",
   "name": "A test rule"
  },
}
```

### Function: `add_rule`

Add a new alert rule.

Route: `/api/v0/rules`

  -

Input (JSON):

  - device_id: This is either the device id or -1 for a global rule
  - rule: The rule which should be in the format %entity $condition $value (i.e %devices.status != 0 for devices marked as down).
  - severity: The severity level the alert will be raised against, Ok, Warning, Critical.
  - disabled: Whether the rule will be disabled or not, 0 = enabled, 1 = disabled
  - count: This is how many polling runs before an alert will trigger and the frequency.
  - delay: Delay is when to start alerting and how frequently. The value is stored in seconds but you can specify minutes, hours or days by doing 5 m, 5 h, 5 d for each one.
  - mute: If mute is enabled then an alert will never be sent but will show up in the Web UI (true or false).
  - invert: This would invert the rules check.
  - name: This is the name of the rule and is mandatory.

Example:
```curl
curl -X POST -d '{"device_id":"-1", "rule":"%devices.os != \"Cisco\"","severity": "critical","count":15,"delay":"5 m","mute":false}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:
```json
rules
{
 "status": "ok",
 "err-msg": ""
}
```

### Function: `edit_rule`

Edit an existing alert rule

Route: `/api/v0/rules`

  -

Input (JSON):

  - rule_id: You must specify the rule_id to edit an existing rule, if this is absent then a new rule will be created.
  - device_id: This is either the device id or -1 for a global rule
  - rule: The rule which should be in the format %entity $condition $value (i.e %devices.status != 0 for devices marked as down).
  - severity: The severity level the alert will be raised against, Ok, Warning, Critical.
  - disabled: Whether the rule will be disabled or not, 0 = enabled, 1 = disabled
  - count: This is how many polling runs before an alert will trigger and the frequency.
  - delay: Delay is when to start alerting and how frequently. The value is stored in seconds but you can specify minutes, hours or days by doing 5 m, 5 h, 5 d for each one.
  - mute: If mute is enabled then an alert will never be sent but will show up in the Web UI (true or false).
  - invert: This would invert the rules check.
  - name: This is the name of the rule and is mandatory.

Example:
```curl
curl -X PUT -d '{"rule_id":1,"device_id":"-1", "rule":"%devices.os != \"Cisco\"","severity": "critical","count":15,"delay":"5 m","mute":false}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:
```json
rules
{
 "status": "ok",
 "err-msg": ""
}
```

## Inventory

### Function: `get_inventory`

Retrieve the inventory for a device. If you call this without any parameters then you will only get part of the inventory. This is because a lot of devices nest each component, for instance you may initially have the chassis, within this the ports - 1 being an sfp cage, then the sfp itself. The way this API call is designed is to enable a recursive lookup. The first call will retrieve the root entry, included within this response will be entPhysicalIndex, you can then call for entPhysicalContainedIn which will then return the next layer of results.

Route: `/api/v0/inventory/:hostname`

  - hostname can be either the device hostname or the device id

Input:

  - entPhysicalClass: This is used to restrict the class of the inventory, for example you can specify chassis to only return items in the inventory that are labelled as chassis.
  - entPhysicalContainedIn: This is used to retrieve items within the inventory assigned to a previous component, for example specifying the chassis (entPhysicalIndex) will retrieve all items where the chassis is the parent.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/inventory/localhost?entPhysicalContainedIn=65536
```

Output:
```json
{
    "status": "ok",
    "err-msg": "",
    "count": 1,
    "inventory": [
        {
            "entPhysical_id": "2",
            "device_id": "32",
            "entPhysicalIndex": "262145",
            "entPhysicalDescr": "Linux 3.3.5 ehci_hcd RB400 EHCI",
            "entPhysicalClass": "unknown",
            "entPhysicalName": "1:1",
            "entPhysicalHardwareRev": "",
            "entPhysicalFirmwareRev": "",
            "entPhysicalSoftwareRev": "",
            "entPhysicalAlias": "",
            "entPhysicalAssetID": "",
            "entPhysicalIsFRU": "false",
            "entPhysicalModelName": "0x0002",
            "entPhysicalVendorType": "zeroDotZero",
            "entPhysicalSerialNum": "rb400_usb",
            "entPhysicalContainedIn": "65536",
            "entPhysicalParentRelPos": "-1",
            "entPhysicalMfgName": "0x1d6b",
            "ifIndex": "0"
        }
    ]
}
```

## Bills

### Function: `list_bills`

Retrieve the list of bills currently in the system.

Route: `/api/v0/bills`

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 1,
 "bills": [
  {
   "bill_id": "1",
   "bill_name": "Router bills",
   "bill_type": "cdr",
   "bill_cdr": "10000000",
   "bill_day": "1",
   "bill_quota": "0",
   "rate_95th_in": "0",
   "rate_95th_out": "0",
   "rate_95th": "0",
   "dir_95th": "in",
   "total_data": "0",
   "total_data_in": "0",
   "total_data_out": "0",
   "rate_average_in": "0",
   "rate_average_out": "0",
   "rate_average": "0",
   "bill_last_calc": "2015-07-02 17:01:26",
   "bill_custid": "Router",
   "bill_ref": "Router",
   "bill_notes": "Bill me",
   "bill_autoadded": "0",
   "ports_total": "0",
   "allowed": "10Mbps",
   "used": "0bps",
   "percent": 0,
   "overuse": "-",
   "ports": [
       {
           "device_id": "168",
           "port_id": "35146",
           "ifName": "eth0"
       }
   ]
  }
 ]
}
```

### Function: `get_bill`

Retrieve a specific bill

Route: `/api/v0/bills/:id`
       `/api/v0/bills?ref=:ref`
       `/api/v0/bills?custid=:custid`

  - id is the specific bill id
  - ref is the billing reference
  - custid is the customer reference

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills?ref=:customerref
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills?custid=:custid
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
 "count": 1,
 "bills": [
  {
   "bill_id": "1",
   "bill_name": "Router bills",
   "bill_type": "cdr",
   "bill_cdr": "10000000",
   "bill_day": "1",
   "bill_quota": "0",
   "rate_95th_in": "0",
   "rate_95th_out": "0",
   "rate_95th": "0",
   "dir_95th": "in",
   "total_data": "0",
   "total_data_in": "0",
   "total_data_out": "0",
   "rate_average_in": "0",
   "rate_average_out": "0",
   "rate_average": "0",
   "bill_last_calc": "2015-07-02 17:01:26",
   "bill_custid": "Router",
   "bill_ref": "Router",
   "bill_notes": "Bill me",
   "bill_autoadded": "0",
   "ports_total": "0",
   "allowed": "10Mbps",
   "used": "0bps",
   "percent": 0,
   "overuse": "-",
   "ports": [
       {
           "device_id": "168",
           "port_id": "35146",
           "ifName": "eth0"
       }
   ]
  }
 ]
}
```

### Function: `list_arp`

Retrieve a specific ARP entry or all ARP enties for a device

Route: `/api/v0/resources/ip/arp/:ip`

  - ip is the specific IP you would like to query, if this is all then you need to pass ?device=_hostname_ (or device id)
  - This may also be a cidr network, for example 192.168.1.0/24

Input:

  - device if you specify all for the IP then you need to populate this with the hostname or id of the device.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/1.1.1.1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/192.168.1.0/24
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/all?device=localhost
```

Output:
```json
{
    "status": "ok",
    "err-msg": "",
    "count": 1,
    "arp": [
        {
            "port_id": "229",
            "mac_address": "da160e5c2002",
            "ipv4_address": "1.1.1.1",
            "context_name": ""
        }
    ]
}
```

## Services

### Function: `list_services`

Retrieve all services

Route: `/api/v0/services`

Input:

  - state: only which have a certain state (valid options are 0=Ok, 1=Warning, 2=Critical).
  - type: service type, used sql LIKE to find services, so for tcp, use type=tcp for http use type=http

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services?state=2
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services?state=0&type=tcp
```

Output:
```json
{
    "status": "ok",
    "err-msg": "",
    "count": 1,
    "services": [
        [
            {
                "service_id": "13",
                "device_id": "1",
                "service_ip": "demo1.yourdomian.net",
                "service_type": "ntp_peer",
                "service_desc": "NTP",
                "service_param": "-H 192.168.1.10",
                "service_ignore": "0",
                "service_status": "0",
                "service_changed": "1470962470",
                "service_message": "NTP OK: Offset -0.000717 secs",
                "service_disabled": "0",
                "service_ds": "{\"offset\":\"s\"}"
            }
        ],
        [
            {
                "service_id": "2",
                "device_id": "2",
                "service_ip": "demo2.yourdomian.net",
                "service_type": "esxi_hardware.py",
                "service_desc": "vmware hardware",
                "service_param": "-H 192.168.1.11 -U USER -P PASS -p",
                "service_ignore": "0",
                "service_status": "0",
                "service_changed": "1471702206",
                "service_message": "OK - Server: Supermicro X9SCL/X9SCM s/n: 0123456789 System BIOS: 2.2 2015-02-20",
                "service_disabled": "0",
                "service_ds": "{\"P2Vol_0_Processor_1_Vcore\":\"\",\"P2Vol_1_System_Board_1_-12V\":\"\",\"P2Vol_2_System_Board_1_12V\":\"\",\"P2Vol_3_System_Board_1_3.3VCC\":\"\",\"P2Vol_4_System_Board_1_5VCC\":\"\",\"P2Vol_5_System_Board_1_AVCC\":\"\",\"P2Vol_6_System_Board_1_VBAT\":\"\",\"P2Vol_7_System_Board_1_"
            }
        ]
    ]
}
```
### Function: `get_service_for_host`

Retrieve services for device

Route: `/api/v0/services/:hostname`

  - id or hostname is the specific device

Input:

  - state: only which have a certain state (valid options are 0=Ok, 1=Warning, 2=Critical).
  - type: service type, used sql LIKE to find services, so for tcp, use type=tcp for http use type=http

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services/:hostname
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services/:hostname?state=2
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services/:hostname?state=0&type=tcp
```

Output:
```json
{
    "status": "ok",
    "err-msg": "",
    "count": 1,
    "services": [
        [
            {
                "service_id": "2",
                "device_id": "2",
                "service_ip": "demo2.yourdomian.net",
                "service_type": "esxi_hardware.py",
                "service_desc": "vmware hardware",
                "service_param": "-H 192.168.1.11 -U USER -P PASS -p",
                "service_ignore": "0",
                "service_status": "0",
                "service_changed": "1471702206",
                "service_message": "OK - Server: Supermicro X9SCL/X9SCM s/n: 0123456789 System BIOS: 2.2 2015-02-20",
                "service_disabled": "0",
                "service_ds": "{\"P2Vol_0_Processor_1_Vcore\":\"\",\"P2Vol_1_System_Board_1_-12V\":\"\",\"P2Vol_2_System_Board_1_12V\":\"\",\"P2Vol_3_System_Board_1_3.3VCC\":\"\",\"P2Vol_4_System_Board_1_5VCC\":\"\",\"P2Vol_5_System_Board_1_AVCC\":\"\",\"P2Vol_6_System_Board_1_VBAT\":\"\",\"P2Vol_7_System_Board_1_"
            }
        ]
    ]
}
```

## Logs
All the `list_*logs` calls are aliased to `list_logs`.

Retrieve all logs or logs for a specific device.

  - id or hostname is the specific device

Input:

  - start: The page number to request.
  - limit: The limit of results to be returned.
  - from: The date and time to search from.
  - to: The data and time to search to.

### Function: `list_eventlog`
Route: `/api/v0/logs/eventlog/:hostname`

### Function: `list_syslog`
Route: `/api/v0/logs/syslog/:hostname`

### Function: `list_alertlog`
Route: `/api/v0/logs/alertlog/:hostname`

### Function: `list_authlog`
Route: `/api/v0/logs/authlog/:hostname`



Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/logs/eventlog/:hostname
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/logs/syslog/:hostname?limit=20
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/logs/eventlog/:hostname?limit=20&start=5&from=2017-07-22%2023:00:00
```

Output:
```json
{
    "status": "ok",
    "err-msg": "",
    "count": 5,
    "total": "15",
    "logs": [
        {
            "hostname": "localhost",
            "sysName": "web01.1.novalocal",
            "event_id": "10050349",
            "host": "279",
            "device_id": "279",
            "datetime": "2017-07-22 19:57:47",
            "message": "ifAlias:  ->  <pptp-something-something-tunnel-something>",
            "type": "interface",
            "reference": "NULL",
            "username": "",
            "severity": "3"
        },
        ....
        {
            "hostname": "localhost",
            "sysName": "web01.1.novalocal",
            "event_id": "10050353",
            "host": "279",
            "device_id": "279",
            "datetime": "2017-07-22 19:57:47",
            "message": "ifHighSpeed:  ->  0",
            "type": "interface",
            "reference": "NULL",
            "username": "",
            "severity": "3"
        }
    ]
}
```