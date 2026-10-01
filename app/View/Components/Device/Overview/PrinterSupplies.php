<?php

namespace App\View\Components\Device\Overview;

use App\Models\Device;
use App\Models\PrinterSupply;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use LibreNMS\Util\Color;

class PrinterSupplies extends Component
{
    /**
     * @var array<string, array<int, array{supply: \App\Models\PrinterSupply, percent: float, colors: array<string, string>}>>
     */
    public array $supplyGroups;

    public function __construct(public Device $device)
    {
        $this->supplyGroups = $device->printerSupplies
            ->groupBy('supply_type')
            ->map(fn (Collection $supplies): Collection => $supplies->map(function (PrinterSupply $supply): array {
                $percent = round($supply->supply_current);

                return [
                    'supply' => $supply,
                    'percent' => $percent,
                    'colors' => $this->colors((string) $supply->supply_descr, $percent),
                ];
            })->values())->map->all()->all();
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.printer-supplies');
    }

    /**
     * @return array<string, string>
     */
    private function colors(string $description, float $percent): array
    {
        $colors = Color::percentage(100 - $percent, null, '#');
        $lowerDescription = strtolower($description);

        if (str_ends_with($description, 'C') || str_contains($lowerDescription, 'cyan')) {
            $colors = [...$colors, 'left' => '#55D6D3', 'right' => '#33B4B1'];
        }
        if (str_ends_with($description, 'M') || str_contains($lowerDescription, 'magenta')) {
            $colors = [...$colors, 'left' => '#F24AC8', 'right' => '#D028A6'];
        }
        if (str_ends_with($description, 'Y') || str_contains($lowerDescription, 'yellow') || str_contains($lowerDescription, 'giallo') || str_contains($lowerDescription, 'gul')) {
            $colors = [...$colors, 'left' => '#FFF200', 'right' => '#DDD000'];
        }
        if (str_ends_with($description, 'K') || str_contains($lowerDescription, 'black') || str_contains($lowerDescription, 'nero')) {
            $colors = [...$colors, 'left' => '#000000', 'right' => '#222222'];
        }

        return $colors;
    }
}
