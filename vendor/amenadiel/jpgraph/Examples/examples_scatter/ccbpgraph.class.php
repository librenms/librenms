<?php // content="text/plain; charset=utf-8"
/**
 * Class CCBPGraph
 * Utility class to create Critical Chain Buffer penetration charts
 */
class CCBPGraph
{
    const TickStep   = 25;
    const YTitle     = '% Buffer used';
    const XTitle     = '% CC Completed';
    const NColorMaps = 2;
    private $graph   = null;
    private $iWidth;
    private $iHeight;
    private $iPlots    = array();
    private $iXMin     = -50;
    private $iXMax     = 100;
    private $iYMin     = -50;
    private $iYMax     = 150;
    private $iColorInd = array(
        array(5, 75), /* Green */
        array(25, 85), /* Yellow */
        array(50, 100)); /* Red */
    private $iColorMap  = 0;
    private $iColorSpec = array(
        array('darkgreen:1.0', 'yellow:1.4', 'red:0.8', 'darkred:0.85'),
        array('#c6e9af', '#ffeeaa', '#ffaaaa', '#de8787'));
    private $iMarginColor = array('darkgreen@0.7', 'darkgreen@0.9');
    private $iSubTitle    = '';
    private $iTitle       = 'CC Buffer penetration';
    /**
     * Construct a new instance of CCBPGraph
     *
     * @param int $aWidth
     * @param int $aHeight
     * @return CCBPGraph
     */
    public function __construct($aWidth, $aHeight)
    {
        $this->iWidth  = $aWidth;
        $this->iHeight = $aHeight;
    }

    /**
     * Set the title and subtitle for the graph
     *
     * @param string $aTitle
     * @param string $aSubTitle
     */
    public function SetTitle($aTitle, $aSubTitle)
    {
        $this->iTitle    = $aTitle;
        $this->iSubTitle = $aSubTitle;
    }

    /**
     * Set the x-axis min and max values
     *
     * @param int $aMin
     * @param int $aMax
     */
    public function SetXMinMax($aMin, $aMax)
    {
        $this->iXMin = floor($aMin / CCBPGraph::TickStep) * CCBPGraph::TickStep;
        $this->iXMax = ceil($aMax / CCBPGraph::TickStep) * CCBPGraph::TickStep;
    }

    /**
     * Specify what color map to use
     *
     * @param int $aMap
     */
    public function SetColorMap($aMap)
    {
        $this->iColorMap = $aMap % CCBPGraph::NColorMaps;
    }

    /**
     * Set the y-axis min and max values
     *
     * @param int $aMin
     * @param int $aMax
     */
    public function SetYMinMax($aMin, $aMax)
    {
        $this->iYMin = floor($aMin / CCBPGraph::TickStep) * CCBPGraph::TickStep;
        $this->iYMax = ceil($aMax / CCBPGraph::TickStep) * CCBPGraph::TickStep;
    }

    /**
     * Set the specification of the color backgrounds and also the
     * optional exact colors to be used
     *
     * @param mixed $aSpec  An array of 3 1x2 arrays. Each array specify the
     * color indication value at x=0 and x=max x in order to determine the slope
     * @param mixed $aColors  An array with four elements specifying the colors
     * of each color indicator
     */
    public function SetColorIndication(array $aSpec, array $aColors = null)
    {
        if (count($aSpec) !== 3) {
            JpgraphError::Raise('Specification of scale values for background indicators must be an array with three elements.');
        }
        $this->iColorInd = $aSpec;
        if ($aColors !== null) {
            if (is_array($aColors) && count($aColors) == 4) {
                $this->iColorSpec = $aColors;
            } else {
                JpGraphError::Raise('Color specification for background indication must have four colors.');
            }
        }
    }

