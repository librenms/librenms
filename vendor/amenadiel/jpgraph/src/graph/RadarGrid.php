<?php
namespace Amenadiel\JpGraph\Graph;

/*=======================================================================
// File:        JPGRAPH_RADAR.PHP
// Description: Radar plot extension for JpGraph
// Created:     2001-02-04
// Ver:         $Id: jpgraph_radar.php 1783 2009-08-25 11:41:01Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

require_once 'jpgraph_plotmark.inc.php';

//===================================================
// CLASS RadarGrid
// Description: Draws grid for the radar graph
//===================================================
class RadarGrid
{
    //extends Grid {
    private $type = 'solid';
    private $grid_color = '#DDDDDD';
    private $show = false, $weight = 1;

    public function __construct()
    {
        // Empty
    }

    public function SetColor($aMajColor)
    {
        $this->grid_color = $aMajColor;
    }

    public function SetWeight($aWeight)
    {
        $this->weight = $aWeight;
    }

    // Specify if grid should be dashed, dotted or solid
    public function SetLineStyle($aType)
    {
        $this->type = $aType;
    }

    // Decide if both major and minor grid should be displayed
    public function Show($aShowMajor = true)
    {
        $this->show = $aShowMajor;
    }

    public function Stroke($img, $grid)
    {
        if (!$this->show) {
            return;
        }

        $nbrticks = count($grid[0]) / 2;
        $nbrpnts = count($grid);
        $img->SetColor($this->grid_color);
        $img->SetLineWeight($this->weight);

        for ($i = 0; $i < $nbrticks; ++$i) {
            for ($j = 0; $j < $nbrpnts; ++$j) {
                $pnts[$j * 2] = $grid[$j][$i * 2];
                $pnts[$j * 2 + 1] = $grid[$j][$i * 2 + 1];
            }
            for ($k = 0; $k < $nbrpnts; ++$k) {
                $l = ($k + 1) % $nbrpnts;
                if ($this->type == 'solid') {
                    $img->Line($pnts[$k * 2], $pnts[$k * 2 + 1], $pnts[$l * 2], $pnts[$l * 2 + 1]);
                } elseif ($this->type == 'dotted') {
                    $img->DashedLine($pnts[$k * 2], $pnts[$k * 2 + 1], $pnts[$l * 2], $pnts[$l * 2 + 1], 1, 6);
                } elseif ($this->type == 'dashed') {
                    $img->DashedLine($pnts[$k * 2], $pnts[$k * 2 + 1], $pnts[$l * 2], $pnts[$l * 2 + 1], 2, 4);
                } elseif ($this->type == 'longdashed') {
                    $img->DashedLine($pnts[$k * 2], $pnts[$k * 2 + 1], $pnts[$l * 2], $pnts[$l * 2 + 1], 8, 6);
                }

            }
            $pnts = array();
        }
    }
} // Class

//===================================================
// CLASS RadarPlot
// Description: Plot a radarplot
//===================================================
class RadarPlot
{
    public $mark = null;
    public $legend = '';
    public $legendcsimtarget = '';
    public $legendcsimalt = '';
    public $csimtargets = array(); // Array of targets for CSIM
    public $csimareas = ""; // Resultant CSIM area tags
    public $csimalts = null; // ALT:s for corresponding target
    private $data = array();
    private $fill = false, $fill_color = array(200, 170, 180);
    private $color = array(0, 0, 0);
    private $weight = 1;
    private $linestyle = 'solid';

    //---------------
    // CONSTRUCTOR
    public function __construct($data)
    {
        $this->data = $data;
        $this->mark = new PlotMark();
    }

    public function Min()
    {
        return Min($this->data);
    }

    public function Max()
    {
        return Max($this->data);
    }

    public function SetLegend($legend)
    {
        $this->legend = $legend;
    }

    public function SetLineStyle($aStyle)
    {
        $this->linestyle = $aStyle;
    }

    public function SetLineWeight($w)
    {
        $this->weight = $w;
    }

    public function SetFillColor($aColor)
    {
        $this->fill_color = $aColor;
        $this->fill = true;
    }

    public function SetFill($f = true)
    {
        $this->fill = $f;
    }

    public function SetColor($aColor, $aFillColor = false)
    {
        $this->color = $aColor;
        if ($aFillColor) {
            $this->SetFillColor($aFillColor);
            $this->fill = true;
        }
    }

    // Set href targets for CSIM
    public function SetCSIMTargets($aTargets, $aAlts = null)
    {
        $this->csimtargets = $aTargets;
        $this->csimalts = $aAlts;
    }

    // Get all created areas
    public function GetCSIMareas()
    {
        return $this->csimareas;
    }

    public function Stroke($img, $pos, $scale, $startangle)
    {
        $nbrpnts = count($this->data);
        $astep = 2 * M_PI / $nbrpnts;
        $a = $startangle;

        for ($i = 0; $i < $nbrpnts; ++$i) {

            // Rotate each non null point to the correct axis-angle
            $cs = $scale->RelTranslate($this->data[$i]);
            $x = round($cs * cos($a) + $scale->scale_abs[0]);
            $y = round($pos - $cs * sin($a));

            $pnts[$i * 2] = $x;
            $pnts[$i * 2 + 1] = $y;

            // If the next point is null then we draw this polygon segment
            // to the center, skip the next and draw the next segment from
            // the center up to the point on the axis with the first non-null
            // value and continues from that point. Some additoinal logic is necessary
            // to handle the boundary conditions
            if ($i < $nbrpnts - 1) {
                if (is_null($this->data[$i + 1])) {
                    $cs = 0;
                    $x = round($cs * cos($a) + $scale->scale_abs[0]);
                    $y = round($pos - $cs * sin($a));
                    $pnts[$i * 2] = $x;
                    $pnts[$i * 2 + 1] = $y;
                    $a += $astep;
                }
            }

            $a += $astep;
        }

        if ($this->fill) {
            $img->SetColor($this->fill_color);
            $img->FilledPolygon($pnts);
        }

        $img->SetLineWeight($this->weight);
        $img->SetColor($this->color);
        $img->SetLineStyle($this->linestyle);
        $pnts[] = $pnts[0];
        $pnts[] = $pnts[1];
        $img->Polygon($pnts);
        $img->SetLineStyle('solid'); // Reset line style to default

        // Add plotmarks on top
        if ($this->mark->show) {
            for ($i = 0; $i < $nbrpnts; ++$i) {
                if (isset($this->csimtargets[$i])) {
                    $this->mark->SetCSIMTarget($this->csimtargets[$i]);
                    $this->mark->SetCSIMAlt($this->csimalts[$i]);
                    $this->mark->SetCSIMAltVal($pnts[$i * 2], $pnts[$i * 2 + 1]);
                    $this->mark->Stroke($img, $pnts[$i * 2], $pnts[$i * 2 + 1]);
                    $this->csimareas .= $this->mark->GetCSIMAreas();
                } else {
                    $this->mark->Stroke($img, $pnts[$i * 2], $pnts[$i * 2 + 1]);
                }
            }
        }

    }

    public function GetCount()
    {
        return count($this->data);
    }

    public function Legend($graph)
    {
        if ($this->legend == '') {
            return;
        }
        if ($this->fill) {
            $graph->legend->Add($this->legend, $this->fill_color, $this->mark);
        } else {
            $graph->legend->Add($this->legend, $this->color, $this->mark);
        }
    }

} // Class

/* EOF */
