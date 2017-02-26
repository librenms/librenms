<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS AccLinePlot
// Description:
//===================================================
class AccLinePlot extends Plot
{
    protected $plots = null, $nbrplots = 0;
    private $iStartEndZero = true;
    //---------------
    // CONSTRUCTOR
    public function __construct($plots)
    {
        $this->plots = $plots;
        $this->nbrplots = count($plots);
        $this->numpoints = $plots[0]->numpoints;

        // Verify that all plots have the same number of data points
        for ($i = 1; $i < $this->nbrplots; ++$i) {
            if ($plots[$i]->numpoints != $this->numpoints) {
                Util\JpGraphError::RaiseL(10003); //('Each plot in an accumulated lineplot must have the same number of data points',0)
            }
        }

        for ($i = 0; $i < $this->nbrplots; ++$i) {
            $this->LineInterpolate($this->plots[$i]->coords[0]);
        }
    }

    //---------------
    // PUBLIC METHODS
    public function Legend($graph)
    {
        foreach ($this->plots as $p) {
            $p->DoLegend($graph);
        }
    }

    public function Max()
    {
        list($xmax) = $this->plots[0]->Max();
        $nmax = 0;
        $n = count($this->plots);
        for ($i = 0; $i < $n; ++$i) {
            $nc = count($this->plots[$i]->coords[0]);
            $nmax = max($nmax, $nc);
            list($x) = $this->plots[$i]->Max();
            $xmax = Max($xmax, $x);
        }
        for ($i = 0; $i < $nmax; $i++) {
            // Get y-value for line $i by adding the
            // individual bars from all the plots added.
            // It would be wrong to just add the
            // individual plots max y-value since that
            // would in most cases give to large y-value.
            $y = $this->plots[0]->coords[0][$i];
            for ($j = 1; $j < $this->nbrplots; $j++) {
                $y += $this->plots[$j]->coords[0][$i];
            }
            $ymax[$i] = $y;
        }
        $ymax = max($ymax);
        return array($xmax, $ymax);
    }

    public function Min()
    {
        $nmax = 0;
        list($xmin, $ysetmin) = $this->plots[0]->Min();
        $n = count($this->plots);
        for ($i = 0; $i < $n; ++$i) {
            $nc = count($this->plots[$i]->coords[0]);
            $nmax = max($nmax, $nc);
            list($x, $y) = $this->plots[$i]->Min();
            $xmin = Min($xmin, $x);
            $ysetmin = Min($y, $ysetmin);
        }
        for ($i = 0; $i < $nmax; $i++) {
            // Get y-value for line $i by adding the
            // individual bars from all the plots added.
            // It would be wrong to just add the
            // individual plots min y-value since that
            // would in most cases give to small y-value.
            $y = $this->plots[0]->coords[0][$i];
            for ($j = 1; $j < $this->nbrplots; $j++) {
                $y += $this->plots[$j]->coords[0][$i];
            }
            $ymin[$i] = $y;
        }
        $ymin = Min($ysetmin, Min($ymin));
        return array($xmin, $ymin);
    }

    // Gets called before any axis are stroked
    public function PreStrokeAdjust($graph)
    {

        // If another plot type have already adjusted the
        // offset we don't touch it.
        // (We check for empty in case the scale is  a log scale
        // and hence doesn't contain any xlabel_offset)

        if (empty($graph->xaxis->scale->ticks->xlabel_offset) ||
            $graph->xaxis->scale->ticks->xlabel_offset == 0) {
            if ($this->center) {
                ++$this->numpoints;
                $a = 0.5;
                $b = 0.5;
            } else {
                $a = 0;
                $b = 0;
            }
            $graph->xaxis->scale->ticks->SetXLabelOffset($a);
            $graph->SetTextScaleOff($b);
            $graph->xaxis->scale->ticks->SupressMinorTickMarks();
        }

    }

    public function SetInterpolateMode($aIntMode)
    {
        $this->iStartEndZero = $aIntMode;
    }

    // Replace all '-' with an interpolated value. We use straightforward
    // linear interpolation. If the data starts with one or several '-' they
    // will be replaced by the the first valid data point
    public function LineInterpolate(&$aData)
    {

        $n = count($aData);
        $i = 0;

        // If first point is undefined we will set it to the same as the first
        // valid data
        if ($aData[$i] === '-') {
            // Find the first valid data
            while ($i < $n && $aData[$i] === '-') {
                ++$i;
            }
            if ($i < $n) {
                for ($j = 0; $j < $i; ++$j) {
                    if ($this->iStartEndZero) {
                        $aData[$i] = 0;
                    } else {
                        $aData[$j] = $aData[$i];
                    }

                }
            } else {
                // All '-' => Error
                return false;
            }
        }

        while ($i < $n) {
            while ($i < $n && $aData[$i] !== '-') {
                ++$i;
            }
            if ($i < $n) {
                $pstart = $i - 1;

                // Now see how long this segment of '-' are
                while ($i < $n && $aData[$i] === '-') {
                    ++$i;
                }
                if ($i < $n) {
                    $pend = $i;
                    $size = $pend - $pstart;
                    $k = ($aData[$pend] - $aData[$pstart]) / $size;
                    // Replace the segment of '-' with a linear interpolated value.
                    for ($j = 1; $j < $size; ++$j) {
                        $aData[$pstart + $j] = $aData[$pstart] + $j * $k;
                    }
                } else {
                    // There are no valid end point. The '-' goes all the way to the end
                    // In that case we just set all the remaining values the the same as the
                    // last valid data point.
                    for ($j = $pstart + 1; $j < $n; ++$j) {
                        if ($this->iStartEndZero) {
                            $aData[$j] = 0;
                        } else {
                            $aData[$j] = $aData[$pstart];
                        }
                    }

                }
            }
        }
        return true;
    }

    // To avoid duplicate of line drawing code here we just
    // change the y-values for each plot and then restore it
    // after we have made the stroke. We must do this copy since
    // it wouldn't be possible to create an acc line plot
    // with the same graphs, i.e AccLinePlot(array($pl,$pl,$pl));
    // since this method would have a side effect.
    public function Stroke($img, $xscale, $yscale)
    {
        $img->SetLineWeight($this->weight);
        $this->numpoints = count($this->plots[0]->coords[0]);
        // Allocate array
        $coords[$this->nbrplots][$this->numpoints] = 0;
        for ($i = 0; $i < $this->numpoints; $i++) {
            $coords[0][$i] = $this->plots[0]->coords[0][$i];
            $accy = $coords[0][$i];
            for ($j = 1; $j < $this->nbrplots; ++$j) {
                $coords[$j][$i] = $this->plots[$j]->coords[0][$i] + $accy;
                $accy = $coords[$j][$i];
            }
        }
        for ($j = $this->nbrplots - 1; $j >= 0; --$j) {
            $p = $this->plots[$j];
            for ($i = 0; $i < $this->numpoints; ++$i) {
                $tmp[$i] = $p->coords[0][$i];
                $p->coords[0][$i] = $coords[$j][$i];
            }
            $p->Stroke($img, $xscale, $yscale);
            for ($i = 0; $i < $this->numpoints; ++$i) {
                $p->coords[0][$i] = $tmp[$i];
            }
            $p->coords[0][] = $tmp;
        }
    }
} // Class
