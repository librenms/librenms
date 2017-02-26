<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS GroupBarPlot
// Description: Produce grouped bar plots
//===================================================
class GroupBarPlot extends BarPlot
{
    public $plots;
    private $nbrplots = 0;
    //---------------
    // CONSTRUCTOR
    public function __construct($plots)
    {
        $this->width = 0.7;
        $this->plots = $plots;
        $this->nbrplots = count($plots);
        if ($this->nbrplots < 1) {
            Util\JpGraphError::RaiseL(2007); //('Cannot create GroupBarPlot from empty plot array.');
        }
        for ($i = 0; $i < $this->nbrplots; ++$i) {
            if (empty($this->plots[$i]) || !isset($this->plots[$i])) {
                Util\JpGraphError::RaiseL(2008, $i); //("Group bar plot element nbr $i is undefined or empty.");
            }
        }
        $this->numpoints = $plots[0]->numpoints;
        $this->width = 0.7;
    }

    //---------------
    // PUBLIC METHODS
    public function Legend($graph)
    {
        $n = count($this->plots);
        for ($i = 0; $i < $n; ++$i) {
            $c = get_class($this->plots[$i]);
            if (!($this->plots[$i] instanceof BarPlot)) {
                Util\JpGraphError::RaiseL(2009, $c);
                //('One of the objects submitted to GroupBar is not a BarPlot. Make sure that you create the Group Bar plot from an array of BarPlot or AccBarPlot objects. (Class = '.$c.')');
            }
            $this->plots[$i]->DoLegend($graph);
        }
    }

    public function Min()
    {
        list($xmin, $ymin) = $this->plots[0]->Min();
        $n = count($this->plots);
        for ($i = 0; $i < $n; ++$i) {
            list($xm, $ym) = $this->plots[$i]->Min();
            $xmin = max($xmin, $xm);
            $ymin = min($ymin, $ym);
        }
        return array($xmin, $ymin);
    }

    public function Max()
    {
        list($xmax, $ymax) = $this->plots[0]->Max();
        $n = count($this->plots);
        for ($i = 0; $i < $n; ++$i) {
            list($xm, $ym) = $this->plots[$i]->Max();
            $xmax = max($xmax, $xm);
            $ymax = max($ymax, $ym);
        }
        return array($xmax, $ymax);
    }

    public function GetCSIMareas()
    {
        $n = count($this->plots);
        $csimareas = '';
        for ($i = 0; $i < $n; ++$i) {
            $csimareas .= $this->plots[$i]->csimareas;
        }
        return $csimareas;
    }

    // Stroke all the bars next to each other
    public function Stroke($img, $xscale, $yscale)
    {
        $tmp = $xscale->off;
        $n = count($this->plots);
        $subwidth = $this->width / $this->nbrplots;

        for ($i = 0; $i < $n; ++$i) {
            $this->plots[$i]->ymin = $this->ybase;
            $this->plots[$i]->SetWidth($subwidth);

            // If the client have used SetTextTickInterval() then
            // major_step will be > 1 and the positioning will fail.
            // If we assume it is always one the positioning will work
            // fine with a text scale but this will not work with
            // arbitrary linear scale
            $xscale->off = $tmp + $i * round($xscale->scale_factor * $subwidth);
            $this->plots[$i]->Stroke($img, $xscale, $yscale);
        }
        $xscale->off = $tmp;
    }
} // Class
