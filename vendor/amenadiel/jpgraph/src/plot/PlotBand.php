<?php
namespace Amenadiel\JpGraph\Plot;

use Amenadiel\JpGraph\Graph\RectPatternFactory;
use Amenadiel\JpGraph\Util;

//=======================================================================
// File:        JPGRAPH_PLOTBAND.PHP
// Description: PHP4 Graph Plotting library. Extension module.
// Created:     2004-02-18
// Ver:         $Id: jpgraph_plotband.php 1106 2009-02-22 20:16:35Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================

//=====================================================================
// Class PlotBand
// Factory class which is used by the client.
// It is responsible for factoring the corresponding pattern
// concrete class.
//=====================================================================
class PlotBand
{
    public $depth; // Determine if band should be over or under the plots
    private $prect = null;
    private $dir, $min, $max;

    public function __construct($aDir, $aPattern, $aMin, $aMax, $aColor = "black", $aWeight = 1, $aDepth = DEPTH_BACK)
    {
        $f = new RectPatternFactory();
        $this->prect = $f->Create($aPattern, $aColor, $aWeight);
        if (is_numeric($aMin) && is_numeric($aMax) && ($aMin > $aMax)) {
            Util\JpGraphError::RaiseL(16004);
        }

        //('Min value for plotband is larger than specified max value. Please correct.');
        $this->dir = $aDir;
        $this->min = $aMin;
        $this->max = $aMax;
        $this->depth = $aDepth;
    }

    // Set position. aRect contains absolute image coordinates
    public function SetPos($aRect)
    {
        assert($this->prect != null);
        $this->prect->SetPos($aRect);
    }

    public function ShowFrame($aFlag = true)
    {
        $this->prect->ShowFrame($aFlag);
    }

    // Set z-order. In front of pplot or in the back
    public function SetOrder($aDepth)
    {
        $this->depth = $aDepth;
    }

    public function SetDensity($aDens)
    {
        $this->prect->SetDensity($aDens);
    }

    public function GetDir()
    {
        return $this->dir;
    }

    public function GetMin()
    {
        return $this->min;
    }

    public function GetMax()
    {
        return $this->max;
    }

    public function PreStrokeAdjust($aGraph)
    {
        // Nothing to do
    }

    // Display band
    public function Stroke($aImg, $aXScale, $aYScale)
    {
        assert($this->prect != null);
        if ($this->dir == HORIZONTAL) {
            if ($this->min === 'min') {
                $this->min = $aYScale->GetMinVal();
            }

            if ($this->max === 'max') {
                $this->max = $aYScale->GetMaxVal();
            }

            // Only draw the bar if it actually appears in the range
            if ($this->min < $aYScale->GetMaxVal() && $this->max > $aYScale->GetMinVal()) {

                // Trucate to limit of axis
                $this->min = max($this->min, $aYScale->GetMinVal());
                $this->max = min($this->max, $aYScale->GetMaxVal());

                $x = $aXScale->scale_abs[0];
                $y = $aYScale->Translate($this->max);
                $width = $aXScale->scale_abs[1] - $aXScale->scale_abs[0] + 1;
                $height = abs($y - $aYScale->Translate($this->min)) + 1;
                $this->prect->SetPos(new Rectangle($x, $y, $width, $height));
                $this->prect->Stroke($aImg);
            }
        } else {
            // VERTICAL
            if ($this->min === 'min') {
                $this->min = $aXScale->GetMinVal();
            }

            if ($this->max === 'max') {
                $this->max = $aXScale->GetMaxVal();
            }

            // Only draw the bar if it actually appears in the range
            if ($this->min < $aXScale->GetMaxVal() && $this->max > $aXScale->GetMinVal()) {

                // Trucate to limit of axis
                $this->min = max($this->min, $aXScale->GetMinVal());
                $this->max = min($this->max, $aXScale->GetMaxVal());

                $y = $aYScale->scale_abs[1];
                $x = $aXScale->Translate($this->min);
                $height = abs($aYScale->scale_abs[1] - $aYScale->scale_abs[0]);
                $width = abs($x - $aXScale->Translate($this->max));
                $this->prect->SetPos(new Rectangle($x, $y, $width, $height));
                $this->prect->Stroke($aImg);
            }
        }
    }
}
