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
    public $iLength = 40, $iMargin = 20, $iBottomMargin = 5;
    public $iCircleWeight = 2, $iCircleRadius = 18, $iCircleColor = 'black';
    public $iTxtFontFamily = FF_VERDANA, $iTxtFontStyle = FS_NORMAL, $iTxtFontSize = 8;
    public $iLblFontFamily = FF_VERDANA, $iLblFontStyle = FS_NORMAL, $iLblFontSize = 8;
    public $iCircleFontFamily = FF_VERDANA, $iCircleFontStyle = FS_NORMAL, $iCircleFontSize = 8;
    public $iLblFontColor = 'black', $iTxtFontColor = 'black', $iCircleFontColor = 'black';
    public $iShow = true;
    public $iFormatString = '%.1f';
    public $iTxtMargin = 6, $iTxt = '';
    public $iZCircleTxt = 'Calm';

    public function SetFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iLblFontFamily = $aFontFamily;
        $this->iLblFontStyle = $aFontStyle;
        $this->iLblFontSize = $aFontSize;
        $this->iTxtFontFamily = $aFontFamily;
        $this->iTxtFontStyle = $aFontStyle;
        $this->iTxtFontSize = $aFontSize;
        $this->iCircleFontFamily = $aFontFamily;
        $this->iCircleFontStyle = $aFontStyle;
        $this->iCircleFontSize = $aFontSize;
    }

    public function SetLFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iLblFontFamily = $aFontFamily;
        $this->iLblFontStyle = $aFontStyle;
        $this->iLblFontSize = $aFontSize;
    }

    public function SetTFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iTxtFontFamily = $aFontFamily;
        $this->iTxtFontStyle = $aFontStyle;
        $this->iTxtFontSize = $aFontSize;
    }

    public function SetCFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iCircleFontFamily = $aFontFamily;
        $this->iCircleFontStyle = $aFontStyle;
        $this->iCircleFontSize = $aFontSize;
    }

    public function SetFontColor($aColor)
    {
        $this->iTxtFontColor = $aColor;
        $this->iLblFontColor = $aColor;
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
        $this->iMargin = $aMarg;
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