    /**
     * Construct the graph
     *
     */
    private function Init()
    {

        // Setup limits for color indications
        $lowx   = $this->iXMin;
        $highx  = $this->iXMax;
        $lowy   = $this->iYMin;
        $highy  = $this->iYMax;
        $width  = $this->iWidth;
        $height = $this->iHeight;

        // Margins
        $lm = 50;
        $rm = 40;
        $tm = 60;
        $bm = 40;

        if ($width <= 300 || $height <= 250) {
            $labelsize = 8;
            $lm        = 25;
            $rm        = 25;
            $tm        = 45;
            $bm        = 25;
        } elseif ($width <= 450 || $height <= 300) {
            $labelsize = 8;
            $lm        = 30;
            $rm        = 30;
            $tm        = 50;
            $bm        = 30;
        } elseif ($width <= 600 || $height <= 400) {
            $labelsize = 9;
        } else {
            $labelsize = 11;
        }

        if ($this->iSubTitle == '') {
            $tm -= $labelsize + 4;
        }

        $graph = new Graph\Graph($width, $height);
        $graph->SetScale('intint', $lowy, $highy, $lowx, $highx);
        $graph->SetMargin($lm, $rm, $tm, $bm);
        $graph->SetMarginColor($this->iMarginColor[$this->iColorMap]);
        $graph->SetClipping();

        $graph->title->Set($this->iTitle);
        $graph->subtitle->Set($this->iSubTitle);

        $graph->title->SetFont(FF_ARIAL, FS_BOLD, $labelsize + 4);
        $graph->subtitle->SetFont(FF_ARIAL, FS_BOLD, $labelsize + 1);

        $graph->SetBox(true, 'black@0.3');

        $graph->xaxis->SetFont(FF_ARIAL, FS_BOLD, $labelsize);
        $graph->yaxis->SetFont(FF_ARIAL, FS_BOLD, $labelsize);

        $graph->xaxis->scale->ticks->Set(CCBPGraph::TickStep, CCBPGraph::TickStep);
        $graph->yaxis->scale->ticks->Set(CCBPGraph::TickStep, CCBPGraph::TickStep);

        $graph->xaxis->HideZeroLabel();
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetLabelFormatString('%d%%');
        $graph->yaxis->SetLabelFormatString('%d%%');

        // For the x-axis we adjust the color so labels on the left of the Y-axis are in black
        $n1 = floor(abs($this->iXMin / 25)) + 1;
        $n2 = floor($this->iXMax / 25);
        if ($this->iColorMap == 0) {
            $xlcolors = array();
            for ($i = 0; $i < $n1; ++$i) {
                $xlcolors[$i] = 'black';
            }
            for ($i = 0; $i < $n2; ++$i) {
                $xlcolors[$n1 + $i] = 'lightgray:1.5';
            }
            $graph->xaxis->SetColor('gray', $xlcolors);
            $graph->yaxis->SetColor('gray', 'lightgray:1.5');
        } else {
            $graph->xaxis->SetColor('darkgray', 'darkgray:0.8');
            $graph->yaxis->SetColor('darkgray', 'darkgray:0.8');
        }
        $graph->SetGridDepth(DEPTH_FRONT);
        $graph->ygrid->SetColor('gray@0.6');
        $graph->ygrid->SetLineStyle('dotted');

        $graph->ygrid->Show();

        $graph->xaxis->SetWeight(1);
        $graph->yaxis->SetWeight(1);

        $ytitle = new Text(CCBPGraph::YTitle, floor($lm * .75), ($height - $tm - $bm) / 2 + $tm);
        #$ytitle->SetFont(FF_VERA,FS_BOLD,$labelsize+1);
        $ytitle->SetAlign('right', 'center');
        $ytitle->SetAngle(90);
        $graph->Add($ytitle);

        $xtitle = new Text(CCBPGraph::XTitle, ($width - $lm - $rm) / 2 + $lm, $height - 10);
        #$xtitle->SetFont(FF_VERA,FS_BOLD,$labelsize);
        $xtitle->SetAlign('center', 'bottom');
        $graph->Add($xtitle);

        $df = 'D j:S M, Y';
        if ($width < 400) {
            $df = 'D j:S M';
        }

        $time = new Text(date($df), $width - 10, $height - 10);
        $time->SetAlign('right', 'bottom');
        #$time->SetFont(FF_VERA,FS_NORMAL,$labelsize-1);
        $time->SetColor('darkgray');
        $graph->Add($time);

        // Use an accumulated fille line graph to create the colored bands

        $n = 3;
        for ($i = 0; $i < $n; ++$i) {
            $b           = $this->iColorInd[$i][0];
            $k           = ($this->iColorInd[$i][1] - $this->iColorInd[$i][0]) / $this->iXMax;
            $colarea[$i] = array(array($lowx, $lowx * $k + $b), array($highx, $highx * $k + $b));
        }
        $colarea[3] = array(array($lowx, $highy), array($highx, $highy));

        $cb = array();
        for ($i = 0; $i < 4; ++$i) {
            $cb[$i] = new Plot\LinePlot(array($colarea[$i][0][1], $colarea[$i][1][1]),
                array($colarea[$i][0][0], $colarea[$i][1][0]));
            $cb[$i]->SetFillColor($this->iColorSpec[$this->iColorMap][$i]);
            $cb[$i]->SetFillFromYMin();
        }

        $graph->Add(array_slice(array_reverse($cb), 0, 4));
        $this->graph = $graph;
    }

    /**
     * Add a line or scatter plot to the graph
     *
     * @param mixed $aPlots
     */
    public function Add($aPlots)
    {
        if (is_array($aPlots)) {
            $this->iPlots = array_merge($this->iPlots, $aPlots);
        } else {
            $this->iPlots[] = $aPlots;
        }
    }

    /**
     * Stroke the graph back to the client or to a file
     *
     * @param mixed $aFile
     */
    public function Stroke($aFile = '')
    {
        $this->Init();
        if (count($this->iPlots) > 0) {
            $this->graph->Add($this->iPlots);
        }
        $this->graph->Stroke($aFile);
    }
}
