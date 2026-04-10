<?php

namespace App\Restify;

use App\Models\PortGroup;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class PortGroupRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortGroup::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
        'desc',
    ];

    public static function related(): array
    {
        return [
            'ports' => BelongsToMany::make('ports', PortRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('desc')->rules('nullable', 'string'),
        ];
    }
}
