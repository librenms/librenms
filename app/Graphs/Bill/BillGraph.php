<?php

/**
 * BillGraph.php
 *
 * Base for bill graphs drawn from billing data with jpgraph
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Graphs\Bill;

use Amenadiel\JpGraph\Graph\Graph;
use App\Data\Graphing\AbstractGraph;
use App\Data\Graphing\GraphImage;
use App\Models\Bill;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\GraphException;

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

        return $bill !== null && $this->allows('view', $bill);
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
        $id = $this->vars->int('id');

        return $id === null ? null : ($this->bill ??= Bill::find($id));
    }

    protected function histId(): ?int
    {
        return $this->vars->int('bill_hist_id');
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
}
