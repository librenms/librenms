<?php

namespace App\Restify;

use App\Models\AuthLog;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class AuthLogRepository extends Repository
{
    public static string $model = AuthLog::class;

    public static string $title = 'user';

    public static array $search = [
        'user',
        'address',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('datetime')->readonly(),
            field('user')->readonly(),
            field('address')->readonly(),
            field('result')->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    /**
     * Auth logs are generated internally by the authentication system — not created manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Auth logs are historical records and should not be deleted via the API.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
