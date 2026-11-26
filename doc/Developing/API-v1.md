# API v1

The v1 API follows [JSON:API v1.1](https://jsonapi.org/format/1.1/). This page
covers the conventions that keep its endpoints consistent and how to add one.
The user facing reference is [API v1](../API/v1.md).

## Building blocks

| Piece | Location |
|-------|----------|
| Routes | `routes/api-v1.php`, served under `/api/v1` and named `api.v1.*` |
| Middleware | the `api.v1` group in `bootstrap/app.php`: JSON:API errors, content negotiation and reserved query parameters, the `api.v1.enabled` switch |
| Controllers | `app/Http/Controllers/Api/V1/` |
| Resources | `app/Http/Resources/Api/V1/`, extending `JsonApiResource` there (Laravel's JSON:API resources) |
| Query parameters | `App\Http\Requests\Api\V1\ResourceRequest`: `include`, `fields`, `sort`, `filter`, `page` |

Routes are public unless they are inside the `auth:sanctum` and `can:api.access`
group in the route file. Controllers authorize with the model's policy, the same
one the web UI uses, and lists are scoped with the model's `hasAccess` scope.

A list endpoint names the sorts and filters it supports, by API name.
Filters map to the model's `Filterable` fields:

```php
public function index(ResourceRequest $request): ResourceCollection
{
    $this->authorize('viewAny', Device::class);
    $request->allowIncludes([]);

    return DeviceResource::collection($request->paginate(
        Device::query()->hasAccess($request->user()),
        sorts: ['hostname' => 'hostname'],
        filters: [
            'hostname' => 'hostname',
            'isEnabled' => ['disabled', ResourceRequest::booleanFilter(inverted: true)],
        ],
    ));
}
```

Unsupported or invalid query parameters are a `400` that names the parameter.
An endpoint specific query parameter needs a character other than a–z, such as
`withCounts`, all lowercase names are reserved by JSON:API.

Resources and collections add `links.self` and `jsonapi` to the document.
A document with only `meta`, such as `/system`, is a `MetaDocument`.

Routes and config are cached on container start, so clear them after changing
endpoints (`php artisan route:clear`, `php artisan config:clear`) and check the
result with `php artisan route:list --path=api/v1`.

## Naming

1. **camelCase** for attribute and relationship names: `system_name` → `systemName`.
   Endpoints use kebab-case: `/port-groups`.
2. **No abbreviations.** Write words out: `sysDescr` → `systemDescription`,
   `ifOperStatus` → `interfaceOperationalStatus`, `ospfNbr` → `ospfNeighbor`,
   `srcAddr` → `sourceAddress`, `serial` → `serialNumber`. Acronyms stay short
   (`ospf`, `fdb`, `mac`, `bgp`, `ip`).
   `if` is not an acronym, expand it to `interface`. `sys` expands to `system`.
3. **No object prefix** on a model's own endpoint, applied after expanding:
   `sensorClass` on `/sensors` → `class`, `storageSize` on `/storage` → `size`.
   A port is an interface, so `ifOperStatus` on `/ports` → `operationalStatus`.
   Keep the prefix when the bare name is reserved: a device's `type` is `deviceType`,
   a port's `ifType` is `interfaceType`.
   Well known SNMP identifiers keep their exact name: `ifIndex`, `sysObjectID`.
   Descriptive values still expand: `sysDescr` → `systemDescription`.
4. **`id` and `type` are reserved.** `id` is the primary key of the model,
   `type` is the endpoint name (`devices`).
5. **Plural** names for lists, singular otherwise.
6. **Booleans** start with `is`, `has` or similar, in the positive form:
   `disabled` → `isEnabled`, `disable_notify` → `isAlertingEnabled`.
7. **Timestamps** end in `At`: `last_polled` → `lastPolledAt`.
   Use `createdAt` and `updatedAt` for when the record was created and last changed,
   not `receivedAt`, `datetime` or `timestamp`. Other names only for times that
   don't map to those, such as `outageStartAt` and `outageEndAt`.
8. **Reserved members** can't be attribute or relationship names: `id`, `type`, `data`,
   `attributes`, `relationships`, `links`, `meta`, `included`, `errors`, `jsonapi`.
9. **No units in names.** Document them instead: `uptimeSeconds` → `uptime` (seconds).
10. **Reuse names** already used elsewhere in v1 for the same meaning, such as
    `ipAddress` and `macAddress`. Name a field for what users call it: the
    `description` a user enters is a device's purpose and a port's ifAlias.

## Endpoints

- Endpoints are plural unless that would confuse (`/devices`, `/storage`),
  and match the resource `type`. Singletons such as `/system` stay singular.
- Actions include a verb, and a noun when acting on another model, in kebab-case
  like every other path segment: `/alerts/{id}/acknowledge`,
  `/devices/{id}/acknowledge-alerts`.
- Related resources may be grouped under a section prefix with a route group, in
  kebab-case, such as the routing resources under `/routing`
  (`/routing/isis-adjacencies`). The resource `type` stays the bare name
  (`isis-adjacencies`) and route names carry the section
  (`api.v1.routing.isis-adjacencies.show`), so relationship links built with
  `route()` resolve.

## Relationships

- Use relationships, not foreign key attributes: a port has `relationships.device`,
  not `deviceId`.
- Every relationship has a `links.related` URL nested under its owner, named
  after the relationship: `/devices/{id}/ports`, `/ports/{id}/device`. A to-one relationship may be
  included with `include`. A to-many relationship is not includable, its related
  link is a paginated endpoint such as `/devices/{id}/ports`.
- Included resources are authorized with their own policy, so a relationship
  never shows a record its own endpoint would refuse.

## Metrics

Resources hold current state only. Time series, counters and rates are not
attributes, they will be served under a separate `/api/v1/metrics` prefix, queried
by metric and the resource they belong to. A resource may link to its metrics.

## Versioning

v1 is in beta and may still change. Once it is final, its shape is frozen and
changes follow these rules.

Not breaking, no new version: adding attributes, relationships, endpoints,
filters, sorts or optional parameters. Clients must ignore members they don't know.

Breaking, needs a new version: renaming or removing anything, changing a
value's type, unit or meaning, changing defaults, or rejecting a request that
used to work.

Once v1 is final:

- The major version stays in the URL (`/api/v1`). Versions within it are dates
  matching a LibreNMS release, selected with a `LibreNMS-Api-Version` request
  header. Unknown versions are a `400`.
- A request without the header gets the first final version, so a client that
  never opts in never changes. Responses echo the version they used in the same
  header and send `Vary: LibreNMS-Api-Version`.
- The code only builds the latest shape. Each breaking change adds a downgrade
  step that turns the new shape into the previous one. Older versions run the
  steps from newest to oldest, so controllers and resources never check the version.
- Downgrades apply to the output, so request-side changes (filter and sort names,
  defaults, status codes) check the version in the controller. Prefer adding a
  new name over changing an old one.
- Every document must go through one place for downgrades to apply: resources,
  `ResourceCollection`, `MetaDocument` and `FormatJsonApiError`. Don't build
  v1 JSON by hand in a controller.
- Retiring a version announces it with `Deprecation` and `Sunset` response headers
  first.

`jsonapi.version` is the JSON:API spec version, not the LibreNMS API version.

## Testing

Feature tests live in `tests/Feature/Api/V1/`. Cover the document shape, access
for a limited user and each supported sort and filter.

Each resource has a test that asserts its exact attribute and relationship
names. When it fails, decide whether the change is additive (update the test) or
breaking (needs a new version once v1 is final).
