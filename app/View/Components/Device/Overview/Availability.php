<?php

namespace App\View\Components\Device\Overview;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Carbon\CarbonInterval;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Util\Time;

class Availability extends Component
{
    /**
     * @var array{days: array<int, array{date: string, availability: float, color: string, outages: array<int, string>|null}>, total: float, totalColor: string}
     */
    public array $availability;

    /**
     * Create a new component instance.
     */
    public function __construct(public readonly Device $device)
    {
        $this->availability = $this->calculateAvailability();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.device.overview.availability');
    }

    /**
     * @return array{days: array<int, array{date: string, availability: float, color: string, outages: array<int, string>|null}>, total: float, totalColor: string}
     */
    private function calculateAvailability(): array
    {
        $days = 90;
        $now = Time::now();
        $start = $now->copy()->subDays($days);
        $outages = $this->device->outages()
            ->where(fn ($query) => $query->whereNull('up_again')->orWhere('up_again', '>', $start->timestamp))
            ->orderBy('going_down')
            ->get(['going_down', 'up_again']);
        $deviceCreated = $this->device->inserted?->timestamp;
        $okThreshold = (float) LibrenmsConfig::get('availablity.threshold_ok', 99.9);
        $warningThreshold = (float) LibrenmsConfig::get('availablity.threshold_warning', 95);
        $currentDay = $start->copy()->startOfDay();
        $dayData = [];

        for ($day = 0; $day < $days; $day++) {
            $dayStart = $currentDay->timestamp;
            $dayEnd = $currentDay->addDay()->timestamp;
            $outageSeconds = 0;
            $outageLines = [];

            foreach ($outages as $outage) {
                $down = max($outage->going_down, $dayStart);
                $up = min($outage->up_again ?: $now->timestamp, $dayEnd);
                if ($up <= $down) {
                    continue;
                }

                $duration = $up - $down;
                $outageSeconds += $duration;
                $durationText = CarbonInterval::seconds($duration)->cascade()->forHumans(['short' => true, 'parts' => 2]);
                $outageLines[] = $outage->going_down >= $dayStart
                    ? __('Outage at :time · :duration', ['time' => Time::format($outage->going_down, 'time'), 'duration' => $durationText])
                    : __('Outage · :duration', ['duration' => $durationText]);
            }

            $period = min(86400, $now->timestamp - $dayStart);
            $availability = $period <= 0 ? 100.0 : max(0, min(100, 100 - ($outageSeconds / $period * 100)));
            if ($deviceCreated !== null && $dayStart < $deviceCreated) {
                $color = 'tw:bg-gray-300';
                $outageLines = null;
            } elseif ($availability >= $okThreshold) {
                $color = 'tw:bg-green-400';
            } elseif ($availability >= $warningThreshold) {
                $color = 'tw:bg-orange-400';
            } else {
                $color = 'tw:bg-red-500';
            }

            $dayData[] = [
                'date' => Time::format($dayStart, 'date'),
                'availability' => round($availability, 2),
                'color' => $color,
                'outages' => $outageLines,
            ];
        }

        $totalOutage = $outages->sum(function ($outage) use ($start, $now): int {
            $down = max($outage->going_down, $start->timestamp);
            $up = min($outage->up_again ?: $now->timestamp, $now->timestamp);

            return max(0, $up - $down);
        });
        $total = round(max(0, min(100, 100 - ($totalOutage / ($now->timestamp - $start->timestamp) * 100))), 3);

        return [
            'days' => $dayData,
            'total' => $total,
            'totalColor' => $total >= $okThreshold ? '' : ($total >= $warningThreshold ? 'tw:text-orange-400' : 'tw:text-red-500'),
        ];
    }
}
