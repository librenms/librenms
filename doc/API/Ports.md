source: API/Devices.md

## Ports

#### Function: `get_all_ports`

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

#### Function: `get_port_info`

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

#### Function: `get_port_info`

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

#### Function: `get_devices_by_group`

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

#### Function: `get_graph_by_portgroup`

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

#### Function: `get_graph_by_portgroup_multiport_bits`

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

