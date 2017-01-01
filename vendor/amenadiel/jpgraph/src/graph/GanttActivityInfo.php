<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

/*=======================================================================
// File:        JPGRAPH_GANTT.PHP
// Description: JpGraph Gantt plot extension
// Created:     2001-11-12
// Ver:         $Id: jpgraph_gantt.php 1809 2009-09-09 13:07:33Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

require_once 'jpgraph_plotband.php';
require_once 'jpgraph_iconplot.php';
require_once 'jpgraph_plotmark.inc.php';

// Maximum size for Automatic Gantt chart
define('MAX_GANTTIMG_SIZE_W', 8000);
define('MAX_GANTTIMG_SIZE_H', 5000);

// Scale Header types
define("GANTT_HDAY", 1);
define("GANTT_HWEEK", 2);
define("GANTT_HMONTH", 4);
define("GANTT_HYEAR", 8);
define("GANTT_HHOUR", 16);
define("GANTT_HMIN", 32);

// Bar patterns
define("GANTT_RDIAG", BAND_RDIAG); // Right diagonal lines
define("GANTT_LDIAG", BAND_LDIAG); // Left diagonal lines
define("GANTT_SOLID", BAND_SOLID); // Solid one color
define("GANTT_VLINE", BAND_VLINE); // Vertical lines
define("GANTT_HLINE", BAND_HLINE); // Horizontal lines
define("GANTT_3DPLANE", BAND_3DPLANE); // "3D" Plane
define("GANTT_HVCROSS", BAND_HVCROSS); // Vertical/Hor crosses
define("GANTT_DIAGCROSS", BAND_DIAGCROSS); // Diagonal crosses

// Conversion constant
define("SECPERDAY", 3600 * 24);

// Locales. ONLY KEPT FOR BACKWARDS COMPATIBILITY
// You should use the proper locale strings directly
// from now on.
define("LOCALE_EN", "en_UK");
define("LOCALE_SV", "sv_SE");

// Layout of bars
define("GANTT_EVEN", 1);
define("GANTT_FROMTOP", 2);

// Style for minute header
define("MINUTESTYLE_MM", 0); // 15
define("MINUTESTYLE_CUSTOM", 2); // Custom format

// Style for hour header
define("HOURSTYLE_HM24", 0); // 13:10
define("HOURSTYLE_HMAMPM", 1); // 1:10pm
define("HOURSTYLE_H24", 2); // 13
define("HOURSTYLE_HAMPM", 3); // 1pm
define("HOURSTYLE_CUSTOM", 4); // User defined

// Style for day header
define("DAYSTYLE_ONELETTER", 0); // "M"
define("DAYSTYLE_LONG", 1); // "Monday"
define("DAYSTYLE_LONGDAYDATE1", 2); // "Monday 23 Jun"
define("DAYSTYLE_LONGDAYDATE2", 3); // "Monday 23 Jun 2003"
define("DAYSTYLE_SHORT", 4); // "Mon"
define("DAYSTYLE_SHORTDAYDATE1", 5); // "Mon 23/6"
define("DAYSTYLE_SHORTDAYDATE2", 6); // "Mon 23 Jun"
define("DAYSTYLE_SHORTDAYDATE3", 7); // "Mon 23"
define("DAYSTYLE_SHORTDATE1", 8); // "23/6"
define("DAYSTYLE_SHORTDATE2", 9); // "23 Jun"
define("DAYSTYLE_SHORTDATE3", 10); // "Mon 23"
define("DAYSTYLE_SHORTDATE4", 11); // "23"
define("DAYSTYLE_CUSTOM", 12); // "M"

// Styles for week header
define("WEEKSTYLE_WNBR", 0);
define("WEEKSTYLE_FIRSTDAY", 1);
define("WEEKSTYLE_FIRSTDAY2", 2);
define("WEEKSTYLE_FIRSTDAYWNBR", 3);
define("WEEKSTYLE_FIRSTDAY2WNBR", 4);

// Styles for month header
define("MONTHSTYLE_SHORTNAME", 0);
define("MONTHSTYLE_LONGNAME", 1);
define("MONTHSTYLE_LONGNAMEYEAR2", 2);
define("MONTHSTYLE_SHORTNAMEYEAR2", 3);
define("MONTHSTYLE_LONGNAMEYEAR4", 4);
define("MONTHSTYLE_SHORTNAMEYEAR4", 5);
define("MONTHSTYLE_FIRSTLETTER", 6);

// Types of constrain links
define('CONSTRAIN_STARTSTART', 0);
define('CONSTRAIN_STARTEND', 1);
define('CONSTRAIN_ENDSTART', 2);
define('CONSTRAIN_ENDEND', 3);

// Arrow direction for constrain links
define('ARROW_DOWN', 0);
define('ARROW_UP', 1);
define('ARROW_LEFT', 2);
define('ARROW_RIGHT', 3);

// Arrow type for constrain type
define('ARROWT_SOLID', 0);
define('ARROWT_OPEN', 1);

// Arrow size for constrain lines
define('ARROW_S1', 0);
define('ARROW_S2', 1);
define('ARROW_S3', 2);
define('ARROW_S4', 3);
define('ARROW_S5', 4);

// Activity types for use with utility method CreateSimple()
define('ACTYPE_NORMAL', 0);
define('ACTYPE_GROUP', 1);
define('ACTYPE_MILESTONE', 2);

define('ACTINFO_3D', 1);
define('ACTINFO_2D', 0);

// Check if array_fill() exists
if (!function_exists('array_fill')) {
    function array_fill($iStart, $iLen, $vValue)
    {
        $aResult = array();
        for ($iCount = $iStart; $iCount < $iLen + $iStart; $iCount++) {
            $aResult[$iCount] = $vValue;
        }
        return $aResult;
    }
}

//===================================================
// CLASS GanttActivityInfo
// Description:
//===================================================
class GanttActivityInfo
{
    public $iShow = true;
    public $iLeftColMargin = 4, $iRightColMargin = 1, $iTopColMargin = 1, $iBottomColMargin = 3;
    public $vgrid = null;
    private $iColor = 'black';
    private $iBackgroundColor = 'lightgray';
    private $iFFamily = FF_FONT1, $iFStyle = FS_NORMAL, $iFSize = 10, $iFontColor = 'black';
    private $iTitles = array();
    private $iWidth = array(), $iHeight = -1;
    private $iTopHeaderMargin = 4;
    private $iStyle = 1;
    private $iHeaderAlign = 'center';

    public function __construct()
    {
        $this->vgrid = new LineProperty();
    }

    public function Hide($aF = true)
    {
        $this->iShow = !$aF;
    }

    public function Show($aF = true)
    {
        $this->iShow = $aF;
    }

    // Specify font
    public function SetFont($aFFamily, $aFStyle = FS_NORMAL, $aFSize = 10)
    {
        $this->iFFamily = $aFFamily;
        $this->iFStyle = $aFStyle;
        $this->iFSize = $aFSize;
    }

    public function SetStyle($aStyle)
    {
        $this->iStyle = $aStyle;
    }

    public function SetColumnMargin($aLeft, $aRight)
    {
        $this->iLeftColMargin = $aLeft;
        $this->iRightColMargin = $aRight;
    }

    public function SetFontColor($aFontColor)
    {
        $this->iFontColor = $aFontColor;
    }

    public function SetColor($aColor)
    {
        $this->iColor = $aColor;
    }

    public function SetBackgroundColor($aColor)
    {
        $this->iBackgroundColor = $aColor;
    }

    public function SetColTitles($aTitles, $aWidth = null)
    {
        $this->iTitles = $aTitles;
        $this->iWidth = $aWidth;
    }

    public function SetMinColWidth($aWidths)
    {
        $n = min(count($this->iTitles), count($aWidths));
        for ($i = 0; $i < $n; ++$i) {
            if (!empty($aWidths[$i])) {
                if (empty($this->iWidth[$i])) {
                    $this->iWidth[$i] = $aWidths[$i];
                } else {
                    $this->iWidth[$i] = max($this->iWidth[$i], $aWidths[$i]);
                }
            }
        }
    }

    public function GetWidth($aImg)
    {
        $txt = new TextProperty();
        $txt->SetFont($this->iFFamily, $this->iFStyle, $this->iFSize);
        $n = count($this->iTitles);
        $rm = $this->iRightColMargin;
        $w = 0;
        for ($h = 0, $i = 0; $i < $n; ++$i) {
            $w += $this->iLeftColMargin;
            $txt->Set($this->iTitles[$i]);
            if (!empty($this->iWidth[$i])) {
                $w1 = max($txt->GetWidth($aImg) + $rm, $this->iWidth[$i]);
            } else {
                $w1 = $txt->GetWidth($aImg) + $rm;
            }
            $this->iWidth[$i] = $w1;
            $w += $w1;
            $h = max($h, $txt->GetHeight($aImg));
        }
        $this->iHeight = $h + $this->iTopHeaderMargin;
        $txt = '';
        return $w;
    }

    public function GetColStart($aImg, &$aStart, $aAddLeftMargin = false)
    {
        $n = count($this->iTitles);
        $adj = $aAddLeftMargin ? $this->iLeftColMargin : 0;
        $aStart = array($aImg->left_margin + $adj);
        for ($i = 1; $i < $n; ++$i) {
            $aStart[$i] = $aStart[$i - 1] + $this->iLeftColMargin + $this->iWidth[$i - 1];
        }
    }

    // Adjust headers left, right or centered
    public function SetHeaderAlign($aAlign)
    {
        $this->iHeaderAlign = $aAlign;
    }

    public function Stroke($aImg, $aXLeft, $aYTop, $aXRight, $aYBottom, $aUseTextHeight = false)
    {

        if (!$this->iShow) {
            return;
        }

        $txt = new TextProperty();
        $txt->SetFont($this->iFFamily, $this->iFStyle, $this->iFSize);
        $txt->SetColor($this->iFontColor);
        $txt->SetAlign($this->iHeaderAlign, 'top');
        $n = count($this->iTitles);

        if ($n == 0) {
            return;
        }

        $x = $aXLeft;
        $h = $this->iHeight;
        $yTop = $aUseTextHeight ? $aYBottom - $h - $this->iTopColMargin - $this->iBottomColMargin : $aYTop;

        if ($h < 0) {
            Util\JpGraphError::RaiseL(6001);
            //('Internal error. Height for ActivityTitles is < 0');
        }

        $aImg->SetLineWeight(1);
        // Set background color
        $aImg->SetColor($this->iBackgroundColor);
        $aImg->FilledRectangle($aXLeft, $yTop, $aXRight, $aYBottom - 1);

        if ($this->iStyle == 1) {
            // Make a 3D effect
            $aImg->SetColor('white');
            $aImg->Line($aXLeft, $yTop + 1, $aXRight, $yTop + 1);
        }

        for ($i = 0; $i < $n; ++$i) {
            if ($this->iStyle == 1) {
                // Make a 3D effect
                $aImg->SetColor('white');
                $aImg->Line($x + 1, $yTop, $x + 1, $aYBottom);
            }
            $x += $this->iLeftColMargin;
            $txt->Set($this->iTitles[$i]);

            // Adjust the text anchor position according to the choosen alignment
            $xp = $x;
            if ($this->iHeaderAlign == 'center') {
                $xp = (($x - $this->iLeftColMargin) + ($x + $this->iWidth[$i])) / 2;
            } elseif ($this->iHeaderAlign == 'right') {
                $xp = $x + $this->iWidth[$i] - $this->iRightColMargin;
            }

            $txt->Stroke($aImg, $xp, $yTop + $this->iTopHeaderMargin);
            $x += $this->iWidth[$i];
            if ($i < $n - 1) {
                $aImg->SetColor($this->iColor);
                $aImg->Line($x, $yTop, $x, $aYBottom);
            }
        }

        $aImg->SetColor($this->iColor);
        $aImg->Line($aXLeft, $yTop, $aXRight, $yTop);

        // Stroke vertical column dividers
        $cols = array();
        $this->GetColStart($aImg, $cols);
        $n = count($cols);
        for ($i = 1; $i < $n; ++$i) {
            $this->vgrid->Stroke($aImg, $cols[$i], $aYBottom, $cols[$i],
                $aImg->height - $aImg->bottom_margin);
        }
    }
}

//===================================================
// Global cache for builtin images
//===================================================
$_gPredefIcons = new PredefIcons();

// <EOF>
