<?php
namespace Amenadiel\JpGraph\Plot;

/**
 * This class represent a plotting of a contour outline of data given as a X-Y matrice
 *
 */
class ContourPlot extends Plot
{

    private $contour, $contourCoord, $contourVal, $contourColor;
    private $nbrCountours = 0;
    private $dataMatrix = array();
    private $invertLegend = false;
    private $interpFactor = 1;
    private $flipData = false;
    private $isobar = 10;
    private $showLegend = false;
    private $highcontrast = false, $highcontrastbw = false;
    private $manualIsobarColors = array();

    /**
     * Construct a contour plotting algorithm. The end result of the algorithm is a sequence of
     * line segments for each isobar given as two vertices.
     *
     * @param $aDataMatrix    The Z-data to be used
     * @param $aIsobar A mixed variable, if it is an integer then this specified the number of isobars to use.
     * The values of the isobars are automatically detrmined to be equ-spaced between the min/max value of the
     * data. If it is an array then it explicetely gives the isobar values
     * @param $aInvert By default the matrice with row index 0 corresponds to Y-value 0, i.e. in the bottom of
     * the plot. If this argument is true then the row with the highest index in the matrice corresponds  to
     * Y-value 0. In affect flipping the matrice around an imaginary horizontal axis.
     * @param $aHighContrast Use high contrast colors (blue/red:ish)
     * @param $aHighContrastBW Use only black colors for contours
     * @return an instance of the contour plot algorithm
     */
    public function __construct($aDataMatrix, $aIsobar = 10, $aFactor = 1, $aInvert = false, $aIsobarColors = array())
    {

        $this->dataMatrix = $aDataMatrix;
        $this->flipData = $aInvert;
        $this->isobar = $aIsobar;
        $this->interpFactor = $aFactor;

        if ($this->interpFactor > 1) {

            if ($this->interpFactor > 5) {
                Util\JpGraphError::RaiseL(28007); // ContourPlot interpolation factor is too large (>5)
            }

            $ip = new MeshInterpolate();
            $this->dataMatrix = $ip->Linear($this->dataMatrix, $this->interpFactor);
        }

        $this->contour = new Contour($this->dataMatrix, $this->isobar, $aIsobarColors);

        if (is_array($aIsobar)) {
            $this->nbrContours = count($aIsobar);
        } else {
            $this->nbrContours = $aIsobar;
        }

    }

    /**
     * Flipe the data around the center
     *
     * @param $aFlg
     *
     */
    public function SetInvert($aFlg = true)
    {
        $this->flipData = $aFlg;
    }

    /**
     * Set the colors for the isobar lines
     *
     * @param $aColorArray
     *
     */
    public function SetIsobarColors($aColorArray)
    {
        $this->manualIsobarColors = $aColorArray;
    }

    /**
     * Show the legend
     *
     * @param $aFlg true if the legend should be shown
     *
     */
    public function ShowLegend($aFlg = true)
    {
        $this->showLegend = $aFlg;
    }

    /**
     * @param $aFlg true if the legend should start with the lowest isobar on top
     * @return unknown_type
     */
    public function Invertlegend($aFlg = true)
    {
        $this->invertLegend = $aFlg;
    }

    /* Internal method. Give the min value to be used for the scaling
     *
     */
    public function Min()
    {
        return array(0, 0);
    }

    /* Internal method. Give the max value to be used for the scaling
     *
     */
    public function Max()
    {
        return array(count($this->dataMatrix[0]) - 1, count($this->dataMatrix) - 1);
    }

    /**
     * Internal ramewrok method to setup the legend to be used for this plot.
     * @param $aGraph The parent graph class
     */
    public function Legend($aGraph)
    {

        if (!$this->showLegend) {
            return;
        }

        if ($this->invertLegend) {
            for ($i = 0; $i < $this->nbrContours; $i++) {
                $aGraph->legend->Add(sprintf('%.1f', $this->contourVal[$i]), $this->contourColor[$i]);
            }
        } else {
            for ($i = $this->nbrContours - 1; $i >= 0; $i--) {
                $aGraph->legend->Add(sprintf('%.1f', $this->contourVal[$i]), $this->contourColor[$i]);
            }
        }
    }

    /**
     *  Framework function which gets called before the Stroke() method is called
     *
     *  @see Plot#PreScaleSetup($aGraph)
     *
     */
    public function PreScaleSetup($aGraph)
    {
        $xn = count($this->dataMatrix[0]) - 1;
        $yn = count($this->dataMatrix) - 1;

        $aGraph->xaxis->scale->Update($aGraph->img, 0, $xn);
        $aGraph->yaxis->scale->Update($aGraph->img, 0, $yn);

        $this->contour->SetInvert($this->flipData);
        list($this->contourCoord, $this->contourVal, $this->contourColor) = $this->contour->getIsobars();
    }

    /**
     * Use high contrast color schema
     *
     * @param $aFlg True, to use high contrast color
     * @param $aBW True, Use only black and white color schema
     */
    public function UseHighContrastColor($aFlg = true, $aBW = false)
    {
        $this->highcontrast = $aFlg;
        $this->highcontrastbw = $aBW;
        $this->contour->UseHighContrastColor($this->highcontrast, $this->highcontrastbw);
    }

    /**
     * Internal method. Stroke the contour plot to the graph
     *
     * @param $img Image handler
     * @param $xscale Instance of the xscale to use
     * @param $yscale Instance of the yscale to use
     */
    public function Stroke($img, $xscale, $yscale)
    {

        if (count($this->manualIsobarColors) > 0) {
            $this->contourColor = $this->manualIsobarColors;
            if (count($this->manualIsobarColors) != $this->nbrContours) {
                Util\JpGraphError::RaiseL(28002);
            }
        }

        $img->SetLineWeight($this->line_weight);

        for ($c = 0; $c < $this->nbrContours; $c++) {

            $img->SetColor($this->contourColor[$c]);

            $n = count($this->contourCoord[$c]);
            $i = 0;
            while ($i < $n) {
                list($x1, $y1) = $this->contourCoord[$c][$i][0];
                $x1t = $xscale->Translate($x1);
                $y1t = $yscale->Translate($y1);

                list($x2, $y2) = $this->contourCoord[$c][$i++][1];
                $x2t = $xscale->Translate($x2);
                $y2t = $yscale->Translate($y2);

                $img->Line($x1t, $y1t, $x2t, $y2t);
            }

        }
    }

}
