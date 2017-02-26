<?php
namespace Amenadiel\JpGraph\Plot;

use \Amenadiel\JpGraph\Image;

/*=======================================================================
// File:        JPGRAPH_BAR.PHP
// Description: Bar plot extension for JpGraph
// Created:     2001-01-08
// Ver:         $Id: jpgraph_bar.php 1905 2009-10-06 18:00:21Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

// Pattern for Bars
DEFINE('PATTERN_DIAG1', 1);
DEFINE('PATTERN_DIAG2', 2);
DEFINE('PATTERN_DIAG3', 3);
DEFINE('PATTERN_DIAG4', 4);
DEFINE('PATTERN_CROSS1', 5);
DEFINE('PATTERN_CROSS2', 6);
DEFINE('PATTERN_CROSS3', 7);
DEFINE('PATTERN_CROSS4', 8);
DEFINE('PATTERN_STRIPE1', 9);
DEFINE('PATTERN_STRIPE2', 10);

//===================================================
// CLASS BarPlot
// Description: Main code to produce a bar plot
//===================================================
class BarPlot extends Plot
{
    public $fill = false, $fill_color = "lightblue"; // Default is to fill with light blue
    public $iPattern = -1, $iPatternDensity = 80, $iPatternColor = 'black';
    public $valuepos = 'top';
    public $grad = false, $grad_style = 1;
    public $grad_fromcolor = array(50, 50, 200), $grad_tocolor = array(255, 255, 255);
    public $ymin = 0;
    protected $width = 0.4; // in percent of major ticks
    protected $abswidth = -1; // Width in absolute pixels
    protected $ybase = 0; // Bars start at 0
    protected $align = "center";
    protected $bar_shadow = false;
    protected $bar_shadow_color = "black";
    protected $bar_shadow_hsize = 3, $bar_shadow_vsize = 3;
    protected $bar_3d = false;
    protected $bar_3d_hsize = 3, $bar_3d_vsize = 3;

    //---------------
    // CONSTRUCTOR
    public function __construct($datay, $datax = false)
    {
        parent::__construct($datay, $datax);
        ++$this->numpoints;
    }

    //---------------
    // PUBLIC METHODS

    // Set a drop shadow for the bar (or rather an "up-right" shadow)
    public function SetShadow($aColor = "black", $aHSize = 3, $aVSize = 3, $aShow = true)
    {
        $this->bar_shadow = $aShow;
        $this->bar_shadow_color = $aColor;
        $this->bar_shadow_vsize = $aVSize;
        $this->bar_shadow_hsize = $aHSize;

        // Adjust the value margin to compensate for shadow
        $this->value->margin += $aVSize;
    }

    public function Set3D($aHSize = 3, $aVSize = 3, $aShow = true)
    {
        $this->bar_3d = $aShow;
        $this->bar_3d_vsize = $aVSize;
        $this->bar_3d_hsize = $aHSize;

        $this->value->margin += $aVSize;
    }

    // DEPRECATED use SetYBase instead
    public function SetYMin($aYStartValue)
    {
        //die("JpGraph Error: Deprecated function SetYMin. Use SetYBase() instead.");
        $this->ybase = $aYStartValue;
    }

    // Specify the base value for the bars
    public function SetYBase($aYStartValue)
    {
        $this->ybase = $aYStartValue;
    }

    // The method will take the specified pattern anre
    // return a pattern index that corresponds to the original
    // patterm being rotated 90 degreees. This is needed when plottin
    // Horizontal bars
    public function RotatePattern($aPat, $aRotate = true)
    {
        $rotate = array(1 => 2, 2 => 1, 3 => 3, 4 => 5, 5 => 4, 6 => 6, 7 => 7, 8 => 8);
        if ($aRotate) {
            return $rotate[$aPat];
        } else {
            return $aPat;
        }
    }

    public function Legend($graph)
    {
        if ($this->grad && $this->legend != "" && !$this->fill) {
            $color = array($this->grad_fromcolor, $this->grad_tocolor);
            // In order to differentiate between gradients and cooors specified as an RGB triple
            $graph->legend->Add($this->legend, $color, "", -$this->grad_style,
                $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
        } elseif ($this->legend != "" && ($this->iPattern > -1 || is_array($this->iPattern))) {
            if (is_array($this->iPattern)) {
                $p1 = $this->RotatePattern($this->iPattern[0], $graph->img->a == 90);
                $p2 = $this->iPatternColor[0];
                $p3 = $this->iPatternDensity[0];
            } else {
                $p1 = $this->RotatePattern($this->iPattern, $graph->img->a == 90);
                $p2 = $this->iPatternColor;
                $p3 = $this->iPatternDensity;
            }
            if ($p3 < 90) {
                $p3 += 5;
            }

            $color = array($p1, $p2, $p3, $this->fill_color);
            // A kludge: Too mark that we add a pattern we use a type value of < 100
            $graph->legend->Add($this->legend, $color, "", -101,
                $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
        } elseif ($this->fill_color && $this->legend != "") {
            if (is_array($this->fill_color)) {
                $graph->legend->Add($this->legend, $this->fill_color[0], "", 0,
                    $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
            } else {
                $graph->legend->Add($this->legend, $this->fill_color, "", 0,
                    $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
            }
        }
    }

    // Gets called before any axis are stroked
    public function PreStrokeAdjust($graph)
    {
        parent::PreStrokeAdjust($graph);

        // If we are using a log Y-scale we want the base to be at the
        // minimum Y-value unless the user have specifically set some other
        // value than the default.
        if (substr($graph->axtype, -3, 3) == "log" && $this->ybase == 0) {
            $this->ybase = $graph->yaxis->scale->GetMinVal();
        }

        // For a "text" X-axis scale we will adjust the
        // display of the bars a little bit.
        if (substr($graph->axtype, 0, 3) == "tex") {
            // Position the ticks between the bars
            $graph->xaxis->scale->ticks->SetXLabelOffset(0.5, 0);

            // Center the bars
            if ($this->abswidth > -1) {
                $graph->SetTextScaleAbsCenterOff($this->abswidth);
            } else {
                if ($this->align == "center") {
                    $graph->SetTextScaleOff(0.5 - $this->width / 2);
                } elseif ($this->align == "right") {
                    $graph->SetTextScaleOff(1 - $this->width);
                }

            }
        } elseif (($this instanceof AccBarPlot) || ($this instanceof GroupBarPlot)) {
            // We only set an absolute width for linear and int scale
            // for text scale the width will be set to a fraction of
            // the majstep width.
            if ($this->abswidth == -1) {
                // Not set
                // set width to a visuable sensible default
                $this->abswidth = $graph->img->plotwidth / (2 * $this->numpoints);
            }
        }
    }

    public function Min()
    {
        $m = parent::Min();
        if ($m[1] >= $this->ybase) {
            $m[1] = $this->ybase;
        }

        return $m;
    }

    public function Max()
    {
        $m = parent::Max();
        if ($m[1] <= $this->ybase) {
            $m[1] = $this->ybase;
        }

        return $m;
    }

    // Specify width as fractions of the major stepo size
    public function SetWidth($aWidth)
    {
        if ($aWidth > 1) {
            // Interpret this as absolute width
            $this->abswidth = $aWidth;
        } else {
            $this->width = $aWidth;
        }
    }

    // Specify width in absolute pixels. If specified this
    // overrides SetWidth()
    public function SetAbsWidth($aWidth)
    {
        $this->abswidth = $aWidth;
    }

    public function SetAlign($aAlign)
    {
        $this->align = $aAlign;
    }

    public function SetNoFill()
    {
        $this->grad = false;
        $this->fill_color = false;
        $this->fill = false;
    }

    public function SetFillColor($aColor)
    {
        // Do an extra error check if the color is specified as an RGB array triple
        // In that case convert it to a hex string since it will otherwise be
        // interpretated as an array of colors for each individual bar.

        $aColor = Image\RGB::tryHexConversion($aColor);
        $this->fill = true;
        $this->fill_color = $aColor;

    }

    public function SetFillGradient($aFromColor, $aToColor = null, $aStyle = null)
    {
        $this->grad = true;
        $this->grad_fromcolor = $aFromColor;
        $this->grad_tocolor = $aToColor;
        $this->grad_style = $aStyle;
    }

    public function SetValuePos($aPos)
    {
        $this->valuepos = $aPos;
    }

    public function SetPattern($aPattern, $aColor = 'black')
    {
        if (is_array($aPattern)) {
            $n = count($aPattern);
            $this->iPattern = array();
            $this->iPatternDensity = array();
            if (is_array($aColor)) {
                $this->iPatternColor = array();
                if (count($aColor) != $n) {
                    Util\JpGraphError::RaiseL(2001); //('NUmber of colors is not the same as the number of patterns in BarPlot::SetPattern()');
                }
            } else {
                $this->iPatternColor = $aColor;
            }
            for ($i = 0; $i < $n; ++$i) {
                $this->_SetPatternHelper($aPattern[$i], $this->iPattern[$i], $this->iPatternDensity[$i]);
                if (is_array($aColor)) {
                    $this->iPatternColor[$i] = $aColor[$i];
                }
            }
        } else {
            $this->_SetPatternHelper($aPattern, $this->iPattern, $this->iPatternDensity);
            $this->iPatternColor = $aColor;
        }
    }

    public function _SetPatternHelper($aPattern, &$aPatternValue, &$aDensity)
    {
        switch ($aPattern) {
            case PATTERN_DIAG1:
                $aPatternValue = 1;
                $aDensity = 92;
                break;
            case PATTERN_DIAG2:
                $aPatternValue = 1;
                $aDensity = 78;
                break;
            case PATTERN_DIAG3:
                $aPatternValue = 2;
                $aDensity = 92;
                break;
            case PATTERN_DIAG4:
                $aPatternValue = 2;
                $aDensity = 78;
                break;
            case PATTERN_CROSS1:
                $aPatternValue = 8;
                $aDensity = 90;
                break;
            case PATTERN_CROSS2:
                $aPatternValue = 8;
                $aDensity = 78;
                break;
            case PATTERN_CROSS3:
                $aPatternValue = 8;
                $aDensity = 65;
                break;
            case PATTERN_CROSS4:
                $aPatternValue = 7;
                $aDensity = 90;
                break;
            case PATTERN_STRIPE1:
                $aPatternValue = 5;
                $aDensity = 94;
                break;
            case PATTERN_STRIPE2:
                $aPatternValue = 5;
                $aDensity = 85;
                break;
            default:
                Util\JpGraphError::RaiseL(2002);
                //('Unknown pattern specified in call to BarPlot::SetPattern()');
        }
    }

    public function Stroke($img, $xscale, $yscale)
    {

        $numpoints = count($this->coords[0]);
        if (isset($this->coords[1])) {
            if (count($this->coords[1]) != $numpoints) {
                Util\JpGraphError::RaiseL(2003, count($this->coords[1]), $numpoints);
                //"Number of X and Y points are not equal. Number of X-points:".count($this->coords[1])."Number of Y-points:$numpoints");
            } else {
                $exist_x = true;
            }
        } else {
            $exist_x = false;
        }

        $numbars = count($this->coords[0]);

        // Use GetMinVal() instead of scale[0] directly since in the case
        // of log scale we get a correct value. Log scales will have negative
        // values for values < 1 while still not representing negative numbers.
        if ($yscale->GetMinVal() >= 0) {
            $zp = $yscale->scale_abs[0];
        } else {
            $zp = $yscale->Translate(0);
        }

        if ($this->abswidth > -1) {
            $abswidth = $this->abswidth;
        } else {
            $abswidth = round($this->width * $xscale->scale_factor, 0);
        }

        // Count pontetial pattern array to avoid doing the count for each iteration
        if (is_array($this->iPattern)) {
            $np = count($this->iPattern);
        }

        $grad = null;
        for ($i = 0; $i < $numbars; ++$i) {

            // If value is NULL, or 0 then don't draw a bar at all
            if ($this->coords[0][$i] === null || $this->coords[0][$i] === '') {
                continue;
            }

            if ($exist_x) {
                $x = $this->coords[1][$i];
            } else {
                $x = $i;
            }

            $x = $xscale->Translate($x);

            // Comment Note: This confuses the positioning when using acc together with
            // grouped bars. Workaround for fixing #191
            /*
            if( !$xscale->textscale ) {
            if($this->align=="center")
            $x -= $abswidth/2;
            elseif($this->align=="right")
            $x -= $abswidth;
            }
             */
            // Stroke fill color and fill gradient
            $pts = array(
                $x, $zp,
                $x, $yscale->Translate($this->coords[0][$i]),
                $x + $abswidth, $yscale->Translate($this->coords[0][$i]),
                $x + $abswidth, $zp);
            if ($this->grad) {
                if ($grad === null) {
                    $grad = new Gradient($img);
                }
                if (is_array($this->grad_fromcolor)) {
                    // The first argument (grad_fromcolor) can be either an array or a single color. If it is an array
                    // then we have two choices. It can either a) be a single color specified as an RGB triple or it can be
                    // an array to specify both (from, to style) for each individual bar. The way to know the difference is
                    // to investgate the first element. If this element is an integer [0,255] then we assume it is an RGB
                    // triple.
                    $ng = count($this->grad_fromcolor);
                    if ($ng === 3) {
                        if (is_numeric($this->grad_fromcolor[0]) && $this->grad_fromcolor[0] > 0 && $this->grad_fromcolor[0] < 256) {
                            // RGB Triple
                            $fromcolor = $this->grad_fromcolor;
                            $tocolor = $this->grad_tocolor;
                            $style = $this->grad_style;
                        } else {
                            $fromcolor = $this->grad_fromcolor[$i % $ng][0];
                            $tocolor = $this->grad_fromcolor[$i % $ng][1];
                            $style = $this->grad_fromcolor[$i % $ng][2];
                        }
                    } else {
                        $fromcolor = $this->grad_fromcolor[$i % $ng][0];
                        $tocolor = $this->grad_fromcolor[$i % $ng][1];
                        $style = $this->grad_fromcolor[$i % $ng][2];
                    }
                    $grad->FilledRectangle($pts[2], $pts[3],
                        $pts[6], $pts[7],
                        $fromcolor, $tocolor, $style);
                } else {
                    $grad->FilledRectangle($pts[2], $pts[3],
                        $pts[6], $pts[7],
                        $this->grad_fromcolor, $this->grad_tocolor, $this->grad_style);
                }
            } elseif (!empty($this->fill_color)) {
                if (is_array($this->fill_color)) {
                    $img->PushColor($this->fill_color[$i % count($this->fill_color)]);
                } else {
                    $img->PushColor($this->fill_color);
                }
                $img->FilledPolygon($pts);
                $img->PopColor();
            }

            /////////////////////////kokorahen rectangle polygon//////////////////////

            // Remember value of this bar
            $val = $this->coords[0][$i];

            if (!empty($val) && !is_numeric($val)) {
                Util\JpGraphError::RaiseL(2004, $i, $val);
                //'All values for a barplot must be numeric. You have specified value['.$i.'] == \''.$val.'\'');
            }

            // Determine the shadow
            if ($this->bar_shadow && $val != 0) {

                $ssh = $this->bar_shadow_hsize;
                $ssv = $this->bar_shadow_vsize;
                // Create points to create a "upper-right" shadow
                if ($val > 0) {
                    $sp[0] = $pts[6];
                    $sp[1] = $pts[7];
                    $sp[2] = $pts[4];
                    $sp[3] = $pts[5];
                    $sp[4] = $pts[2];
                    $sp[5] = $pts[3];
                    $sp[6] = $pts[2] + $ssh;
                    $sp[7] = $pts[3] - $ssv;
                    $sp[8] = $pts[4] + $ssh;
                    $sp[9] = $pts[5] - $ssv;
                    $sp[10] = $pts[6] + $ssh;
                    $sp[11] = $pts[7] - $ssv;
                } elseif ($val < 0) {
                    $sp[0] = $pts[4];
                    $sp[1] = $pts[5];
                    $sp[2] = $pts[6];
                    $sp[3] = $pts[7];
                    $sp[4] = $pts[0];
                    $sp[5] = $pts[1];
                    $sp[6] = $pts[0] + $ssh;
                    $sp[7] = $pts[1] - $ssv;
                    $sp[8] = $pts[6] + $ssh;
                    $sp[9] = $pts[7] - $ssv;
                    $sp[10] = $pts[4] + $ssh;
                    $sp[11] = $pts[5] - $ssv;
                }
                if (is_array($this->bar_shadow_color)) {
                    $numcolors = count($this->bar_shadow_color);
                    if ($numcolors == 0) {
                        Util\JpGraphError::RaiseL(2005); //('You have specified an empty array for shadow colors in the bar plot.');
                    }
                    $img->PushColor($this->bar_shadow_color[$i % $numcolors]);
                } else {
                    $img->PushColor($this->bar_shadow_color);
                }
                $img->FilledPolygon($sp);
                $img->PopColor();

            } elseif ($this->bar_3d && $val != 0) {
                // Determine the 3D

                $ssh = $this->bar_3d_hsize;
                $ssv = $this->bar_3d_vsize;

                // Create points to create a "upper-right" shadow
                if ($val > 0) {
                    $sp1[0] = $pts[6];
                    $sp1[1] = $pts[7];
                    $sp1[2] = $pts[4];
                    $sp1[3] = $pts[5];
                    $sp1[4] = $pts[4] + $ssh;
                    $sp1[5] = $pts[5] - $ssv;
                    $sp1[6] = $pts[6] + $ssh;
                    $sp1[7] = $pts[7] - $ssv;

                    $sp2[0] = $pts[4];
                    $sp2[1] = $pts[5];
                    $sp2[2] = $pts[2];
                    $sp2[3] = $pts[3];
                    $sp2[4] = $pts[2] + $ssh;
                    $sp2[5] = $pts[3] - $ssv;
                    $sp2[6] = $pts[4] + $ssh;
                    $sp2[7] = $pts[5] - $ssv;

                } elseif ($val < 0) {
                    $sp1[0] = $pts[4];
                    $sp1[1] = $pts[5];
                    $sp1[2] = $pts[6];
                    $sp1[3] = $pts[7];
                    $sp1[4] = $pts[6] + $ssh;
                    $sp1[5] = $pts[7] - $ssv;
                    $sp1[6] = $pts[4] + $ssh;
                    $sp1[7] = $pts[5] - $ssv;

                    $sp2[0] = $pts[6];
                    $sp2[1] = $pts[7];
                    $sp2[2] = $pts[0];
                    $sp2[3] = $pts[1];
                    $sp2[4] = $pts[0] + $ssh;
                    $sp2[5] = $pts[1] - $ssv;
                    $sp2[6] = $pts[6] + $ssh;
                    $sp2[7] = $pts[7] - $ssv;
                }

                $base_color = $this->fill_color;

                $img->PushColor($base_color . ':0.7');
                $img->FilledPolygon($sp1);
                $img->PopColor();

                $img->PushColor($base_color . ':1.1');
                $img->FilledPolygon($sp2);
                $img->PopColor();
            }

            // Stroke the pattern
            if (is_array($this->iPattern)) {
                $f = new RectPatternFactory();
                if (is_array($this->iPatternColor)) {
                    $pcolor = $this->iPatternColor[$i % $np];
                } else {
                    $pcolor = $this->iPatternColor;
                }
                $prect = $f->Create($this->iPattern[$i % $np], $pcolor, 1);
                $prect->SetDensity($this->iPatternDensity[$i % $np]);

                if ($val < 0) {
                    $rx = $pts[0];
                    $ry = $pts[1];
                } else {
                    $rx = $pts[2];
                    $ry = $pts[3];
                }
                $width = abs($pts[4] - $pts[0]) + 1;
                $height = abs($pts[1] - $pts[3]) + 1;
                $prect->SetPos(new Rectangle($rx, $ry, $width, $height));
                $prect->Stroke($img);
            } else {
                if ($this->iPattern > -1) {
                    $f = new RectPatternFactory();
                    $prect = $f->Create($this->iPattern, $this->iPatternColor, 1);
                    $prect->SetDensity($this->iPatternDensity);
                    if ($val < 0) {
                        $rx = $pts[0];
                        $ry = $pts[1];
                    } else {
                        $rx = $pts[2];
                        $ry = $pts[3];
                    }
                    $width = abs($pts[4] - $pts[0]) + 1;
                    $height = abs($pts[1] - $pts[3]) + 1;
                    $prect->SetPos(new Rectangle($rx, $ry, $width, $height));
                    $prect->Stroke($img);
                }
            }

            // Stroke the outline of the bar
            if (is_array($this->color)) {
                $img->SetColor($this->color[$i % count($this->color)]);
            } else {
                $img->SetColor($this->color);
            }

            $pts[] = $pts[0];
            $pts[] = $pts[1];

            if ($this->weight > 0) {
                $img->SetLineWeight($this->weight);
                $img->Polygon($pts);
            }

            // Determine how to best position the values of the individual bars
            $x = $pts[2] + ($pts[4] - $pts[2]) / 2;
            $this->value->SetMargin(5);

            if ($this->valuepos == 'top') {
                $y = $pts[3];
                if ($img->a === 90) {
                    if ($val < 0) {
                        $this->value->SetAlign('right', 'center');
                    } else {
                        $this->value->SetAlign('left', 'center');
                    }

                } else {
                    if ($val < 0) {
                        $this->value->SetMargin(-5);
                        $y = $pts[1];
                        $this->value->SetAlign('center', 'bottom');
                    } else {
                        $this->value->SetAlign('center', 'bottom');
                    }

                }
                $this->value->Stroke($img, $val, $x, $y);
            } elseif ($this->valuepos == 'max') {
                $y = $pts[3];
                if ($img->a === 90) {
                    if ($val < 0) {
                        $this->value->SetAlign('left', 'center');
                    } else {
                        $this->value->SetAlign('right', 'center');
                    }

                } else {
                    if ($val < 0) {
                        $this->value->SetAlign('center', 'bottom');
                    } else {
                        $this->value->SetAlign('center', 'top');
                    }
                }
                $this->value->SetMargin(-5);
                $this->value->Stroke($img, $val, $x, $y);
            } elseif ($this->valuepos == 'center') {
                $y = ($pts[3] + $pts[1]) / 2;
                $this->value->SetAlign('center', 'center');
                $this->value->SetMargin(0);
                $this->value->Stroke($img, $val, $x, $y);
            } elseif ($this->valuepos == 'bottom' || $this->valuepos == 'min') {
                $y = $pts[1];
                if ($img->a === 90) {
                    if ($val < 0) {
                        $this->value->SetAlign('right', 'center');
                    } else {
                        $this->value->SetAlign('left', 'center');
                    }

                }
                $this->value->SetMargin(3);
                $this->value->Stroke($img, $val, $x, $y);
            } else {
                Util\JpGraphError::RaiseL(2006, $this->valuepos);
                //'Unknown position for values on bars :'.$this->valuepos);
            }
            // Create the client side image map
            $rpts = $img->ArrRotate($pts);
            $csimcoord = round($rpts[0]) . ", " . round($rpts[1]);
            for ($j = 1; $j < 4; ++$j) {
                $csimcoord .= ", " . round($rpts[2 * $j]) . ", " . round($rpts[2 * $j + 1]);
            }
            if (!empty($this->csimtargets[$i])) {
                $this->csimareas .= '<area shape="poly" coords="' . $csimcoord . '" ';
                $this->csimareas .= " href=\"" . htmlentities($this->csimtargets[$i]) . "\"";

                if (!empty($this->csimwintargets[$i])) {
                    $this->csimareas .= " target=\"" . $this->csimwintargets[$i] . "\" ";
                }

                $sval = '';
                if (!empty($this->csimalts[$i])) {
                    $sval = sprintf($this->csimalts[$i], $this->coords[0][$i]);
                    $this->csimareas .= " title=\"$sval\" alt=\"$sval\" ";
                }
                $this->csimareas .= " />\n";
            }
        }
        return true;
    }
} // Class

/* EOF */
