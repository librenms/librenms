<?php
namespace Amenadiel\JpGraph\Graph;

/*=======================================================================
// File:        JPGRAPH_CANVTOOLS.PHP
// Description: Some utilities for text and shape drawing on a canvas
// Created:     2002-08-23
// Ver:         $Id: jpgraph_canvtools.php 1857 2009-09-28 14:38:14Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

define('CORNER_TOPLEFT', 0);
define('CORNER_TOPRIGHT', 1);
define('CORNER_BOTTOMRIGHT', 2);
define('CORNER_BOTTOMLEFT', 3);

//===================================================
// CLASS Shape
// Description: Methods to draw shapes on canvas
//===================================================
class Shape
{
    private $img;
    private $scale;

    public function __construct($aGraph, $scale)
    {
        $this->img = $aGraph->img;
        $this->img->SetColor('black');
        $this->scale = $scale;
    }

    public function SetColor($aColor)
    {
        $this->img->SetColor($aColor);
    }

    public function Line($x1, $y1, $x2, $y2)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        list($x2, $y2) = $this->scale->Translate($x2, $y2);
        $this->img->Line($x1, $y1, $x2, $y2);
    }

    public function SetLineWeight($aWeight)
    {
        $this->img->SetLineWeight($aWeight);
    }

    public function Polygon($p, $aClosed = false)
    {
        $n = count($p);
        for ($i = 0; $i < $n; $i += 2) {
            $p[$i]     = $this->scale->TranslateX($p[$i]);
            $p[$i + 1] = $this->scale->TranslateY($p[$i + 1]);
        }
        $this->img->Polygon($p, $aClosed);
    }

    public function FilledPolygon($p)
    {
        $n = count($p);
        for ($i = 0; $i < $n; $i += 2) {
            $p[$i]     = $this->scale->TranslateX($p[$i]);
            $p[$i + 1] = $this->scale->TranslateY($p[$i + 1]);
        }
        $this->img->FilledPolygon($p);
    }

    // Draw a bezier curve with defining points in the $aPnts array
    // using $aSteps steps.
    // 0=x0, 1=y0
    // 2=x1, 3=y1
    // 4=x2, 5=y2
    // 6=x3, 7=y3
    public function Bezier($p, $aSteps = 40)
    {
        $x0 = $p[0];
        $y0 = $p[1];
        // Calculate coefficients
        $cx = 3 * ($p[2] - $p[0]);
        $bx = 3 * ($p[4] - $p[2]) - $cx;
        $ax = $p[6] - $p[0] - $cx - $bx;
        $cy = 3 * ($p[3] - $p[1]);
        $by = 3 * ($p[5] - $p[3]) - $cy;
        $ay = $p[7] - $p[1] - $cy - $by;

        // Step size
        $delta = 1.0 / $aSteps;

        $x_old = $x0;
        $y_old = $y0;
        for ($t = $delta; $t <= 1.0; $t += $delta) {
            $tt  = $t * $t;
            $ttt = $tt * $t;
            $x   = $ax * $ttt + $bx * $tt + $cx * $t + $x0;
            $y   = $ay * $ttt + $by * $tt + $cy * $t + $y0;
            $this->Line($x_old, $y_old, $x, $y);
            $x_old = $x;
            $y_old = $y;
        }
        $this->Line($x_old, $y_old, $p[6], $p[7]);
    }

    public function Rectangle($x1, $y1, $x2, $y2)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        list($x2, $y2) = $this->scale->Translate($x2, $y2);
        $this->img->Rectangle($x1, $y1, $x2, $y2);
    }

    public function FilledRectangle($x1, $y1, $x2, $y2)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        list($x2, $y2) = $this->scale->Translate($x2, $y2);
        $this->img->FilledRectangle($x1, $y1, $x2, $y2);
    }

    public function Circle($x1, $y1, $r)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        if ($r >= 0) {
            $r = $this->scale->TranslateX($r);
        } else {
            $r = -$r;
        }

        $this->img->Circle($x1, $y1, $r);
    }

    public function FilledCircle($x1, $y1, $r)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        if ($r >= 0) {
            $r = $this->scale->TranslateX($r);
        } else {
            $r = -$r;
        }

        $this->img->FilledCircle($x1, $y1, $r);
    }

    public function RoundedRectangle($x1, $y1, $x2, $y2, $r = null)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        list($x2, $y2) = $this->scale->Translate($x2, $y2);

        if ($r == null) {
            $r = 5;
        } elseif ($r >= 0) {
            $r = $this->scale->TranslateX($r);
        } else {
            $r = -$r;
        }

        $this->img->RoundedRectangle($x1, $y1, $x2, $y2, $r);
    }

    public function FilledRoundedRectangle($x1, $y1, $x2, $y2, $r = null)
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        list($x2, $y2) = $this->scale->Translate($x2, $y2);

        if ($r == null) {
            $r = 5;
        } elseif ($r > 0) {
            $r = $this->scale->TranslateX($r);
        } else {
            $r = -$r;
        }

        $this->img->FilledRoundedRectangle($x1, $y1, $x2, $y2, $r);
    }

    public function ShadowRectangle($x1, $y1, $x2, $y2, $fcolor = false, $shadow_width = null, $shadow_color = array(102, 102, 102))
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        list($x2, $y2) = $this->scale->Translate($x2, $y2);
        if ($shadow_width == null) {
            $shadow_width = 4;
        } else {
            $shadow_width = $this->scale->TranslateX($shadow_width);
        }

        $this->img->ShadowRectangle($x1, $y1, $x2, $y2, $fcolor, $shadow_width, $shadow_color);
    }

    public function SetTextAlign($halign, $valign = "bottom")
    {
        $this->img->SetTextAlign($halign, $valign = "bottom");
    }

    public function StrokeText($x1, $y1, $txt, $dir = 0, $paragraph_align = "left")
    {
        list($x1, $y1) = $this->scale->Translate($x1, $y1);
        $this->img->StrokeText($x1, $y1, $txt, $dir, $paragraph_align);
    }

    // A rounded rectangle where one of the corner has been moved "into" the
    // rectangle 'iw' width and 'ih' height. Corners:
    // 0=Top left, 1=top right, 2=bottom right, 3=bottom left
    public function IndentedRectangle($xt, $yt, $w, $h, $iw = 0, $ih = 0, $aCorner = 3, $aFillColor = "", $r = 4)
    {
        list($xt, $yt) = $this->scale->Translate($xt, $yt);
        list($w, $h)   = $this->scale->Translate($w, $h);
        list($iw, $ih) = $this->scale->Translate($iw, $ih);

        $xr = $xt + $w - 0;
        $yl = $yt + $h - 0;

        switch ($aCorner) {
            case 0: // Upper left

                // Bottom line, left &  right arc
                $this->img->Line($xt + $r, $yl, $xr - $r, $yl);
                $this->img->Arc($xt + $r, $yl - $r, $r * 2, $r * 2, 90, 180);
                $this->img->Arc($xr - $r, $yl - $r, $r * 2, $r * 2, 0, 90);

                // Right line, Top right arc
                $this->img->Line($xr, $yt + $r, $xr, $yl - $r);
                $this->img->Arc($xr - $r, $yt + $r, $r * 2, $r * 2, 270, 360);

                // Top line, Top left arc
                $this->img->Line($xt + $iw + $r, $yt, $xr - $r, $yt);
                $this->img->Arc($xt + $iw + $r, $yt + $r, $r * 2, $r * 2, 180, 270);

                // Left line
                $this->img->Line($xt, $yt + $ih + $r, $xt, $yl - $r);

                // Indent horizontal, Lower left arc
                $this->img->Line($xt + $r, $yt + $ih, $xt + $iw - $r, $yt + $ih);
                $this->img->Arc($xt + $r, $yt + $ih + $r, $r * 2, $r * 2, 180, 270);

                // Indent vertical, Indent arc
                $this->img->Line($xt + $iw, $yt + $r, $xt + $iw, $yt + $ih - $r);
                $this->img->Arc($xt + $iw - $r, $yt + $ih - $r, $r * 2, $r * 2, 0, 90);

                if ($aFillColor != '') {
                    $bc = $this->img->current_color_name;
                    $this->img->PushColor($aFillColor);
                    $this->img->FillToBorder($xr - $r, $yl - $r, $bc);
                    $this->img->PopColor();
                }

                break;

            case 1: // Upper right

                // Bottom line, left &  right arc
                $this->img->Line($xt + $r, $yl, $xr - $r, $yl);
                $this->img->Arc($xt + $r, $yl - $r, $r * 2, $r * 2, 90, 180);
                $this->img->Arc($xr - $r, $yl - $r, $r * 2, $r * 2, 0, 90);

                // Left line, Top left arc
                $this->img->Line($xt, $yt + $r, $xt, $yl - $r);
                $this->img->Arc($xt + $r, $yt + $r, $r * 2, $r * 2, 180, 270);

                // Top line, Top right arc
                $this->img->Line($xt + $r, $yt, $xr - $iw - $r, $yt);
                $this->img->Arc($xr - $iw - $r, $yt + $r, $r * 2, $r * 2, 270, 360);

                // Right line
                $this->img->Line($xr, $yt + $ih + $r, $xr, $yl - $r);

                // Indent horizontal, Lower right arc
                $this->img->Line($xr - $iw + $r, $yt + $ih, $xr - $r, $yt + $ih);
                $this->img->Arc($xr - $r, $yt + $ih + $r, $r * 2, $r * 2, 270, 360);

                // Indent vertical, Indent arc
                $this->img->Line($xr - $iw, $yt + $r, $xr - $iw, $yt + $ih - $r);
                $this->img->Arc($xr - $iw + $r, $yt + $ih - $r, $r * 2, $r * 2, 90, 180);

                if ($aFillColor != '') {
                    $bc = $this->img->current_color_name;
                    $this->img->PushColor($aFillColor);
                    $this->img->FillToBorder($xt + $r, $yl - $r, $bc);
                    $this->img->PopColor();
                }

                break;

            case 2: // Lower right
                // Top line, Top left & Top right arc
                $this->img->Line($xt + $r, $yt, $xr - $r, $yt);
                $this->img->Arc($xt + $r, $yt + $r, $r * 2, $r * 2, 180, 270);
                $this->img->Arc($xr - $r, $yt + $r, $r * 2, $r * 2, 270, 360);

                // Left line, Bottom left arc
                $this->img->Line($xt, $yt + $r, $xt, $yl - $r);
                $this->img->Arc($xt + $r, $yl - $r, $r * 2, $r * 2, 90, 180);

                // Bottom line, Bottom right arc
                $this->img->Line($xt + $r, $yl, $xr - $iw - $r, $yl);
                $this->img->Arc($xr - $iw - $r, $yl - $r, $r * 2, $r * 2, 0, 90);

                // Right line
                $this->img->Line($xr, $yt + $r, $xr, $yl - $ih - $r);

                // Indent horizontal, Lower right arc
                $this->img->Line($xr - $r, $yl - $ih, $xr - $iw + $r, $yl - $ih);
                $this->img->Arc($xr - $r, $yl - $ih - $r, $r * 2, $r * 2, 0, 90);

                // Indent vertical, Indent arc
                $this->img->Line($xr - $iw, $yl - $r, $xr - $iw, $yl - $ih + $r);
                $this->img->Arc($xr - $iw + $r, $yl - $ih + $r, $r * 2, $r * 2, 180, 270);

                if ($aFillColor != '') {
                    $bc = $this->img->current_color_name;
                    $this->img->PushColor($aFillColor);
                    $this->img->FillToBorder($xt + $r, $yt + $r, $bc);
                    $this->img->PopColor();
                }

                break;

            case 3: // Lower left
                // Top line, Top left & Top right arc
                $this->img->Line($xt + $r, $yt, $xr - $r, $yt);
                $this->img->Arc($xt + $r, $yt + $r, $r * 2, $r * 2, 180, 270);
                $this->img->Arc($xr - $r, $yt + $r, $r * 2, $r * 2, 270, 360);

                // Right line, Bottom right arc
                $this->img->Line($xr, $yt + $r, $xr, $yl - $r);
                $this->img->Arc($xr - $r, $yl - $r, $r * 2, $r * 2, 0, 90);

                // Bottom line, Bottom left arc
                $this->img->Line($xt + $iw + $r, $yl, $xr - $r, $yl);
                $this->img->Arc($xt + $iw + $r, $yl - $r, $r * 2, $r * 2, 90, 180);

                // Left line
                $this->img->Line($xt, $yt + $r, $xt, $yl - $ih - $r);

                // Indent horizontal, Lower left arc
                $this->img->Line($xt + $r, $yl - $ih, $xt + $iw - $r, $yl - $ih);
                $this->img->Arc($xt + $r, $yl - $ih - $r, $r * 2, $r * 2, 90, 180);

                // Indent vertical, Indent arc
                $this->img->Line($xt + $iw, $yl - $ih + $r, $xt + $iw, $yl - $r);
                $this->img->Arc($xt + $iw - $r, $yl - $ih + $r, $r * 2, $r * 2, 270, 360);

                if ($aFillColor != '') {
                    $bc = $this->img->current_color_name;
                    $this->img->PushColor($aFillColor);
                    $this->img->FillToBorder($xr - $r, $yt + $r, $bc);
                    $this->img->PopColor();
                }

                break;
        }
    }
}
