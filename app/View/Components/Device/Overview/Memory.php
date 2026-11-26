<?php

namespace App\View\Components\Device\Overview;

use App\Models\Device;
use App\Models\Mempool;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Util\Number;

class Memory extends Component
{
    /**
     * @var array<int, array{mempool: \App\Models\Mempool, total: string, used: string, free: string, percent: float, shadow: ?float, leftText: string, rightText: string}>
     */
    public array $mempools;

    public function __construct(public Device $device)
    {
        $this->mempools = $device->mempools->map(function (Mempool $mempool) use ($device): array {
            $isPercent = $mempool->mempool_total == 100;
            $suffix = $isPercent ? '%' : 'B';
            $total = Number::formatBi($mempool->mempool_total, suffix: $suffix);
            $used = Number::formatBi($mempool->mempool_used, suffix: $suffix);
            $free = Number::formatBi($mempool->mempool_free, suffix: $suffix);
            $percent = (float) $mempool->mempool_perc;
            $shadow = null;

            if ($mempool->mempool_class === 'system' && $device->mempools->count() > 1) {
                $buffers = $device->mempools->where('mempool_class', 'buffers')->sum('mempool_used');
                $cached = $device->mempools->where('mempool_class', 'cached')->sum('mempool_used');
                $shadow = Number::calculatePercent($mempool->mempool_used + $buffers + $cached, $mempool->mempool_total, 0);
            }

            [$leftText, $rightText] = match (true) {
                $isPercent => ["$percent%", ''],
                in_array($mempool->mempool_class, ['system', 'virtual', 'swap'], true) => ["$used / $total ($percent%)", $free],
                default => ["$used ($percent%)", ''],
            };

            return compact('mempool', 'total', 'used', 'free', 'percent', 'shadow', 'leftText', 'rightText');
        })->values()->all();
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.memory');
    }
}
