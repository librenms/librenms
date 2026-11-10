<?php

namespace App\Graphs\Bill;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use App\Data\Graphing\GraphImage;
use App\Facades\LibrenmsConfig;
use App\Graphs\AbstractGraph;
use App\Models\Bill;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Util\Number;

/**
 * Base for bill graphs drawn from billing data with jpgraph
 */
abstract class BillGraph extends AbstractGraph
{
    private ?Bill $bill = null;

    /**
     * Build the jpgraph graph
     *
     * @throws GraphException
     */
    abstract protected function graph(): Graph;

    public function authorize(): bool
    {
        $bill = $this->bill();

        return $bill !== null && $this->canView($bill);
    }

    public function getTitle(): string
    {
        return $this->bill()->bill_name ?? 'Bill';
    }

    protected function validation(): array
    {
        return [
            'id' => 'required|integer',
            'bill_hist_id' => 'nullable|integer',
        ];
    }

    protected function draw(): GraphImage
    {
        $image = $this->graph()->Stroke(_IMG_HANDLER);

        ob_start();
        imagepng($image);

        return new GraphImage(ImageFormat::Png, $this->getTitle(), (string) ob_get_clean());
    }

    protected function bill(): ?Bill
    {
        return $this->params->id === null ? null : ($this->bill ??= Bill::find($this->params->id));
    }

    protected function histId(): ?int
    {
        return $this->params->input->int('bill_hist_id');
    }

    /**
     * Graph with the styling shared by all bill graphs
     *
     * @param  array<int, int|float>  $limits  scale limits passed to Graph::SetScale()
     */
    protected function newGraph(string $name, string $yTitle, string $scale = 'textlin', array $limits = []): Graph
    {
        $graph = new Graph($this->params->width, $this->params->height, $name);
        $graph->img->SetImgFormat('png');
        $graph->SetScale($scale, ...$limits); // creates the axes

        // work around bug in jpgraph error handling
        $graph->title->Set(' ');
        $graph->subtitle->Set(' ');
        $graph->subsubtitle->Set(' ');
        $graph->footer->left->Set(' ');
        $graph->footer->center->Set(' ');
        $graph->footer->right->Set(' ');

        $graph->SetMarginColor('white');
        $graph->SetFrame(false);
        $graph->SetMargin('75', '30', '30', '65');

        $graph->legend->SetFont(FF_FONT1, FS_NORMAL);
        $graph->legend->SetLayout(LEGEND_HOR);
        $graph->legend->Pos('0.52', '0.91', 'center');

        $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
        $graph->xaxis->SetPos('min');
        $graph->xaxis->SetTitleMargin(30);
        $graph->xaxis->title->Set(' ');

        $graph->xgrid->Show(true, true);
        $graph->xgrid->SetColor('#e0e0e0', '#efefef');

        $graph->yaxis->SetFont(FF_FONT1);
        $graph->yaxis->SetTitleMargin(50);
        $graph->yaxis->title->SetFont(FF_FONT1, FS_NORMAL, 10);
        $graph->yaxis->title->Set($yTitle);

        $graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#FFFFFF@0.5');

        return $graph;
    }

    /**
     * Graph for bytes transferred per tick
     *
     * @param  array<string, mixed>  $graph_data
     */
    protected function transferGraph(array $graph_data): Graph
    {
        $graph = $this->newGraph($graph_data['graph_name'], 'Bytes Transferred');
        $graph->title->SetFont(FF_FONT2, FS_BOLD, 10);
        $graph->xaxis->SetTickLabels($graph_data['ticklabels']);
        $graph->yaxis->SetLabelFormatCallback(fn ($value) => Number::formatBase($value, LibrenmsConfig::get('billing.base'), 1, 0));

        return $graph;
    }

    /**
     * The in, total and out bars of a transfer graph
     *
     * @param  array<string, mixed>  $graph_data
     * @return array<int, BarPlot>
     */
    protected function trafficBars(array $graph_data): array
    {
        $total = new BarPlot($graph_data['tot_data']);
        $total->SetLegend('Traffic total');
        $total->SetColor('darkgray');
        $total->SetFillColor('lightgray@0.4');
        $total->value->Show();
        $total->value->SetFormatCallback('\LibreNMS\Billing::formatBytesShort');

        $in = new BarPlot($graph_data['in_data']);
        $in->SetLegend('Traffic In');
        $in->SetColor('darkgreen');
        $in->SetFillColor('lightgreen@0.4');
        $in->SetWeight(1);

        $out = new BarPlot($graph_data['out_data']);
        $out->SetLegend('Traffic Out');
        $out->SetColor('darkblue');
        $out->SetFillColor('lightblue@0.4');
        $out->SetWeight(1);

        return [$in, $total, $out];
    }
}
