<?php
namespace Amenadiel\JpGraph\Plot;

/*=======================================================================
// File:        JPGRAPH_WINDROSE.PHP
// Description: Windrose extension for JpGraph
// Created:     2003-09-17
// Ver:         $Id: jpgraph_windrose.php 1928 2010-01-11 19:56:51Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

//------------------------------------------------------------------------
// Determine how many compass directions to show
//------------------------------------------------------------------------

//===================================================
// CLASS LegendStyle
//===================================================
class LegendStyle
{
    public $iLength                 = 40;
    public $iMargin                 = 20;
    public $iBottomMargin           = 5;
    public $iCircleWeight           = 2;
    public $iCircleRadius           = 18;
    public $iCircleColor            = 'black';
    public $iTxtFontFamily          = FF_VERDANA;
    public $iTxtFontStyle           = FS_NORMAL;
    public $iTxtFontSize            = 8;
    public $iLblFontFamily          = FF_VERDANA;
    public $iLblFontStyle           = FS_NORMAL;
    public $iLblFontSize            = 8;
    public $iCircleFontFamily       = FF_VERDANA;
    public $iCircleFontStyle        = FS_NORMAL;
    public $iCircleFontSize         = 8;
    public $iLblFontColor           = 'black';
    public $iTxtFontColor           = 'black';
    public $iCircleFontColor        = 'black';
    public $iShow                   = true;
    public $iFormatString           = '%.1f';
    public $iTxtMargin              = 6;
    public $iTxt                    = '';
    public $iZCircleTxt             = 'Calm';

    public function SetFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iLblFontFamily    = $aFontFamily;
        $this->iLblFontStyle     = $aFontStyle;
        $this->iLblFontSize      = $aFontSize;
        $this->iTxtFontFamily    = $aFontFamily;
        $this->iTxtFontStyle     = $aFontStyle;
        $this->iTxtFontSize      = $aFontSize;
        $this->iCircleFontFamily = $aFontFamily;
        $this->iCircleFontStyle  = $aFontStyle;
        $this->iCircleFontSize   = $aFontSize;
    }

    public function SetLFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iLblFontFamily = $aFontFamily;
        $this->iLblFontStyle  = $aFontStyle;
        $this->iLblFontSize   = $aFontSize;
    }

    public function SetTFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iTxtFontFamily = $aFontFamily;
        $this->iTxtFontStyle  = $aFontStyle;
        $this->iTxtFontSize   = $aFontSize;
    }

    public function SetCFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iCircleFontFamily = $aFontFamily;
        $this->iCircleFontStyle  = $aFontStyle;
        $this->iCircleFontSize   = $aFontSize;
    }

    public function SetFontColor($aColor)
    {
        $this->iTxtFontColor    = $aColor;
        $this->iLblFontColor    = $aColor;
        $this->iCircleFontColor = $aColor;
    }

    public function SetTFontColor($aColor)
    {
        $this->iTxtFontColor = $aColor;
    }

    public function SetLFontColor($aColor)
    {
        $this->iLblFontColor = $aColor;
    }

    public function SetCFontColor($aColor)
    {
        $this->iCircleFontColor = $aColor;
    }

    public function SetCircleWeight($aWeight)
    {
        $this->iCircleWeight = $aWeight;
    }

    public function SetCircleRadius($aRadius)
    {
        $this->iCircleRadius = $aRadius;
    }

    public function SetCircleColor($aColor)
    {
        $this->iCircleColor = $aColor;
    }

    public function SetCircleText($aTxt)
    {
        $this->iZCircleTxt = $aTxt;
    }

    public function SetMargin($aMarg, $aBottomMargin = 5)
    {
        $this->iMargin       = $aMarg;
        $this->iBottomMargin = $aBottomMargin;
    }

    public function SetLength($aLength)
    {
        $this->iLength = $aLength;
    }

    public function Show($aFlg = true)
    {
        $this->iShow = $aFlg;
    }

    public function Hide($aFlg = true)
    {
        $this->iShow = !$aFlg;
    }

    public function SetFormat($aFmt)
    {
        $this->iFormatString = $aFmt;
    }

    public function SetText($aTxt)
    {
        $this->iTxt = $aTxt;
    }
}
