<?php

namespace App\Restify;

use App\Models\User;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class UserRepository extends Repository
{
    public static string $model = User::class;

    public static string $id = 'user_id';

    public static string $title = 'username';

    public static array $search = [
        'username',
        'realname',
        'email',
    ];

    public static array $match = [
        'username' => 'text',
        'realname' => 'text',
        'email' => 'text',
        'enabled' => 'integer',
    ];

    public static array $sort = [
        'username',
        'realname',
        'email',
        'enabled',
    ];

    public static function related(): array
    {
        return [
            'devicesOwned' => BelongsToMany::make('devicesOwned', DeviceRepository::class),
            'portsOwned' => BelongsToMany::make('portsOwned', PortRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('username')->readonly(),
            field('realname')->readonly(),
            field('email')->readonly(),
            field('enabled')->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (! $user->hasRole('admin')) {
                return $query->where('user_id', $user->user_id);
            }

            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
