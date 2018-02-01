<?php
namespace Amenadiel\JpGraph\Themes;

/**
 * Orange Theme class
 */
class OrangeTheme extends Theme
{
    private $font_color       = '#CC4400';
    private $background_color = '#FFEEDD';
    private $axis_color       = '#CC6600';
    private $grid_color       = '#CC6633';

    public function GetColorList()
    {
        return array(
            '#FF9900',
            '#FFCC00',
            '#AA6600',
            '#CCCC00',
            '#CC6600',
            '#FFFF66',
            '#CCFF00',
            '#CC3300',
            '#669933',
            '#EE7700',
            '#AAEE33',
            '#77AA00',
            '#CCFF99',
            '#FF6633',
            '#885500',
            '#AADD00',
            '#99CC44',
            '#887711',
        );
    }

    public function SetupGraph($graph)
    {

        // graph
        /*
        $img = $graph->img;
        $height = $img->height;
        $graph->SetMargin($img->left_margin, $img->right_margin, $img->top_margin, $height * 0.25);
         */
        $graph->SetFrame(false);
        $graph->SetMarginColor('white');
        $graph->SetBackgroundGradient($this->background_color, '#FFFFFF', GRAD_HOR, BGRAD_PLOT);

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

        // grid
        $graph->ygrid->SetColor($this->grid_color);
        $graph->ygrid->SetLineStyle('dotted');

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

        // legend
        $graph->legend->SetFillColor('white');
        /*
        $graph->legend->SetFrameWeight(0);
        $graph->legend->Pos(0.5, 0.85, 'center', 'top');
        $graph->legend->SetLayout(LEGEND_HOR);
        $graph->legend->SetColumns(3);
         */
        $graph->legend->SetShadow(false);
        $graph->legend->SetMarkAbsSize(5);

        // title
        $graph->title->SetColor($this->font_color);
        $graph->subtitle->SetColor($this->font_color);
        $graph->subsubtitle->SetColor($this->font_color);

        $graph->SetAntiAliasing();
    }

    public function PreStrokeApply($graph)
    {
        if ($graph->legend->HasItems()) {
            $img    = $graph->img;
            $height = $img->height;
            $graph->SetMargin($img->left_margin, $img->right_margin, $img->top_margin, $height * 0.25);
        }
    }

    public function ApplyPlot($plot)
    {
        switch (get_class($plot)) {
            case 'GroupBarPlot':
                {
                    foreach ($plot->plots as $_plot) {
                        $this->ApplyPlot($_plot);
                    }
                    break;
                }

            case 'AccBarPlot':
                {
                    foreach ($plot->plots as $_plot) {
                        $this->ApplyPlot($_plot);
                    }
                    break;
                }

            case 'BarPlot':
                {
                    $plot->Clear();

                    $color = $this->GetNextColor();
                    $plot->SetColor($color);
                    $plot->SetFillColor($color);
                    $plot->SetShadow('red', 3, 4, false);
                    break;
                }

            case 'LinePlot':
                {
                    $plot->Clear();

                    $plot->SetColor($this->GetNextColor() . '@0.4');
                    $plot->SetWeight(2);
                    break;
                }

            case 'PiePlot':
                {
                    $plot->ShowBorder(false);
                    $plot->SetSliceColors($this->GetThemeColors());
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
