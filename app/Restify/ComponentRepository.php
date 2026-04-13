<?php

namespace App\Restify;

use App\Models\Component;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class ComponentRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Component::class;

    public static string $title = 'label';

    public static array $search = [
        'label',
        'type',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'type' => 'text',
        'label' => 'text',
        'status' => 'integer',
        'disabled' => 'integer',
        'ignore' => 'integer',
        'error' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'type',
        'label',
        'status',
        'disabled',
        'ignore',
        'error',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('type')->readonly(),
            field('label')->readonly(),
            field('status')->readonly(),
            field('disabled')->readonly(),
            field('ignore')->readonly(),
            field('error')->readonly(),
        ];
    }

    /**
     * Components are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Components are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
