<?php

namespace App\Restify;

use App\Models\Sla;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class SlaRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Sla::class;

    public static string $id = 'sla_id';

    public static string $title = 'tag';

    public static array $search = [
        'owner',
        'tag',
        'rtt_type',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'sla_nr' => 'integer',
        'owner' => 'text',
        'tag' => 'text',
        'rtt_type' => 'text',
        'rtt' => 'integer',
        'status' => 'integer',
        'opstatus' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'sla_nr',
        'owner',
        'tag',
        'rtt_type',
        'rtt',
        'status',
        'opstatus',
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
            field('sla_nr')->readonly(),
            field('owner')->readonly(),
            field('tag')->readonly(),
            field('rtt_type')->readonly(),
            field('rtt')->readonly(),
            field('status')->readonly(),
            field('opstatus')->readonly(),
        ];
    }

    /**
     * SLA entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * SLA entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
