<?php

namespace App\Graphs\Bill;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use App\Models\BillHistory;
use Carbon\Carbon;
use LibreNMS\Billing;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Util\Number;

class HistoricbitsGraph extends BillGraph
{
    protected function validation(): array
    {
        return [
            ...parent::validation(),
            'reducefactor' => 'nullable|integer',
        ];
    }

    protected function graph(): Graph
    {
        $bill_id = $this->bill()->bill_id;
        $hist_id = $this->histId();

        if ($hist_id !== null) {
            $history = BillHistory::where('bill_id', $bill_id)->find($hist_id);
            if ($history === null) {
                throw new GraphException('No Data', 'No Data');
            }

            $dur = Carbon::parse($history->bill_dateto)->timestamp - Carbon::parse($history->bill_datefrom)->timestamp;
            $graph_data = Billing::getHistoryBitsGraphData($bill_id, $hist_id, $this->reduceFactor($dur));
        } else {
            $dur = $this->params->period;
            $graph_data = Billing::getBitsGraphData($bill_id, $this->params->from, $this->params->to, $this->reduceFactor($dur));
        }

        if (empty($graph_data['ticks'])) {
            throw new GraphException('No Data', 'No Data');
        }

        $ticks = $graph_data['ticks'];
        $xmin = $ticks[0];
        $xmax = $ticks[count($ticks) - 1];

        $graph = $this->newGraph($graph_data['graph_name'] ?? 'Bill Graph', 'Bits per second', 'datlin', [0, 0, $graph_data['from'], $graph_data['to']]);
        $graph->legend->Pos(0.52, 0.90, 'center');
        $graph->xaxis->SetTextLabelInterval(2);
        $graph->xaxis->SetLabelFormatCallback(function ($aVal) use ($dur) {
            if ($dur < 172800) {
                return date('H:i', $aVal);
            } elseif ($dur < 604800) {
                return date('D', $aVal);
            }

            return date('j M', $aVal);
        });
        $graph->yaxis->SetLabelFormatCallback(fn ($y) => Number::formatSi($y, 0, 0, ''));
        $graph->yaxis->HideZeroLabel(1);

        $lineplot = new LinePlot($graph_data['tot_data'], $ticks);
        $lineplot->SetLegend('Traffic total');
        $lineplot->SetColor('#d5d5d5');
        $lineplot->SetFillColor('#d5d5d5@0.5');

        $lineplot_in = new LinePlot($graph_data['in_data'], $ticks);
        $lineplot_in->SetLegend('Traffic In');
        $lineplot_in->SetColor('darkgreen');
        $lineplot_in->SetFillColor('lightgreen@0.4');
        $lineplot_in->SetWeight(1);

        $lineplot_out = new LinePlot(array_map(fn ($x) => $x * -1, $graph_data['out_data']), $ticks);
        $lineplot_out->SetLegend('Traffic Out');
        $lineplot_out->SetColor('darkblue');
        $lineplot_out->SetFillColor('lightblue@0.4');
        $lineplot_out->SetWeight(1);

        $graph->Add($lineplot);
        $graph->Add($lineplot_in);
        $graph->Add($lineplot_out);

        $bill_type = strtolower((string) $graph_data['bill_type']);
        if ($bill_type == 'cdr') {
            $lineplot_95th = new LinePlot([$graph_data['rate_95th'], $graph_data['rate_95th']], [$xmin, $xmax]);
            $lineplot_95th->SetColor('red');
            $graph->Add($lineplot_95th);
        } elseif ($bill_type == 'quota') {
            $lineplot_ave = new LinePlot([$graph_data['rate_average'], $graph_data['rate_average']], [$xmin, $xmax]);
            $lineplot_ave->SetColor('red');
            $graph->Add($lineplot_ave);
        }

        return $graph;
    }

    /**
     * Combine data points so there are about three per pixel
     */
    private function reduceFactor(int $duration): int
    {
        $reducefactor = $this->vars->int('reducefactor') ?? 0;

        if ($reducefactor >= 2) {
            return $reducefactor;
        }

        return max(2, (int) round($duration / 300 / (max(1, $this->params->height - 100) * 3)));
    }
}
