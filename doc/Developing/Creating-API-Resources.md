# Creating API v1 Resources

API v1 uses [Laravel Restify](https://restify.binarcode.com/) repositories to expose Eloquent models as REST endpoints. This guide walks through adding a new resource.

## Overview

Each API resource needs:

1. A **Repository** class in `app/Restify/` — defines fields, search, and query scoping
2. A **Policy** with an `allowRestify()` method — controls who can access the resource
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

- **`$model`** — The Eloquent model class this repository wraps.
- **`$id`** — Override if the model uses a non-standard primary key (e.g. `device_id`, `port_id`).
- **`fields()`** — Defines which attributes are exposed in the API response. Use `->readonly()` for fields that should not be writable.
- **`$search`** — Array of columns that the `?search=` parameter queries against.
- **`indexQuery()` / `showQuery()`** — Scope queries for access control. See existing `DeviceRepository` and `PortRepository` for examples using `$query->hasAccess($user)`.

### Making fields writable

Remove `->readonly()` to allow updates. You can also add validation:

```php
field('notes')->rules('nullable', 'string', 'max:1000'),
field('purpose')->rules('nullable', 'string', 'max:255'),
```

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

Plus search, filters, bulk operations, and nested resource support — all provided by Restify automatically.

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

Used by `DeviceRepository` and `PortRepository` — restricts results to devices the user has access to:

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

Used by `UserRepository` — non-admins can only see themselves:

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
| Base repository | `app/Restify/Repository.php` |
| Service provider | `app/Providers/RestifyServiceProvider.php` |
| Restify config | `config/restify.php` |
| Sanctum config | `config/sanctum.php` |
| Policies | `app/Policies/` |
