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
        - [`get_port_stats_by_port_hostname`](#api-route-8)
        - [`get_graph_by_port_hostname`](#api-route-9)
        - [`list_devices`](#api-route-10)
        - [`add_device`](#api-route-11)
    - [`routing`](#api-routing)
        - [`list_bgp`](#api-route-1)
    - [`switching`](#api-switching)
        - [`get_vlans`](#api-route-4)
    - [`alerts`](#api-alerts)
        - [`get_alert`](#api-route-12)
        - [`ack_alert`](#api-route-13)
        - [`list_alerts`](#api-route-14)
    - [`rules`](#api-rules)
        - [`get_alert_rule`](#api-route-15)
        - [`delete_rule`](#api-route-16)
        - [`list_alert_rules`](#api-route-17)
        - [`add_rule`](#api-route-18)
        - [`edit_rule`](#api-route-19)

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
 - type: can be one of the following, all, ignored, up, down, disabled to filter by that device status.

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices?order=hostname%20DESC&type=down
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

### <a name="api-route-11">Function: `add_device`</a> [`top`](#top)

Add a new device.

Route: /api/v0/devices

Input (JSON):

 - hostname: device hostname
 - port: SNMP port (defaults to port defined in config).
 - transport: SNMP protocol (defaults to transport defined in config).
 - version: SNMP version to use, v1, v2c or v3. Defaults to v2c.

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
    "message": "Device localhost.localdomain has been added successfully"
}
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
 "message": "Alert has been ackgnowledged"
}
```

### <a name="api-route-14">Function: `list_alerts`</a> [`top`](#top)

List all alerts

Route: /api/v0/alerts

Input:

 - state: Filter the alerts by state, 0 = ok, 1 = alert, 2 = ack

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/alerts
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
