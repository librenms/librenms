<?php

namespace LibreNMS\Util;

use App\Models\Device;
use Illuminate\Validation\ValidationException;

class VrfContextCleanup
{
    private const array RELATIONSHIPS = [
        'macs', 'nd', 'ipv4', 'ipv6', 'bgppeers', 'bgpPeersCbgp',
        'ospfInstances', 'ospfAreas', 'ospfPorts', 'ospfNbrs',
        'ospfv3Instances', 'ospfv3Areas', 'ospfv3Ports', 'ospfv3Nbrs',
    ];

    public static function hasContextData(Device $device): bool
    {
        return array_any(self::RELATIONSHIPS, fn ($relationship) => $device->$relationship()->where('context_name', '!=', '')->exists());
    }

    /**
     * @return list<array{name: string, token: string}>
     */
    public static function candidates(Device $device): array
    {
        $device->unsetRelation('vrfs');
        $active = $device->getVrfContexts();
        $contexts = [];

        foreach (self::RELATIONSHIPS as $relationship) {
            $query = $device->$relationship();
            $column = $query->getRelated()->qualifyColumn('context_name');
            foreach ($query->whereNotNull($column)->select($column)->cursor() as $model) {
                $context = $model->context_name;
                if ($context !== '' && ! in_array($context, $active, true)) {
                    $contexts[base64_encode($context)] = $context;
                }
            }
        }

        return collect($contexts)->sort()->values()
            ->map(fn (string $context) => ['name' => $context, 'token' => base64_encode($context)])
            ->all();
    }

    public static function clean(Device $device, string $context): void
    {
        $device->getConnection()->transaction(function () use ($device, $context): void {
            $device->setRelation('vrfs', $device->vrfs()->lockForUpdate()->get());
            if ($context === '' || in_array($context, $device->getVrfContexts(), true)) {
                throw ValidationException::withMessages(['context' => __('Active and default contexts cannot be cleaned up.')]);
            }

            $deleted = 0;
            foreach (self::RELATIONSHIPS as $relationship) {
                $models = $device->$relationship()->where('context_name', $context)->get()
                    ->filter(fn ($model) => $model->context_name === $context);
                $models->each->delete();
                $deleted += $models->count();
                $device->unsetRelation($relationship);
            }
            if ($deleted === 0) {
                throw ValidationException::withMessages(['context' => __('No stored data was found for this context.')]);
            }
        });
    }
}
