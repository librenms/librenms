<?php
namespace Amenadiel\JpGraph\Themes;

/**
 * Softy Theme class
 */
class SoftyTheme extends Theme
{
    protected $font_color       = '#000000';
    protected $background_color = '#F7F8F4';
    protected $axis_color       = '#000000';
    protected $grid_color       = '#CCCCCC';

    public function GetColorList()
    {
        return array(
            '#CFE7FB',
            '#F9D76F',
            '#B9D566',
            '#FFBB90',
            '#66BBBB',
            '#E69090',
            '#BB90BB',
            '#9AB67C',
            '#D1CC66',

/*

'#AFD8F8',
'#F6BD0F',
'#8BBA00',
'#FF8E46',
'#008E8E',

'#D64646',
'#8E468E',
'#588526',
'#B3AA00',
'#008ED6',

'#9D080D',
'#A186BE',
 */
        );
    }

    public function SetupGraph($graph)
    {

        // graph
        $graph->SetFrame(false);
        $graph->SetMarginColor('white');

        // legend
        $graph->legend->SetFrameWeight(0);
        $graph->legend->Pos(0.5, 0.85, 'center', 'top');
        $graph->legend->SetFillColor('white');
        $graph->legend->SetLayout(LEGEND_HOR);
        $graph->legend->SetColumns(3);
        $graph->legend->SetShadow(false);
        $graph->legend->SetMarkAbsSize(5);

        // xaxis
        $graph->xaxis->title->SetColor($this->font_color);
        $graph->xaxis->SetColor($this->axis_color, $this->font_color);
        $graph->xaxis->SetTickSide(SIDE_BOTTOM);
        $graph->xaxis->SetLabelMargin(10);

        // yaxis
        $graph->yaxis->title->SetColor($this->font_color);
        $graph->yaxis->SetColor($this->axis_color, $this->font_color);
        $graph->yaxis->SetTickSide(SIDE_LEFT);
        $graph->yaxis->SetLabelMargin(8);
        $graph->yaxis->HideLine();
        $graph->yaxis->HideTicks();
        $graph->xaxis->SetTitleMargin(15);

        // y2~
        if (isset($graph->y2axis)) {
            $graph->y2axis->title->SetColor($this->font_color);
            $graph->y2axis->SetColor($this->axis_color, $this->font_color);
            $graph->y2axis->SetTickSide(SIDE_LEFT);
            $graph->y2axis->SetLabelMargin(8);
            $graph->y2axis->HideLine();
            $graph->y2axis->HideTicks();
        }

        // yn
        if (isset($graph->y2axis)) {
            foreach ($graph->ynaxis as $axis) {
                $axis->title->SetColor($this->font_color);
                $axis->SetColor($this->axis_color, $this->font_color);
                $axis->SetTickSide(SIDE_LEFT);
                $axis->SetLabelMargin(8);
                $axis->HideLine();
                $axis->HideTicks();
            }
        }

        // grid
        $graph->ygrid->SetColor($this->grid_color);
        $graph->ygrid->SetLineStyle('dotted');
        $graph->ygrid->SetFill(true, '#FFFFFF', $this->background_color);
        $graph->xgrid->Show();
        $graph->xgrid->SetColor($this->grid_color);
        $graph->xgrid->SetLineStyle('dotted');

        // font
        $graph->title->SetColor($this->font_color);
        $graph->subtitle->SetColor($this->font_color);
        $graph->subsubtitle->SetColor($this->font_color);

        //        $graph->img->SetAntiAliasing();
    }

    public function SetupPieGraph($graph)
    {

        // graph
        $graph->SetFrame(false);

        // title
        $graph->title->SetColor($this->font_color);
        $graph->subtitle->SetColor($this->font_color);
        $graph->subsubtitle->SetColor($this->font_color);

        $graph->SetAntiAliasing();
    }

    public function PreStrokeApply($graph)
    {
        if ($graph->legend->HasItems()) {
            $img = $graph->img;
            $graph->SetMargin($img->left_margin, $img->right_margin, $img->top_margin, $img->height * 0.25);
            //            $graph->SetMargin(200, $img->right_margin, $img->top_margin, $height * 0.25);
        }
    }

    public function ApplyPlot($plot)
    {
        switch (get_class($plot)) {
            case 'BarPlot':
                {
                    $plot->Clear();

                    $color = $this->GetNextColor();
                    $plot->SetColor($color);
                    $plot->SetFillColor($color);
                    $plot->SetShadow('red', 3, 4, false);
                    $plot->value->SetAlign('center', 'center');
                    break;
                }

            case 'LinePlot':
                {
                    $plot->Clear();

                    $plot->SetColor($this->GetNextColor());
                    $plot->SetWeight(2);
                    //                $plot->SetBarCenter();
                    break;
                }

            case 'PiePlot':
                {
                    $plot->ShowBorder(false);
                    $plot->SetSliceColors($this->GetThemeColors());
                    break;
                }

            case 'GroupBarPlot':
                {
                    foreach ($plot->plots as $_plot) {
                        $this->ApplyPlot($_plot);
                    }
                    break;
                }

            case 'AccBarPlot':
                {
                    $plot->value->SetAlign('center', 'center');
                    foreach ($plot->plots as $_plot) {
                        $this->ApplyPlot($_plot);
                        $_plot->SetValuePos('center');
                    }
                    break;
                }

            case 'ScatterPlot':
                {
                    break;
                }

            case 'PiePlot3D':
                {
                    $plot->SetSliceColors($this->GetThemeColors());
                    break;
                }

            default:
                {
                }
        }
    }
}
