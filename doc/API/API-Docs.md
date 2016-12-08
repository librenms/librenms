source: API/API-Docs.md
- API
<a name="top"></a>
- [`Structure`](#api-structure)
    - [`Versioning`](#api-versioning)
    - [`token`](#api-tokens)
    - [`end-points`](#api-end_points)
    - [`input`](#api-input)
    - [`output`](#api-output)
- [`endpoints`](#api-endpoints)
    - [`devices`](#api-devices)
        - [`del_device`](#api-route-2)
        - [`get_device`](#api-route-3)
        - [`get_graphs`](#api-route-5)
        - [`get_graph_generic_by_hostname`](#api-route-6)
        - [`get_port_graphs`](#api-route-7)
        - [`get_port_stack`](#api-route-29)
        - [`get_components`](#api-route-25)
        - [`add_components`](#api-route-26)
        - [`edit_components`](#api-route-27)
        - [`delete_components`](#api-route-28)
        - [`get_port_stats_by_port_hostname`](#api-route-8)
        - [`get_graph_by_port_hostname`](#api-route-9)
        - [`list_devices`](#api-route-10)
        - [`add_device`](#api-route-11)
        - [`list_oxidized`](#api-route-21)
        - [`update_device_field`](#api-route-update_device_field)
        - [`get_device_groups`](#api-route-get_device_groups)
    - [`devicegroups`](#api-devicegroups)
        - [`get_devicegroups`](#api-route-get_devicegroups)
        - [`get_devices_by_group`](#api-route-get_devices_by_group)
    - [`routing`](#api-routing)
        - [`list_bgp`](#api-route-1)
        - [`list_ipsec`](#list_ipsec)
    - [`switching`](#api-switching)
        - [`get_vlans`](#api-route-4)
    - [`alerts`](#api-alerts)
        - [`get_alert`](#api-route-12)
        - [`ack_alert`](#api-route-13)
        - [`unmute_alert`](#api-route-24)
        - [`list_alerts`](#api-route-14)
    - [`rules`](#api-rules)
        - [`get_alert_rule`](#api-route-15)
        - [`delete_rule`](#api-route-16)
        - [`list_alert_rules`](#api-route-17)
        - [`add_rule`](#api-route-18)
        - [`edit_rule`](#api-route-19)
    - [`inventory`](#api-inventory)
        - [`get_inventory`](#api-route-20)
    - [`bills`](#api-bills)
        - [`list_bills`](#api-route-22)
        - [`get_bill`](#api-route-23)
    - [`resources`](#api-resources)
        - [`list_arp`](#api-resources-list_arp)
    - [`services`](#api-services)
        - [`list_services`](#api-services-list_services)
        - [`get_service_for_host`](#api-services-get_service_for_host)

Describes the API structure.

# <a name="api-structure">`Structure`</a> [`top`](#top)

## <a name="api-versioning">`Versioning`</a> [`top`](#top)

Versioning an API is a minefield which saw us looking at numerous options on how to do this. Paul wrote an excellent blog post which touches on this: http://blog.librenms.org/2014/09/restful-apis/

We have currently settled on using versioning within the API end point itself `/api/v0`. As the API itself is new and still in active development we also decided that v0 would be the best starting point to indicate it's in development.

## <a name="api-tokens">`Tokens`</a> [`top`](#top)

To access any of the token end points you will be required to authenticate using a token. Tokens can be created directly from within the LibreNMS web interface by going to `/api-access/`.

- Click on 'Create API access token'.
- Select the user you would like to generate the token for.
- Enter an optional description.
- Click Create API Token.

## <a name="api-end_points">`Endpoints`</a> [`top`](#top)

Whilst this documentation will describe and show examples of the end points, we've designed the API so you should be able to traverse through it without know any of the available API routes.

You can do this by first calling `/api/v0`:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0

Output
{
 "list_bgp": "https://librenms.org/api/v0/bgp",
  ...
 "edit_rule": "https://librenms.org/api/v0/rules"
}
```

## <a name="api-input">`Input`</a> [`top`](#top)

Input to the API is done in three different ways, sometimes a combination two or three of these.

  - Passing parameters via the api route. For example when obtaining a devices details you will pass the hostname of the device in the route: `/api/v0/devices/:hostname`.
  - Passing parameters via the query string. For example you can list all devices on your install but limit the output to devices that are currently down: `/api/v0/devices?type=down`
  - Passing data in via JSON, this will mainly be used when adding or updating information via the API, for instance adding a new device:
```curl
curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}'-H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices
```

## <a name="api-output">`Output`</a> [`top`](#top)

Output from the API currently is via two output types.

  - JSON Most API responses will output json. As show in the example for calling the API endpoint.
  - PNG This is for when the request is for an image such as a graph for a switch port.

# <a name="api-endpoints">`Endpoints`</a> [`top`](#top)

## <a name="api-devices">`Devices`</a> [`top`](#top)

### <a name="api-route-2">Function: `del_device`</a> [`top`](#top)

Delete a given device.

Route: /api/v0/devices/:hostname

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:
```text
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

### <a name="api-route-3">Function: `get_device`</a> [`top`](#top)

Get details of a given device.

Route: /api/v0/devices/:hostname

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:
```text
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

### <a name="api-route-5">Function: `get_graphs`</a> [`top`](#top)

Get a list of available graphs for a device, this does not include ports.

Route: /api/v0/devices/:hostname/graphs

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/graphs
```

Output:
```text
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

### <a name="api-route-6">Function: `get_graph_generic_by_hostname`</a> [`top`](#top)

Get a specific graph for a device, this does not include ports.

Route: /api/v0/devices/:hostname/:type

  - hostname can be either the device hostname or id
  - type is the type of graph you want, use [`get_graphs`](#api-route-5) to see the graphs available. Defaults to device_uptime.

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

### <a name="api-route-7">Function: `get_port_graphs`</a> [`top`](#top)

Get a list of ports for a particular device.

Route: /api/v0/devices/:hostname/ports

  - hostname can be either the device hostname or id

Input:

  - columns: Comma separated list of columns you want returned.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ports
```

Output:

```text
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

### <a name="api-route-29">Function: `get_port_stack`</a> [`top`](#top)

Get a list of port mappings for a device.  This is useful for showing physical ports that are in a virtual port-channel.

Route: /api/v0/devices/:hostname/port_stack

  - hostname can be either the device hostname or id

Input:

  - valid_mappings: Filter the result by only showing valid mappings ("0" values not shown).

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/port_stack?valid_mappings
```

Output:

```text
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

### <a name="api-route-25">Function: `get_components`</a> [`top`](#top)

Get a list of components for a particular device.

Route: /api/v0/devices/:hostname/components

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

```text
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

### <a name="api-route-26">Function: `add_components`</a> [`top`](#top)

Create a new component of a type on a particular device.

Route: /api/v0/devices/:hostname/components/:type

  - hostname can be either the device hostname or id
  - type is the type of component to add

Example:
```curl
curl -X POST -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components/APITEST
```

Output:

```text
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

### <a name="api-route-27">Function: `edit_components`</a> [`top`](#top)

Edit an existing component on a particular device.

Route: /api/v0/devices/:hostname/components

  - hostname can be either the device hostname or id

In this example we set the label and add a new field: TestField:
```curl
curl -X PUT -d '{"4459": {"type": "APITEST","label": "This is a test label","status": 1,"ignore": 0,"disabled": 0,"error": "","TestField": "TestData"}}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components
```

Output:

```text
{
    "status": "ok",
    "err-msg": "",
    "count": 1
}
```

Just take the JSON array from add_components or edit_components, edit as you wish and submit it back to edit_components.

### <a name="api-route-28">Function: `delete_components`</a> [`top`](#top)

Delete an existing component on a particular device.

Route: /api/v0/devices/:hostname/components/:component

  - hostname can be either the device hostname or id
  - component is the component ID to be deleted.

Example:
```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components/4459
```

Output:

```text
{
    "status": "ok",
    "err-msg": ""
}
```

### <a name="api-route-8">Function: `get_port_stats_by_port_hostname`</a> [`top`](#top)

Get information about a particular port for a device.

Route: /api/v0/devices/:hostname/ports/:ifname

  - hostname can be either the device hostname or id
  - ifname can be any of the interface names for the device which can be obtained using [`get_port_graphs`](#api-route-7). Please ensure that the ifname is urlencoded if it needs to be (i.e Gi0/1/0 would need to be urlencoded.

Input:

  - columns: Comma separated list of columns you want returned.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/ports/eth0
```

Output:

```text
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

### <a name="api-route-9">Function: `get_graph_by_port_hostname`</a> [`top`](#top)

Get a graph of a port for a particular device.

Route: /api/v0/devices/:hostname/ports/:ifname/:type

  - hostname can be either the device hostname or id
  - ifname can be any of the interface names for the device which can be obtained using [`get_port_graphs`](#api-route-7). Please ensure that the ifname is urlencoded if it needs to be (i.e Gi0/1/0 would need to be urlencoded.
  - type is the port type you want the graph for, you can request a list of ports for a device with [`get_port_graphs`](#api-route-7).

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

### <a name="api-route-10">Function: `list_devices`</a> [`top`](#top)

Return a list of devices.

Route: /api/v0/devices

Input:

  - order: How to order the output, default is by hostname. Can be prepended by DESC or ASC to change the order.
  - type: can be one of the following to filter or search by:
    - all: All devices
    - ignored: Only ignored devices
    - up: Only devices that are up
    - down: Only devices that are down
    - disabled: Disabled devices
    - mac: search by mac address
    - ipv4: search by IPv4 address
    - ipv6: search by IPv6 address (compressed or uncompressed)
  - query: If searching by, then this will be used as the input.
Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices?order=hostname%20DESC&type=down
```

Output:

```text
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

```text
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

### <a name="api-route-11">Function: `add_device`</a> [`top`](#top)

Add a new device.

Route: /api/v0/devices

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

```text
{
    "status": "ok",
    "message": "Device localhost.localdomain (57) has been added successfully"
}
```

### <a name="api-route-21">Function: `list_oxidized`</a> [`top`](#top)

List devices for use with Oxidized. If you have group support enabled then a group will also be returned based on your config.

Route: /api/v0/oxidized

Input (JSON):

  -

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/oxidized
```

Output:

```text
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

### <a name="api-route-update_device_field">Function: `update_device_field`</a> [`top`](#top)

Update devices field in the database.

Route: /api/v0/devices/:hostname

  - hostname can be either the device hostname or id

Input (JSON):

  - field: The column name within the database (can be an array of fields)
  - data: The data to update the column with (can be an array of data))

Examples:
```curl
curl -X PATCH -d '{"field": "notes", "data": "This server should be kept online"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost
```

Output:

```text
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

```text
[
    {
        "status": "ok",
        "message": "Device fields have been updated"
    }
]
```

### <a name="api-route-get_device_groups">Function `get_device_groups`</a> [`top`](#top)

List the device groups that a device is matched on.

Route: /api/v0/devices/:hostname/groups

  - hostname can be either the device hostname or id

Input (JSON):

  -

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/groups
```

Output:
```text
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

## <a name="api-devicegroups">`Device Groups`</a> [`top`](#top)

### <a name="api-route-get_devicegroups">Function `get_devicegroups`</a> [`top`](#top)

List all device groups.

Route: /api/v0/devicegroups

Input (JSON):

  -

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devicegroups
```

Output:
```text
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

### <a name="api-route-get_devices_by_group">Function `get_devices_by_group`</a> [`top`](#top)

List all devices matching the group provided.

Route: /api/v0/devicegroups/:name

  - name Is the name of the device group which can be obtained using [`get_devicegroups`](#api-route-get_devicegroups). Please ensure that the name is urlencoded if it needs to be (i.e Linux Servers would need to be urlencoded.

Input (JSON):

  -

Examples:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devicegroups/LinuxServers
```

Output:
```text
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

## <a name="api-routing">`Routing`</a> [`top`](#top)

### <a name="api-route-1">Function: `list_bgp`</a> [`top`](#top)

List the current BGP sessions.

Route: /api/v0/bgp

Input:

  - hostname = either the devices hostname or id.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bgp
```

Output:
```text
{
 "status": "ok",
 "err-msg": "",
 "count": 0,
 "bgp_sessions": [

 ]
}
```

### <a name="list_ipsec">Function: `list_ipsec`</a> [`top`](#top)

List the current IPSec tunnels which are active.

Route: /api/v0/routing/ipsec/data/:hostname

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/routing/ipsec/data/localhost
```

Output:
```text
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

## <a name="api-switching">`Switching`</a> [`top`](#top)

### <a name="api-route-4">Function: `get_vlans`</a> [`top`](#top)

Get a list of all VLANs for a given device.

Route: /api/v0/devices/:hostname/vlans

  - hostname can be either the device hostname or id

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/vlans
```

Output:
```text
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

## <a name="api-alerts">`Alerts`</a> [`top`](#top)

### <a name="api-route-12">Function: `get_alert`</a> [`top`](#top)

Get details of an alert

Route: /api/v0/alerts/:id

  - id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#api-route-14).

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/1
```

Output:
```text
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

### <a name="api-route-13">Function: `ack_alert`</a> [`top`](#top)

Acknowledge an alert

Route: /api/v0/alerts/:id

  - id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#api-route-14).

Input:

  -

Example:
```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/1
```

Output:
```text
{
 "status": "ok",
 "err-msg": "",
 "message": "Alert has been acknowledged"
}
```

### <a name="api-route-24">Function: `unmute_alert`</a> [`top`](#top)

Unmute an alert

Route: /api/v0/alerts/unmute/:id

  - id is the alert id, you can obtain a list of alert ids from [`list_alerts`](#api-route-14).

Input:

  -

Example:
```curl
curl -X PUT -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts/unmute/1
```

Output:
```text
{
 "status": "ok",
 "err-msg": "",
 "message": "Alert has been unmuted"
}
```


### <a name="api-route-14">Function: `list_alerts`</a> [`top`](#top)

List all alerts

Route: /api/v0/alerts

Input:

  - state: Filter the alerts by state, 0 = ok, 1 = alert, 2 = ack

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts?state=1
```

Output:
```text
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

## <a name="api-rules">`Rules`</a> [`top`](#top)

### <a name="api-route-15">Function: `get_alert_rule`</a> [`top`](#top)

Get the alert rule details.

Route: /api/v0/rules/:id

  - id is the rule id.

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules/1
```

Output:
```text
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

### <a name="api-route-16">Function: `delete_rule`</a> [`top`](#top)

Delete an alert rule by id

Route: /api/v0/rules/:id

  - id is the rule id.

Input:

  -

Example:
```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules/1
```

Output:
```text
{
 "status": "ok",
 "err-msg": "",
 "message": "Alert rule has been removed"
}
```

### <a name="api-route-17">Function: `list_alert_rules`</a> [`top`](#top)

List the alert rules.

Route: /api/v0/rules

  -

Input:

  -

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/rules
```

Output:
```text
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

### <a name="api-route-18">Function: `add_rule`</a> [`top`](#top)

Add a new alert rule.

Route: /api/v0/rules

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
```text
rules
{
 "status": "ok",
 "err-msg": ""
}
```

### <a name="api-route-19">Function: `edit_rule`</a> [`top`](#top)

Edit an existing alert rule

Route: /api/v0/rules

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
```text
rules
{
 "status": "ok",
 "err-msg": ""
}
```

## <a name="api-inventory">`Inventory`</a> [`top`](#top)

### <a name="api-route-20">Function: `get_inventory`</a> [`top`](#top)

Retrieve the inventory for a device. If you call this without any parameters then you will only get part of the inventory. This is because a lot of devices nest each component, for instance you may initially have the chassis, within this the ports - 1 being an sfp cage, then the sfp itself. The way this API call is designed is to enable a recursive lookup. The first call will retrieve the root entry, included within this response will be entPhysicalIndex, you can then call for entPhysicalContainedIn which will then return the next layer of results.

Route: /api/v0/inventory/:hostname

  - hostname can be either the device hostname or the device id

Input:

  - entPhysicalClass: This is used to restrict the class of the inventory, for example you can specify chassis to only return items in the inventory that are labelled as chassis.
  - entPhysicalContainedIn: This is used to retrieve items within the inventory assigned to a previous component, for example specifying the chassis (entPhysicalIndex) will retrieve all items where the chassis is the parent.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/inventory/localhost?entPhysicalContainedIn=65536
```

Output:
```text
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

## <a name="api-bills">`Bills`</a> [`top`](#top)

### <a name="api-route-22">Function: `list_bills`</a> [`top`](#top)

Retrieve the list of bills currently in the system.

Route: /api/v0/bills

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Output:
```text
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
   "overuse": "-"
  }
 ]
}
```

### <a name="api-route-23">Function: `get_bill`</a> [`top`](#top)

Retrieve a specific bill

Route: /api/v0/bills/:id
       /api/v0/bills?ref=:ref
       /api/v0/bills?custid=:custid

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
```text
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
   "overuse": "-"
  }
 ]
}
```

### <a name="api-resources-list_arp">Function: `list_arp`</a> [`top`](#top)

Retrieve a specific ARP entry or all ARP enties for a device

Route: /api/v0/resources/ip/arp/:ip

  - ip is the specific IP you would like to query, if this is all then you need to pass ?device=_hostname_ (or device id)

Input:

  - device if you specify all for the IP then you need to populate this with the hostname or id of the device.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/1.1.1.1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/ip/arp/1.1.1.1?device=localhost
```

Output:
```text
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

### <a name="api-services-list_services">Function: `list_services`</a> [`top`](#top)

Retrieve all services

Route: /api/v0/services

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
```text
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
### <a name="api-services-get_service_for_host">Function: `get_service_for_host`</a> [`top`](#top)

Retrieve services for device

Route: /api/v0/services/:hostname

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
```text
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
