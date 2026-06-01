<?php

namespace App\Restify;

use App\Models\DeviceGroup;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeviceGroupRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = DeviceGroup::class;

    public static string $title = 'name';

    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class)
                ->canAttach(fn ($request, $pivot) => true)
                ->canDetach(fn ($request, $pivot) => self::groupForPivot($pivot)?->type === 'static'),
            'users' => BelongsToMany::make('users', UserRepository::class),
        ];
    }

    private static function groupForPivot($pivot): ?DeviceGroup
    {
        $id = $pivot->device_group_id ?? null;

        return $id ? DeviceGroup::find($id) : null;
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'description' => MatchFilter::make()->setType('text')->setColumn('desc'),
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'rules' => MatchFilter::make()->setType('text')->setColumn('rules'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'description' => SortableFilter::make()->setColumn('desc'),
            'category' => SortableFilter::make()->setColumn('type'),
            'rules' => SortableFilter::make()->setColumn('rules'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('description', fn ($value, $model) => $model->desc)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->desc = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string', 'max:255'),
            field('category', fn ($value, $model) => $model->type)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->type = $request->input($attribute);
                    }
                })
                ->rules('required', 'string', 'in:dynamic,static'),
            field('rules')->rules('nullable', 'array'),
        ];
    }

    public function actions(RestifyRequest $request): array
    {
        $canSee = fn (\Illuminate\Http\Request $req) => $req->user()?->can('update', \App\Models\DeviceGroup::class) ?? false;

        return [
            \App\Restify\Actions\MaintenanceDeviceGroupAction::make()->canSee($canSee),
        ];
    }

    public function store(RestifyRequest $request)
    {
        $deviceIds = $this->validatedDeviceIds($request);

        $response = parent::store($request);

        $this->syncDevicesIfRequested($deviceIds);

        return $response;
    }

    public function update(RestifyRequest $request, $repositoryId)
    {
        $deviceIds = $this->validatedDeviceIds($request);

        $response = parent::update($request, $repositoryId);

        $this->syncDevicesIfRequested($deviceIds);

        return $response;
    }

    /**
     * Returns null when 'devices' is not in the payload (leave membership untouched),
     * otherwise an array of integer device_ids (possibly empty to clear).
     *
     * @return array<int>|null
     */
    private function validatedDeviceIds(RestifyRequest $request): ?array
    {
        if (! $request->has('devices')) {
            return null;
        }

        $payload = ['devices' => $request->input('devices', [])];
        Validator::make($payload, [
            'devices' => ['array'],
            'devices.*' => ['integer', 'exists:devices,device_id'],
        ])->validate();

        return array_values(array_map('intval', $payload['devices']));
    }

    /**
     * @param  array<int>|null  $deviceIds
     */
    private function syncDevicesIfRequested(?array $deviceIds): void
    {
        if ($deviceIds === null) {
            return;
        }

        /** @var DeviceGroup $group */
        $group = $this->resource;

        if ($group->type !== 'static' && $deviceIds !== []) {
            throw ValidationException::withMessages([
                'devices' => 'Devices cannot be manually assigned to a dynamic group.',
            ]);
        }

        $group->devices()->sync($deviceIds);
    }
}
