## Versioning

The versioning of an API is difficult. We examined many options.

We put the version into the API endpoint itself: `/api/v0`. The API is
new and still in active development. We therefore start at v0 to show
this development state.

## Tokens

Each endpoint needs authentication with a token. You can create a token
in the LibreNMS web interface at `/api-access/`.

- Click on 'Create API access token'.
- Select the user of the new token.
- Enter an optional description.
- Click Create API Token.

## Endpoints

This documentation describes each endpoint and gives examples. The API
also lets you move through it without knowledge of the API routes.

To do this, first call `/api/v0`:

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

There are three input methods for the API. A call can use two or three
of them together.

- Parameters in the API route. For example, the details of a device
  need the hostname in the route: `/api/v0/devices/:hostname`.
- Parameters in the query string. For example, this call lists all
  devices on your install but shows only the down devices:
  `/api/v0/devices?type=down`
- Data in JSON. This method adds and updates information. For example,
  it adds a new device:

```curl
curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices
```

## Output

The API has two output types:

- JSON: most API responses give JSON, as in the example above.
- PNG: this type applies to a request for an image, such as a graph of
  a switch port.

## Endpoint Categories

- [Devices](Devices.md)
- [DeviceGroups](DeviceGroups.md)
- [Ports](Ports.md)
- [Port_Groups](Port_Groups.md)
- [PortGroups](PortGroups.md)
- [PortSecurity](PortSecurity.md)
- [Alerts](Alerts.md)
- [Routing](Routing.md)
- [Switching](Switching.md)
- [Inventory](Inventory.md)
- [Bills](Bills.md)
- [ARP](ARP.md)
- [Services](Services.md)
- [Logs](Logs.md)
- [System](System.md)
- [Pollers](Pollers.md)
- [Locations](Locations.md)
