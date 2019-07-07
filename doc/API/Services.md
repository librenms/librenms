source: API/Services.md
path: blob/master/doc/

### `list_services`

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
    "message": "",
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

### `get_service_for_host`

Retrieve services for device

Route: `/api/v0/services/:hostname`

- id or hostname is the specific device

Input:

- state: only which have a certain state (valid options are 0=Ok, 1=Warning, 2=Critical).
- type: service type, used sql LIKE to find services, so for tcp, use
  type=tcp for http use type=http

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
    "message": "",
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

### `add_service_for_host`

Add a service for device

Route: `/api/v0/services/:hostname`

- id or hostname is the specific device

Input:

- type: service type
- ip: ip of the service
- desc: description for the service
- param: parameters for the service
- ignore: ignore the service for checks

Example:

```curl
curl -X POST -d '{"type":"ping","ip": "192.168.1.10","desc":"test ping","param": "-t 10 -c 5"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/services/192.168.1.10
```

Output:

```json
{
    "status": "ok",
    "message": "Service ping has been added to device 192.168.1.10 (#10)"
}
```
