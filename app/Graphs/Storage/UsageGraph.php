<?php

namespace App\Graphs\Storage;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\ThemeColor;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Storage;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Util\Color;

/**
 * Used and free space stacked up to the size of the storage
 */
class UsageGraph extends AbstractSeriesGraph
{
    private ?Storage $storage = null;

    protected function validation(): array
    {
        return ['id' => 'required|integer'];
    }

    protected function isAuthorized(): bool
    {
        $storage = $this->findStorage();

        return $storage !== null && $this->canView($storage);
    }

    public function getPageContext(): GraphPageContext
    {
        $storage = $this->findStorage();

        return new GraphPageContext($storage?->device, subtitle: $storage ? __('graph.storage.subtitle', ['descr' => $storage->storage_descr]) : null);
    }

    public function getTitle(): string
    {
        $storage = $this->storage();

        return $storage->device?->shortDisplayName() . '::' . $storage->storage_descr;
    }

    public function getSeries(): array
    {
        $storage = $this->storage();
        $metric = $storage->metricIdentity('storage');
        // percentages are stored multiplied by the storage units
        [$unit, $scale] = $storage->isPercentOnly() ? ['%', 1 / ($storage->storage_units ?: 1)] : ['B', 1];

        return [
            'used' => new DataSeries($metric, 'used', __('graph.series.used'), $unit, $scale,
                new SeriesStyle(color: Color::percentage($storage->storage_perc ?? 0, $storage->storage_perc_warn)['middle'], stacked: true)),
            'free' => new DataSeries($metric, 'free', __('graph.series.free'), $unit, $scale,
                new SeriesStyle(color: new ThemeColor('D0D0D0', '3E444C'), stacked: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        $storage = $this->storage();

        return $storage->isPercentOnly()
            ? new GraphOptions(scaleMin: 0, scaleMax: 100, legendTitle: $storage->storage_descr)
            : new GraphOptions(scaleMin: 0, base: 1024, legendTitle: $storage->storage_descr);
    }

    /**
     * @throws NoGraphData
     */
    private function storage(): Storage
    {
        return $this->findStorage() ?? throw new NoGraphData;
    }

    private function findStorage(): ?Storage
    {
        return $this->params->id === null ? null : ($this->storage ??= Storage::with('device')->find($this->params->id));
    }
}
