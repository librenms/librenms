## Versioning

The versioning of an API is difficult. We examined many options.

We put the version into the API endpoint itself: `/api/v0`. The API is
new and still in active development. We therefore start at v0 to show
this development state.

## Tokens

Endpoints require authentication using an API access token. You can create and manage tokens
in the LibreNMS web interface by navigating to **Settings (gear icon) → API Settings → API Access** (or `/api-access/`).

To create a token:

- Click **Create API access token**.
- Enter an optional description.
- Select a token expiration (e.g. Never, 7 days, 30 days, 90 days, 1 year, or a custom number of days).
- Click **Create**.
- Copy the generated API token immediately. For security, tokens are stored as SHA-256 hashes and will only be displayed once upon creation or reset.

API tokens are generated in the format `{id}|{secret}` (for example: `1|abc123...`).

## Authentication

API requests are authenticated via HTTP headers.

All API endpoints accept the standard `Authorization: Bearer` header:

- `Authorization: Bearer YOURAPITOKENHERE`

For backwards compatibility, **API v0** (`/api/v0`) also supports:

- `X-Auth-Token: YOURAPITOKENHERE`

## Endpoints

This documentation describes each endpoint and gives examples. The API
also lets you move through it without knowledge of the API routes.

To do this, first call `/api/v0`:

```curl
curl -H 'Authorization: Bearer YOURAPITOKENHERE' https://librenms.org/api/v0
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
curl -X POST -d '{"hostname":"localhost.localdomain","version":"v1","community":"public"}' -H 'Authorization: Bearer YOURAPITOKENHERE' https://librenms.org/api/v0/devices
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
