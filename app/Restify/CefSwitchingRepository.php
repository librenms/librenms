<?php

namespace App\Restify;

use App\Models\CefSwitching;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class CefSwitchingRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = CefSwitching::class;

    public static string $id = 'cef_switching_id';

    public static string $title = 'cef_path';

    public static array $search = [
        'cef_path',
        'afi',
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
            field('entPhysicalIndex')->readonly(),
            field('afi')->readonly(),
            field('cef_index')->readonly(),
            field('cef_path')->readonly(),
            field('drop')->readonly(),
            field('punt')->readonly(),
            field('punt2host')->readonly(),
            field('updated')->readonly(),
        ];
    }

    /**
     * CEF switching entries are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * CEF switching entries are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
