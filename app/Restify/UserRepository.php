<?php

namespace App\Restify;

use App\Models\User;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class UserRepository extends Repository
{
    public static string $model = User::class;

    public static string $id = 'user_id';

    public static string $title = 'username';




    public static function related(): array
    {
        return [
            'devices-owned' => BelongsToMany::make('devicesOwned', DeviceRepository::class)->label('devices-owned'),
            'ports-owned' => BelongsToMany::make('portsOwned', PortRepository::class)->label('ports-owned'),
        ];
    }

    public static function searchables(): array
    {
        return [
            'username' => SearchableFilter::make()->setColumn('username'),
            'realName' => SearchableFilter::make()->setColumn('realname'),
            'email' => SearchableFilter::make()->setColumn('email'),
        ];
    }

    public static function matches(): array
    {
        return [
            'username' => MatchFilter::make()->setType('text')->setColumn('username'),
            'realName' => MatchFilter::make()->setType('text')->setColumn('realname'),
            'email' => MatchFilter::make()->setType('text')->setColumn('email'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('enabled'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'username' => SortableFilter::make()->setColumn('username'),
            'realName' => SortableFilter::make()->setColumn('realname'),
            'email' => SortableFilter::make()->setColumn('email'),
            'isEnabled' => SortableFilter::make()->setColumn('enabled'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('username')->readonly(),
            field('realName', fn ($value, $model) => $model->realname)->readonly(),
            field('email')->readonly(),
            field('isEnabled', fn ($value, $model) => $model->enabled)->readonly(),
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
