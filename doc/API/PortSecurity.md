### `get_all_port_security`

Get all port security info by inputting port_id

Route: `/api/v0/port_security`

  - 

Input:

- 

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_security/
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
        {
          "id": "1",
          "port_id": "1",
          "device_id": "1",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "secureup",
          "cpsIfMaxSecureMacAddr": 2,
          "cpsIfCurrentSecureMacAddrCount": 2,
          "cpsIfViolationAction": "dropNotify",
          "cpsIfViolationCount": 0,
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
        {
          "id": "2",
          "port_id": "2",
          "device_id": "1",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "secureup",
          "cpsIfMaxSecureMacAddr": "2",
          "cpsIfCurrentSecureMacAddrCount": "2",
          "cpsIfViolationAction": "dropNotify",
          "cpsIfViolationCount": "0",
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
        ...
        {
          "id": "100",
          "port_id": "130",
          "device_id": "5",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "securedown",
          "cpsIfMaxSecureMacAddr": "5",
          "cpsIfCurrentSecureMacAddrCount": "2",
          "cpsIfViolationAction": "shutdown",
          "cpsIfViolationCount": "10",
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
    ]
}
```

### `get_port_security_by_port`

Get all port security info by inputting port_id

Route: `/api/v0/port_security/:port_id`

  - portid must be an integer

Input:

- 

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_security/123
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
         {
          "id": "1",
          "port_id": "1",
          "device_id": "1",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "secureup",
          "cpsIfMaxSecureMacAddr": 2,
          "cpsIfCurrentSecureMacAddrCount": 2,
          "cpsIfViolationAction": "dropNotify",
          "cpsIfViolationCount": 0,
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
        {
          "id": "2",
          "port_id": "2",
          "device_id": "1",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "secureup",
          "cpsIfMaxSecureMacAddr": "2",
          "cpsIfCurrentSecureMacAddrCount": "2",
          "cpsIfViolationAction": "dropNotify",
          "cpsIfViolationCount": "0",
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
        ...
        {
          "id": "100",
          "port_id": "130",
          "device_id": "5",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "securedown",
          "cpsIfMaxSecureMacAddr": "5",
          "cpsIfCurrentSecureMacAddrCount": "2",
          "cpsIfViolationAction": "shutdown",
          "cpsIfViolationCount": "10",
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
    ]
}
```

### `get_port_security_by_hostname`

Get all port security info by inputting port_id

Route: `/api/v0/port_security/:hostname`

  - hostname can be str hostname or int device_id

Input:

- 

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_security/switch1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/port_security/5
```

Output:

```json
{
  "status": "ok",
  "message": "",
  "ports": [
         {
          "id": "1",
          "port_id": "1",
          "device_id": "5",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "secureup",
          "cpsIfMaxSecureMacAddr": 2,
          "cpsIfCurrentSecureMacAddrCount": 2,
          "cpsIfViolationAction": "dropNotify",
          "cpsIfViolationCount": 0,
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
        {
          "id": "2",
          "port_id": "2",
          "device_id": "5",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "secureup",
          "cpsIfMaxSecureMacAddr": "2",
          "cpsIfCurrentSecureMacAddrCount": "2",
          "cpsIfViolationAction": "dropNotify",
          "cpsIfViolationCount": "0",
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
        ...
        {
          "id": "100",
          "port_id": "130",
          "device_id": "5",
          "cpsIfPortSecurityEnable": "true",
          "cpsIfPortSecurityStatus": "securedown",
          "cpsIfMaxSecureMacAddr": "5",
          "cpsIfCurrentSecureMacAddrCount": "2",
          "cpsIfViolationAction": "shutdown",
          "cpsIfViolationCount": "10",
          "cpsIfSecureLastMacAddress": "0:1e:f7:c3:50:6",
          "cpsIfStickyEnable": "false"
          //"cpsIfSecureLastMacAddrVlanId": null
        },
    ]
}
```
