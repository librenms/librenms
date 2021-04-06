source: API/index.md
path: blob/master/doc/

## Versioning

Versioning an API is a minefield which saw us looking at numerous
options on how to do this.

We have currently settled on using versioning within the API end point
itself `/api/v0`. As the API itself is new and still in active
development we also decided that v0 would be the best starting point
to indicate it's in development.

## Tokens

To access any of the token end points you will be required to
authenticate using a token. Tokens can be created directly from within
the LibreNMS web interface by going to `/api-access/`.

- Click on 'Create API access token'.
- Select the user you would like to generate the token for.
- Enter an optional description.
- Click Create API Token.

## Endpoints

Whilst this documentation will describe and show examples of the end
points, we've designed the API so you should be able to traverse
through it without knowing any of the available API routes.

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

Input to the API is done in three different ways, sometimes a
combination two or three of these.

- Passing parameters via the api route. For example when obtaining a
  devices details you will pass the hostname of the device in the route: `/api/v0/devices/:hostname`.
- Passing parameters via the query string. For example you can list
  all devices on your install but limit the output to devices that are
  currently down: `/api/v0/devices?type=down`
- Passing data in via JSON, this will mainly be used when adding or
  updating information via the API, for instance adding a new device:

```curl
curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}'-H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices
```

## Output

Output from the API currently is via two output types:

- JSON Most API responses will output json. As show in the example for
  calling the API endpoint.
- PNG This is for when the request is for an image such as a graph for a switch port.

## Endpoint Categories

- [Devices](Devices.md)
- [DeviceGroups](DeviceGroups.md)
- [Ports](Ports.md)
- [Port_Groups](Port_Groups.md)
- [PortGroups](PortGroups.md)
- [Alerts](Alerts.md)
- [Routing](Routing.md)
- [Switching](Switching.md)
- [Inventory](Inventory.md)
- [Bills](Bills.md)
- [ARP](ARP.md)
- [Services](Services.md)
- [Logs](Logs.md)
- [System](System.md)
- [Locations](Locations.md)
