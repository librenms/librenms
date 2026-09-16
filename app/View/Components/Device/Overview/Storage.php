<?php

namespace App\View\Components\Device\Overview;

use App\Models\Device;
use App\Models\Storage as StorageModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use LibreNMS\Util\Number;

class Storage extends Component
{
    /**
     * @var Collection<int, array{drive: \App\Models\Storage, description: string, percent: float, total: string, free: string, used: string}>
     */
    public Collection $drives;

    public function __construct(public Device $device)
    {
        $this->drives = $device->storage
            ->filter(fn (StorageModel $drive): bool => $drive->isValid($device->os))
            ->map(function (StorageModel $drive) use ($device): array {
                $description = (string) $drive->storage_descr;
                if ($device->os === 'junos') {
                    $description = preg_replace('/.*mounted on: (.*)/', '$1', $description) ?? $description;
                }

                return [
                    'drive' => $drive,
                    'description' => str($description)->limit(50)->toString(),
                    'percent' => round($drive->storage_perc),
                    'total' => Number::formatBi($drive->storage_size),
                    'free' => Number::formatBi($drive->storage_free),
                    'used' => Number::formatBi($drive->storage_used),
                ];
            });
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.storage');
    }
}
