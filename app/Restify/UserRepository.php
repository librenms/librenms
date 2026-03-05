<?php

namespace App\Restify;

use App\Models\User;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class UserRepository extends Repository
{
    public static string $model = User::class;

    public static string $id = "user_id";

    public static string $title = "username";

    public static array $search = [
        "username",
        "realname",
        "email",
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field("username")->readonly(),
            field("realname")->readonly(),
            field("email")->readonly(),
            field("level")->readonly(),
            field("enabled")->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (! $user->isAdmin()) {
                return $query->where("user_id", $user->user_id);
            }

            return $query;
        }

        return $query->whereRaw("1 = 0");
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
