<?php
namespace Amenadiel\JpGraph\Graph;

class PolarLogScale extends LogScale
{
    private $graph;
    public $clockwise = false;

    public function __construct($aMax, $graph, $aClockwise = false)
    {
        parent::__construct(0, $aMax, 'x');
        $this->graph = $graph;
        $this->ticks->SetLabelLogType(LOGLABELS_MAGNITUDE);
        $this->clockwise = $aClockwise;
    }

    public function SetClockwise($aFlg)
    {
        $this->clockwise = $aFlg;
    }

    public function PTranslate($aAngle, $aRad)
    {
        if ($aRad == 0) {
            $aRad = 1;
        }

        $aRad = log10($aRad);
        $m    = $this->scale[1];
        $w    = $this->graph->img->plotwidth / 2;
        $aRad = $aRad / $m * $w;

        $a = $aAngle / 180 * M_PI;
        if ($this->clockwise) {
            $a = 2 * M_PI - $a;
        }

        $x = cos($a) * $aRad;
        $y = sin($a) * $aRad;

        $x += $w + $this->graph->img->left_margin; //$this->_Translate(0);
        if ($this->graph->iType == POLAR_360) {
            $y = ($this->graph->img->top_margin + $this->graph->img->plotheight / 2) - $y;
        } else {
            $y = ($this->graph->img->top_margin + $this->graph->img->plotheight) - $y;
        }
        return [$x, $y];
    }
}
