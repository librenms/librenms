<?php

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Number;

class ServerStatsController extends WidgetController
{
    protected string $name = 'server-stats';
    protected $defaults = [
        'title' => null,
        'columnsize' => 3,
        'device' => null,
        'cpu' => 0,
        'mempools' => [],
        'disks' => [],
        'unit' => 'AUTO',
    ];

    public function getTitle(): string
    {
        $settings = $this->getSettings();
        if ($settings['title']) {
            return $settings['title'];
        }

        $device = Device::hasAccess(request()->user())->find($settings['device']);
        if ($device) {
            return $device->displayName() . ' Stats';
        }

        return parent::getTitle();
    }

    public function getView(Request $request): \Illuminate\View\View|string
    {
        $data = $this->getSettings();
        $requestedUnit = strtoupper($data['unit'] ?? 'AUTO');

        if (is_null($data['device'])) {
            return $this->getSettingsView($request);
        }

        $device = Device::hasAccess($request->user())->find($data['device']);
        if ($device) {
            
            $data['cpu'] = round((float) $device->processors()->avg('processor_usage'), 1);

           
            $mempools = $device->mempools()->get(['mempool_descr', 'mempool_used', 'mempool_total']);
            $disks = $device->storage()->get(['storage_descr', 'storage_used', 'storage_size', 'storage_units']);

            $disksFormatted = $disks->map(function ($d) {
                $units = (float) ($d->storage_units ?? 1);
                return (object) [
                    'storage_descr' => $d->storage_descr,
                    'bytes_used'    => (float) $d->storage_used * $units,
                    'bytes_total'   => (float) $d->storage_size * $units,
                ];
            });

            
            $maxMem = (float) ($mempools->max('mempool_total') ?? 0);
            $maxDisk = (float) ($disksFormatted->isEmpty() ? 0 : ($disksFormatted->max('bytes_total') ?? 0));
            $maxValue = max($maxMem, $maxDisk);

            if ($maxValue <= 0) {
                $maxValue = 1073741824; 
            }

            
            [$factor, $displayUnit] = $this->resolveUnit($requestedUnit, $maxValue);
            $data['unit'] = $displayUnit;

            
            $data['mempools'] = $mempools->map(function ($m) use ($factor) {
                return (object) [
                    'descr' => $m->mempool_descr ?? 'Memory',
                    'used'  => round(((float) $m->mempool_used) / $factor, 2),
                    'total' => round(((float) $m->mempool_total) / $factor, 2),
                ];
            });

            
            $data['disks'] = $disksFormatted->map(function ($d) use ($factor) {
                return (object) [
                    'descr' => $d->storage_descr ?? 'Storage',
                    'used'  => round($d->bytes_used / $factor, 2),
                    'total' => round($d->bytes_total / $factor, 2),
                ];
            });
        }

        return view('widgets.server-stats', $data);
    }

    public function getSettingsView(Request $request): View
    {
        $settings = $this->getSettings(true);
        $settings['device'] = Device::hasAccess($request->user())->find($settings['device']) ?: null;
        $settings['unit'] = strtoupper($settings['unit'] ?? 'AUTO');

        return view('widgets.settings.server-stats', $settings);
    }

    public function getSettings($settingsView = false): array
    {
        $settings = parent::getSettings($settingsView);
        $settings['columns'] = 12 / ($settings['columnsize'] ?? 3);

        return $settings;
    }

    private function resolveUnit(string $unit, float $maxValue): array
    {
        if ($unit === 'AUTO') {
            return $this->scaleSI($maxValue);
        }

        switch ($unit) {
            case 'TIB':
            case 'TB':
                return [1000 ** 4, 'TB'];
            case 'GIB':
            case 'GB':
                return [1000 ** 3, 'GB'];
            default:
                return [1000 ** 2, 'MB'];
        }
    }
    
    private function scaleSI(float $bytes): array
    {
        $formatted = Number::formatSi($bytes, 2, 0, 'B');
        
        if (preg_match('/([\d\.]+)\s*([a-zA-Z]+)/', $formatted, $matches)) {
            $unitLabel = $matches[2];
        } else {
            $unitLabel = 'GB';
        }

        $siFactors = [
            'B'  => 1,
            'kB' => 1000,
            'MB' => 1000 ** 2,
            'GB' => 1000 ** 3,
            'TB' => 1000 ** 4,
            'PB' => 1000 ** 5,
        ];

        $factor = $siFactors[$unitLabel] ?? (1000 ** 3);

        return [$factor, $unitLabel];
    }
}
