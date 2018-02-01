<?php
namespace Amenadiel\JpGraph\Plot;

use Amenadiel\JpGraph\Graph;

//===================================================
// CLASS ScatterPlot
// Description: Render X and Y plots
//===================================================
class ScatterPlot extends Plot
{
    public $mark;
    public $link;
    private $impuls = false;
    //---------------
    // CONSTRUCTOR
    public function __construct($datay, $datax = false)
    {
        if ((count($datax) != count($datay)) && is_array($datax)) {
            Util\JpGraphError::RaiseL(20003); //("Scatterplot must have equal number of X and Y points.");
        }
        parent::__construct($datay, $datax);
        $this->mark = new PlotMark();
        $this->mark->SetType(MARK_SQUARE);
        $this->mark->SetColor($this->color);
        $this->value->SetAlign('center', 'center');
        $this->value->SetMargin(0);
        $this->link        = new Graph\LineProperty(1, 'black', 'solid');
        $this->link->iShow = false;
    }

    //---------------
    // PUBLIC METHODS
    public function SetImpuls($f = true)
    {
        $this->impuls = $f;
    }

    public function SetStem($f = true)
    {
        $this->impuls = $f;
    }

    // Combine the scatter plot points with a line
    public function SetLinkPoints($aFlag = true, $aColor = "black", $aWeight = 1, $aStyle = 'solid')
    {
        $this->link->iShow   = $aFlag;
        $this->link->iColor  = $aColor;
        $this->link->iWeight = $aWeight;
        $this->link->iStyle  = $aStyle;
    }

    public function Stroke($img, $xscale, $yscale)
    {
        $ymin = $yscale->scale_abs[0];
        if ($yscale->scale[0] < 0) {
            $yzero = $yscale->Translate(0);
        } else {
            $yzero = $yscale->scale_abs[0];
        }

        $this->csimareas = '';
        for ($i = 0; $i < $this->numpoints; ++$i) {

            // Skip null values
            if ($this->coords[0][$i] === '' || $this->coords[0][$i] === '-' || $this->coords[0][$i] === 'x') {
                continue;
            }

            if (isset($this->coords[1])) {
                $xt = $xscale->Translate($this->coords[1][$i]);
            } else {
                $xt = $xscale->Translate($i);
            }

            $yt = $yscale->Translate($this->coords[0][$i]);

            if ($this->link->iShow && isset($yt_old)) {
                $img->SetColor($this->link->iColor);
                $img->SetLineWeight($this->link->iWeight);
                $old = $img->SetLineStyle($this->link->iStyle);
                $img->StyleLine($xt_old, $yt_old, $xt, $yt);
                $img->SetLineStyle($old);
            }

            if ($this->impuls) {
                $img->SetColor($this->color);
                $img->SetLineWeight($this->weight);
                $img->Line($xt, $yzero, $xt, $yt);
            }

            if (!empty($this->csimtargets[$i])) {
                if (!empty($this->csimwintargets[$i])) {
                    $this->mark->SetCSIMTarget($this->csimtargets[$i], $this->csimwintargets[$i]);
                } else {
                    $this->mark->SetCSIMTarget($this->csimtargets[$i]);
                }
                $this->mark->SetCSIMAlt($this->csimalts[$i]);
            }

            if (isset($this->coords[1])) {
                $this->mark->SetCSIMAltVal($this->coords[0][$i], $this->coords[1][$i]);
            } else {
                $this->mark->SetCSIMAltVal($this->coords[0][$i], $i);
            }

            $this->mark->Stroke($img, $xt, $yt);

            $this->csimareas .= $this->mark->GetCSIMAreas();
            $this->value->Stroke($img, $this->coords[0][$i], $xt, $yt);

            $xt_old = $xt;
            $yt_old = $yt;
        }
    }

    // Framework function
    public function Legend($aGraph)
    {
        if ($this->legend != "") {
            $aGraph->legend->Add($this->legend, $this->mark->fill_color, $this->mark, 0,
                $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
        }
    }
} // Class
/* EOF */
