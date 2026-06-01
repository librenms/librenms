# Creating API v1 Resources

API v1 uses [Laravel Restify](https://restify.binarcode.com/) repositories to expose Eloquent models as REST endpoints. This guide walks through adding a new resource.

## Overview

Each API resource needs:

1. A **Repository** class in `app/Restify/` defines fields, search, and query scoping
2. A **Policy** with an `allowRestify()` method controls who can access the resource
3. **Registration** in `RestifyServiceProvider`

## Step 1: Create the Repository

Create a new file in `app/Restify/`. For example, to expose the `Location` model:

```php
<?php

namespace App\Restify;

use App\Models\Location;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LocationRepository extends Repository
{
    public static string $model = Location::class;

    // The primary key column (if not 'id')
    public static string $id = 'id';

    // The field used as the display title
    public static string $title = 'location';

    // Fields searchable via ?search=query
    public static array $search = [
        'location',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('location')->readonly(),
            field('lat')->readonly(),
            field('lng')->readonly(),
            field('timestamp')->readonly(),
        ];
    }

    // Scope the index query (e.g. for access control)
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
```

### Key concepts

- **`$model`** The Eloquent model class this repository wraps.
- **`$id`** Override if the model uses a non-standard primary key (e.g. `device_id`, `port_id`).
- **`uriKey`** The URL slug for this resource (e.g. `devices`). Auto-derived from the class name; override to control the public name. See [Resource name & URLs](#resource-name--urls-urikey).
- **`$title`** The column used as the human-readable label for a record (shown in relationship pickers and some UIs).
- **`fields()`** Defines which attributes are exposed in the API response. Use `->readonly()` for fields that should not be writable. See [Fields and `->label()`](#fields-and-the-label-method).
- **`$search` / `searchables()`** Columns the `?search=` parameter queries against. See [Filtering, searching, sorting](#filtering-searching-and-sorting).
- **`related()`** Declares relationships (nested routes + `?include=`). See [Relationships](#relationships-related).
- **`indexQuery()` / `showQuery()`** Scope queries for access control. See existing `DeviceRepository` and `PortRepository` for examples using `$query->hasAccess($user)`.

### Making fields writable

Remove `->readonly()` to allow updates. You can also add validation:

```php
field('notes')->rules('nullable', 'string', 'max:1000'),
field('purpose')->rules('nullable', 'string', 'max:255'),
```

### Fields and the `->label()` method

`field('name')` exposes an attribute. The first argument is the **public JSON key** in the response. When it matches the column name, the value is read/written directly:

```php
field('hostname')->rules('required', 'string', 'max:255'),   // reads/writes the hostname column
```

When the API name should differ from the database column, pass a **resolve callback** as the second argument (this is the convention used throughout LibreNMS to present clean names over legacy columns):

```php
field('systemName',  fn ($value, $model) => $model->sysName)->readonly(),   // JSON "systemName" <- sysName column
field('isUp',        fn ($value, $model) => $model->status)->readonly(),
```

Common field modifiers:

| Modifier | Effect |
|---|---|
| `->readonly()` | Field is returned but never accepted on create/update. |
| `->rules('nullable', 'string', ...)` | Laravel validation applied on store/update. |
| `->hidden()` | Field is accepted for writes but omitted from responses. |
| `->label('name')` | Overrides the field's "attribute" used internally as `getAttribute() = label ?? attribute`. Rarely needed on plain fields; its important role is on **relationships**, where it sets the relationship's URL segment. |

> On **relationships** (`HasMany`/`BelongsTo`/`BelongsToMany`) the `label` is what controls the nested-route URL segment but you normally don't set it by hand: the base repository derives it automatically. See [Relationships](#relationships-related).

## Filtering, searching, and sorting

These three methods turn into query parameters that show up automatically in the OpenAPI document and Swagger UI. In every case the **array key is the public API name** and `setColumn()` maps it to the real database column, so the API stays stable even if the schema changes.

```php
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

// ?search=core  fuzzy match across these columns
public static function searchables(): array
{
    return [
        'hostname' => SearchableFilter::make()->setColumn('hostname'),
        'ip'       => SearchableFilter::make()->setColumn('ip'),
    ];
}

// ?hostname=core1&isUp=true  exact, per-field filters
public static function matches(): array
{
    return [
        'hostname' => MatchFilter::make()->setType('text')->setColumn('hostname'),
        'isUp'     => MatchFilter::make()->setType('bool')->setColumn('status'),
        'uptime'   => MatchFilter::make()->setType('integer')->setColumn('uptime'),
    ];
}

// ?sort=-hostname  (leading "-" = descending)
public static function sorts(): array
{
    return [
        'hostname' => SortableFilter::make()->setColumn('hostname'),
    ];
}
```

- **`setColumn()`** decouples the API name (the array key) from the DB column. e.g. expose `systemName` while filtering on `sysName`.
- **`MatchFilter::setType()`** (`text`, `bool`, `integer`, `datetime`, …) drives both how the value is cast in the query and the parameter's type in the OpenAPI document.
- A simple `public static array $search = ['hostname', 'ip'];` is a shorthand alternative to `searchables()` when you only need plain column matching.

## Relationships (`related()`)

Declare related resources in `related()`. Each entry maps an array key to an *eager field* pointing at another repository:

```php
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Fields\BelongsToMany;

public static function related(): array
{
    return [
        'ports'    => HasMany::make('ports', PortRepository::class),
        'location' => BelongsTo::make('location', LocationRepository::class),
        'bills'    => BelongsToMany::make('bills', BillRepository::class),
    ];
}
```

Field types:

| Type | Use when | Extra routes |
|---|---|---|
| `BelongsTo` | the model holds the foreign key (to-one) | |
| `HasMany` | the related rows hold the foreign key (to-many) | |
| `BelongsToMany` | many-to-many through a pivot table | `attach` / `sync` / `detach` |

This automatically exposes:

- `GET /api/v1/devices/{id}/ports` list a relation (always a paginated list, even for `BelongsTo`)
- `?include=ports,location` embed relations inline on the parent
- for `BelongsToMany`: `POST .../attach/{rel}`, `POST .../sync/{rel}`, `POST .../detach/{rel}`

### The three identifiers (read this before adding relations)

`HasMany::make($name, TargetRepository::class)` sets two values, and a third is derived. Mixing them up causes `403`/`404`s, so it's worth understanding:

| Identifier | Source | Controls |
|---|---|---|
| **`relation`** | the `make()` first arg | the Eloquent method called to load data (`$model->{relation}()`) |
| **`attribute`** | the `make()` first arg | the `attach`/`sync`/`detach` URL segment |
| **segment / `label`** | auto-set to the **target repository's `uriKey`** | the **`GET` relationship** URL segment |

Restify resolves a nested route `/{parent}/{id}/{segment}` by requiring `{segment}` to (a) be a registered repository `uriKey` **and** (b) match a parent relation's `getAttribute()` (`= label ?? attribute`). The base class `App\Restify\Repository::collectRelated()` keeps these in sync for you it pins every relation's `label` to the target repository's `uriKey`. The practical consequences:

- The `GET` relationship segment is **always the target repository's `uriKey`**, e.g. `DeviceOutageRepository::uriKey() === 'device-outages'` → `GET /api/v1/devices/{id}/device-outages`.
- The **target model's repository is the single source of truth** for its public name. Rename it once via [`uriKey`](#resource-name--urls-urikey) and every relationship that points to it updates automatically no `->label()` calls scattered across parent repositories.
- You normally just declare the relation with its Eloquent method name; the URL "just works".

### When the Eloquent method name isn't URL-friendly

If the model's relationship method is camelCase (e.g. `devicesOwned()`), pass the kebab-case slug as the first argument (so `attach` URLs stay clean) and point `relation` at the real method with `tap()`:

```php
'devices-owned' => tap(
    BelongsToMany::make('devices-owned', DeviceRepository::class),
    static fn ($f) => $f->relation = 'devicesOwned',  // the actual Eloquent method
),
```

For the example above:

- `GET /api/v1/users/{id}/devices` lists them (segment = `DeviceRepository::uriKey()` = `devices`).
- `POST /api/v1/users/{id}/attach/devices-owned` attaches (segment = `attribute` = `devices-owned`, bridged to `devicesOwned()` by the `RestifyAttachRelationResolver` middleware).

### Missing parent

A relationship request whose parent id doesn't exist (e.g. `/api/v1/devices/999999/ports`) returns `404` via the `EnsureRelatedParentExists` middleware rather than a 500.

## Resource name & URLs (`uriKey`)

Every repository has a **`uriKey`** the slug used both for its own collection path and for any relationship that points at it.

- **Default:** kebab-case plural of the class name minus `Repository` `PortRepository → ports`, `DeviceOutageRepository → device-outages`.
- **Override** on the owning repository:

```php
public static $uriKey = 'device-outages';
```

Because relationship segments are derived from the *target* repository's `uriKey` (see above), this is the **single place** to set a resource's public name changing it moves `/api/v1/<uriKey>` and every `…/{id}/<uriKey>` reference at once.

## Step 2: Create or Update the Policy

Restify checks a `allowRestify()` method on the model's policy to determine if the resource should be accessible at all.

If the model already has a policy in `app/Policies/`, add the method:

```php
public function allowRestify(User $user = null): bool
{
    return $user !== null && $user->hasGlobalRead();
}
```

If no policy exists, create one:

```php
<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function allowRestify(User $user = null): bool
    {
        return $user !== null && $user->hasGlobalRead();
    }

    public function viewAny(User $user): bool
    {
        return $user->hasGlobalRead();
    }

    public function view(User $user, Location $location): bool
    {
        return $user->hasGlobalRead();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Location $location): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->isAdmin();
    }
}
```

Restify uses standard Laravel policy methods (`viewAny`, `view`, `create`, `update`, `delete`) for authorization on individual actions.

## Step 3: Register the Repository

Add the repository to `app/Providers/RestifyServiceProvider.php`:

```php
use App\Restify\LocationRepository;

// In the boot() method:
Restify::repositories([
    DeviceRepository::class,
    PortRepository::class,
    UserRepository::class,
    LocationRepository::class,  // Add here
]);
```

## Step 4: Clear caches

If running in Docker, the container caches config on startup. Clear it after changes:

```bash
docker compose exec --user librenms librenms php artisan config:clear
```

## Result

After registration, these endpoints are automatically available:

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/v1/locations` | List (paginated, searchable) |
| GET | `/api/v1/locations/{id}` | Show single resource |
| POST | `/api/v1/locations` | Create (if fields are writable and policy allows) |
| PUT/PATCH | `/api/v1/locations/{id}` | Update |
| DELETE | `/api/v1/locations/{id}` | Delete |
| GET | `/api/v1/locations/{id}/<relation>` | List a relation declared in `related()` |
| POST | `/api/v1/locations/{id}/attach/<relation>` | Attach (for `BelongsToMany` relations) |

Plus search, filters, bulk operations, and nested resource support all provided by Restify automatically. The OpenAPI document at `/api/v1/openapi.json` updates itself from this configuration no regeneration step.

## Access Control Patterns

### Global read access only

```php
public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
{
    return $query;
}
```

The `allowRestify()` policy method already gates access. No additional scoping needed if global read is sufficient.

### Per-device access scoping

Used by `DeviceRepository` and `PortRepository` restricts results to devices the user has access to:

```php
public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
{
    if ($user = $request->user()) {
        return $query->hasAccess($user);
    }

    return $query->whereRaw('1 = 0');
}
```

### Admin-only listing

Used by `UserRepository` non-admins can only see themselves:

```php
public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
{
    if ($user = $request->user()) {
        if (! $user->isAdmin()) {
            return $query->where('user_id', $user->user_id);
        }
        return $query;
    }

    return $query->whereRaw('1 = 0');
}
```

## File Locations

| What | Path |
|------|------|
| Repositories | `app/Restify/` |
| Base repository (relationship naming, action gating) | `app/Restify/Repository.php` |
| Service provider | `app/Providers/RestifyServiceProvider.php` |
| Restify config (middleware stack) | `config/restify.php` |
| Sanctum config | `config/sanctum.php` |
| Policies | `app/Policies/` |
| Relationship-name → method bridge | `app/Http/Middleware/RestifyAttachRelationResolver.php` |
| Missing-parent → 404 guard | `app/Http/Middleware/EnsureRelatedParentExists.php` |
| OpenAPI generator | `app/Services/Api/OpenApi/OpenApiGenerator.php` |
