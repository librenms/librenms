<?php

namespace App\Graphs\Mempool;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Mempool;
use LibreNMS\Exceptions\NoGraphData;

/**
 * Used and free memory stacked up to the size of the memory pool
 */
class UsageGraph extends AbstractSeriesGraph
{
    private ?Mempool $mempool = null;

    protected function validation(): array
    {
        return ['id' => 'required|integer'];
    }

    protected function isAuthorized(): bool
    {
        $mempool = $this->findMempool();

        return $mempool !== null && $this->canView($mempool);
    }

    public function getPageContext(): GraphPageContext
    {
        $mempool = $this->findMempool();

        return new GraphPageContext($mempool?->device, subtitle: $mempool ? __('graph.mempool.subtitle', ['descr' => $mempool->mempool_descr]) : null);
    }

    public function getTitle(): string
    {
        $mempool = $this->mempool();

        return $mempool->device?->shortDisplayName() . '::' . $mempool->mempool_descr;
    }

    public function getSeries(): array
    {
        $mempool = $this->mempool();
        $metric = $mempool->metricIdentity('mempool');
        $unit = $mempool->isPercentOnly() ? '%' : 'B';

        return [
            'used' => new DataSeries($metric, 'used', __('graph.series.used'), $unit,
                style: new SeriesStyle(SeriesRole::usage($mempool->mempool_perc ?? 0, $mempool->mempool_perc_warn), stacked: true)),
            'free' => new DataSeries($metric, 'free', __('graph.series.free'), $unit,
                style: new SeriesStyle(SeriesRole::Muted, stacked: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        $mempool = $this->mempool();

        return $mempool->isPercentOnly()
            ? new GraphOptions(scaleMin: 0, scaleMax: 100, legendTitle: $mempool->mempool_descr)
            : new GraphOptions(scaleMin: 0, base: 1024, legendTitle: $mempool->mempool_descr);
    }

    /**
     * @throws NoGraphData
     */
    private function mempool(): Mempool
    {
        return $this->findMempool() ?? throw new NoGraphData;
    }

    private function findMempool(): ?Mempool
    {
        return $this->params->id === null ? null : ($this->mempool ??= Mempool::with('device')->find($this->params->id));
    }
}
