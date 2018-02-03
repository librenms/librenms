<?php
namespace Amenadiel\JpGraph\Plot;

/*=======================================================================
// File:        JPGRAPH_POLAR.PHP
// Description: Polar plot extension for JpGraph
// Created:     2003-02-02
// Ver:         $Id: jpgraph_polar.php 1796 2009-09-07 09:37:19Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

require_once 'jpgraph_plotmark.inc.php';
require_once "jpgraph_log.php";

define('POLAR_360', 1);
define('POLAR_180', 2);

//
// Note. Don't attempt to make sense of this code.
// In order not to have to be able to inherit the scaling code
// from the main graph package we have had to make some "tricks" since
// the original scaling and axis was not designed to do what is
// required here.
// There were two option. 1: Re-implement everything and get a clean design
// and 2: do some "small" trickery and be able to inherit most of
// the functionlity from the main graph package.
// We choose 2: here in order to save some time.
//

//--------------------------------------------------------------------------
// class PolarPlot
//--------------------------------------------------------------------------
class PolarPlot
{
    public $line_style       = 'solid';
    public $mark;
    public $legendcsimtarget     = '';
    public $legendcsimalt        = '';
    public $legend               = "";
    public $csimtargets          = []; // Array of targets for CSIM
    public $csimareas            = ""; // Resultant CSIM area tags
    public $csimalts             = null; // ALT:s for corresponding target
    public $scale                = null;
    private $numpoints           = 0;
    private $iColor              = 'navy';
    private $iFillColor          = '';
    private $iLineWeight         = 1;
    private $coord               = null;

    public function __construct($aData)
    {
        $n = count($aData);
        if ($n & 1) {
            Util\JpGraphError::RaiseL(17001);
            //('Polar plots must have an even number of data point. Each data point is a tuple (angle,radius).');
        }
        $this->numpoints = $n / 2;
        $this->coord     = $aData;
        $this->mark      = new PlotMark();
    }

    public function SetWeight($aWeight)
    {
        $this->iLineWeight = $aWeight;
    }

    public function SetColor($aColor)
    {
        $this->iColor = $aColor;
    }

    public function SetFillColor($aColor)
    {
        $this->iFillColor = $aColor;
    }

    public function Max()
    {
        $m = $this->coord[1];
        $i = 1;
        while ($i < $this->numpoints) {
            $m = max($m, $this->coord[2 * $i + 1]);
            ++$i;
        }
        return $m;
    }

    // Set href targets for CSIM
    public function SetCSIMTargets($aTargets, $aAlts = null)
    {
        $this->csimtargets = $aTargets;
        $this->csimalts    = $aAlts;
    }

    // Get all created areas
    public function GetCSIMareas()
    {
        return $this->csimareas;
    }

    public function SetLegend($aLegend, $aCSIM = "", $aCSIMAlt = "")
    {
        $this->legend           = $aLegend;
        $this->legendcsimtarget = $aCSIM;
        $this->legendcsimalt    = $aCSIMAlt;
    }

    // Private methods

    public function Legend($aGraph)
    {
        $color = $this->iColor;
        if ($this->legend != "") {
            if ($this->iFillColor != '') {
                $color = $this->iFillColor;
                $aGraph->legend->Add($this->legend, $color, $this->mark, 0,
                    $this->legendcsimtarget, $this->legendcsimalt);
            } else {
                $aGraph->legend->Add($this->legend, $color, $this->mark, $this->line_style,
                    $this->legendcsimtarget, $this->legendcsimalt);
            }
        }
    }

    public function Stroke($img, $scale)
    {
        $i               = 0;
        $p               = [];
        $this->csimareas = '';
        while ($i < $this->numpoints) {
            list($x1, $y1) = $scale->PTranslate($this->coord[2 * $i], $this->coord[2 * $i + 1]);
            $p[2 * $i]     = $x1;
            $p[2 * $i + 1] = $y1;

            if (isset($this->csimtargets[$i])) {
                $this->mark->SetCSIMTarget($this->csimtargets[$i]);
                $this->mark->SetCSIMAlt($this->csimalts[$i]);
                $this->mark->SetCSIMAltVal($this->coord[2 * $i], $this->coord[2 * $i + 1]);
                $this->mark->Stroke($img, $x1, $y1);
                $this->csimareas .= $this->mark->GetCSIMAreas();
            } else {
                $this->mark->Stroke($img, $x1, $y1);
            }

            ++$i;
        }

        if ($this->iFillColor != '') {
            $img->SetColor($this->iFillColor);
            $img->FilledPolygon($p);
        }
        $img->SetLineWeight($this->iLineWeight);
        $img->SetColor($this->iColor);
        $img->Polygon($p, $this->iFillColor != '');
    }
}
