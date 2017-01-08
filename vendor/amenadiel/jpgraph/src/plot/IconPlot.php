<?php
namespace Amenadiel\JpGraph\Plot;

//=======================================================================
// File:        JPGRAPH_ICONPLOT.PHP
// Description: Extension module to add icons to plots
// Created:     2004-02-18
// Ver:         $Id: jpgraph_iconplot.php 1404 2009-06-28 15:25:41Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================

//===================================================
// CLASS IconPlot
// Description: Make it possible to add a (small) image
// to the graph
//===================================================
class IconPlot
{
    public $iX = 0, $iY = 0, $iScale = 1.0, $iMix = 100;
    private $iHorAnchor = 'left', $iVertAnchor = 'top';
    private $iFile = '';
    private $iAnchors = array('left', 'right', 'top', 'bottom', 'center');
    private $iCountryFlag = '', $iCountryStdSize = 3;
    private $iScalePosY = null, $iScalePosX = null;
    private $iImgString = '';

    public function __construct($aFile = "", $aX = 0, $aY = 0, $aScale = 1.0, $aMix = 100)
    {
        $this->iFile = $aFile;
        $this->iX = $aX;
        $this->iY = $aY;
        $this->iScale = $aScale;
        if ($aMix < 0 || $aMix > 100) {
            Util\JpGraphError::RaiseL(8001); //('Mix value for icon must be between 0 and 100.');
        }
        $this->iMix = $aMix;
    }

    public function SetCountryFlag($aFlag, $aX = 0, $aY = 0, $aScale = 1.0, $aMix = 100, $aStdSize = 3)
    {
        $this->iCountryFlag = $aFlag;
        $this->iX = $aX;
        $this->iY = $aY;
        $this->iScale = $aScale;
        if ($aMix < 0 || $aMix > 100) {
            Util\JpGraphError::RaiseL(8001); //'Mix value for icon must be between 0 and 100.');
        }
        $this->iMix = $aMix;
        $this->iCountryStdSize = $aStdSize;
    }

    public function SetPos($aX, $aY)
    {
        $this->iX = $aX;
        $this->iY = $aY;
    }

    public function CreateFromString($aStr)
    {
        $this->iImgString = $aStr;
    }

    public function SetScalePos($aX, $aY)
    {
        $this->iScalePosX = $aX;
        $this->iScalePosY = $aY;
    }

    public function SetScale($aScale)
    {
        $this->iScale = $aScale;
    }

    public function SetMix($aMix)
    {
        if ($aMix < 0 || $aMix > 100) {
            Util\JpGraphError::RaiseL(8001); //('Mix value for icon must be between 0 and 100.');
        }
        $this->iMix = $aMix;
    }

    public function SetAnchor($aXAnchor = 'left', $aYAnchor = 'center')
    {
        if (!in_array($aXAnchor, $this->iAnchors) ||
            !in_array($aYAnchor, $this->iAnchors)) {
            Util\JpGraphError::RaiseL(8002); //("Anchor position for icons must be one of 'top', 'bottom', 'left', 'right' or 'center'");
        }
        $this->iHorAnchor = $aXAnchor;
        $this->iVertAnchor = $aYAnchor;
    }

    public function PreStrokeAdjust($aGraph)
    {
        // Nothing to do ...
    }

    public function DoLegend($aGraph)
    {
        // Nothing to do ...
    }

    public function Max()
    {
        return array(false, false);
    }

    // The next four function are framework function tht gets called
    // from Gantt and is not menaiungfull in the context of Icons but
    // they must be implemented to avoid errors.
    public function GetMaxDate()
    {return false;}

    public function GetMinDate()
    {return false;}

    public function GetLineNbr()
    {return 0;}

    public function GetAbsHeight()
    {return 0;}

    public function Min()
    {
        return array(false, false);
    }

    public function StrokeMargin(&$aImg)
    {
        return true;
    }

    public function Stroke($aImg, $axscale = null, $ayscale = null)
    {
        $this->StrokeWithScale($aImg, $axscale, $ayscale);
    }

    public function StrokeWithScale($aImg, $axscale, $ayscale)
    {
        if ($this->iScalePosX === null || $this->iScalePosY === null ||
            $axscale === null || $ayscale === null) {
            $this->_Stroke($aImg);
        } else {
            $this->_Stroke($aImg,
                round($axscale->Translate($this->iScalePosX)),
                round($ayscale->Translate($this->iScalePosY)));
        }
    }

    public function GetWidthHeight()
    {
        $dummy = 0;
        return $this->_Stroke($dummy, null, null, true);
    }

    public function _Stroke($aImg, $x = null, $y = null, $aReturnWidthHeight = false)
    {
        if ($this->iFile != '' && $this->iCountryFlag != '') {
            Util\JpGraphError::RaiseL(8003); //('It is not possible to specify both an image file and a country flag for the same icon.');
        }
        if ($this->iFile != '') {
            $gdimg = Graph::LoadBkgImage('', $this->iFile);
        } elseif ($this->iImgString != '') {
            $gdimg = Image::CreateFromString($this->iImgString);
        } else {
            if (!class_exists('FlagImages', false)) {
                Util\JpGraphError::RaiseL(8004); //('In order to use Country flags as icons you must include the "jpgraph_flags.php" file.');
            }
            $fobj = new FlagImages($this->iCountryStdSize);
            $dummy = '';
            $gdimg = $fobj->GetImgByName($this->iCountryFlag, $dummy);
        }

        $iconw = imagesx($gdimg);
        $iconh = imagesy($gdimg);

        if ($aReturnWidthHeight) {
            return array(round($iconw * $this->iScale), round($iconh * $this->iScale));
        }

        if ($x !== null && $y !== null) {
            $this->iX = $x;
            $this->iY = $y;
        }
        if ($this->iX >= 0 && $this->iX <= 1.0) {
            $w = imagesx($aImg->img);
            $this->iX = round($w * $this->iX);
        }
        if ($this->iY >= 0 && $this->iY <= 1.0) {
            $h = imagesy($aImg->img);
            $this->iY = round($h * $this->iY);
        }

        if ($this->iHorAnchor == 'center') {
            $this->iX -= round($iconw * $this->iScale / 2);
        }

        if ($this->iHorAnchor == 'right') {
            $this->iX -= round($iconw * $this->iScale);
        }

        if ($this->iVertAnchor == 'center') {
            $this->iY -= round($iconh * $this->iScale / 2);
        }

        if ($this->iVertAnchor == 'bottom') {
            $this->iY -= round($iconh * $this->iScale);
        }

        $aImg->CopyMerge($gdimg, $this->iX, $this->iY, 0, 0,
            round($iconw * $this->iScale), round($iconh * $this->iScale),
            $iconw, $iconh,
            $this->iMix);
    }
}
