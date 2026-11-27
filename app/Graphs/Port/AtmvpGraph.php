<?php

namespace App\Graphs\Port;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Models\JuniAtmVp;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph of one JunOSe ATM virtual path of the port, chosen by the vp input
 */
abstract class AtmvpGraph extends PortGraph
{
    private ?JuniAtmVp $vp = null;

    protected function validation(): array
    {
        return ['vp' => 'required|integer'];
    }

    protected function isAuthorized(): bool
    {
        return parent::isAuthorized() && $this->findVp() !== null;
    }

    public function getPageContext(): GraphPageContext
    {
        $context = parent::getPageContext();
        $vpId = $this->params->input->int('vp');

        return new GraphPageContext($context->device, $context->port, $vpId === null ? null : __('graph.port.atmvp_subtitle', ['vp' => $vpId]));
    }

    public function getTitle(): string
    {
        return parent::getTitle() . '::vp' . $this->vp()->vp_id;
    }

    /**
     * The in and out series of a pair of fields of the virtual path
     *
     * @return array{in: DataSeries, out: DataSeries}
     *
     * @throws NoGraphData
     */
    protected function duplex(string $in, string $out, string $unit, SeriesRole $inRole, SeriesRole $outRole, float $scale = 1, bool $percentileLines = false): array
    {
        $metric = $this->vp()->metricIdentity('atm_vp');
        [$in, $out] = $this->directions($in, $out);

        return [
            'in' => new DataSeries($metric, $in, __('graph.series.in'), $unit, $scale,
                new SeriesStyle($inRole, fill: true, maxFill: true, percentileLines: $percentileLines)),
            'out' => new DataSeries($metric, $out, __('graph.series.out'), $unit, $scale,
                new SeriesStyle($outRole, fill: true, maxFill: true, invert: true, percentileLines: $percentileLines)),
        ];
    }

    /**
     * @throws NoGraphData
     */
    protected function vp(): JuniAtmVp
    {
        return $this->findVp() ?? throw new NoGraphData;
    }

    private function findVp(): ?JuniAtmVp
    {
        $port = $this->requestedPort();
        $vpId = $this->params->input->int('vp');

        if ($port === null || $vpId === null) {
            return null;
        }

        return $this->vp ??= JuniAtmVp::where('port_id', $port->port_id)->where('vp_id', $vpId)->first()?->setRelation('port', $port);
    }
}
