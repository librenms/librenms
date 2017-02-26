<?php
namespace Amenadiel\JpGraph\Image;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS Progress
// Holds parameters for the progress indicator
// displyed within a bar
//===================================================
class Progress
{
    public $iProgress = -1;
    public $iPattern = GANTT_SOLID;
    public $iColor = "black", $iFillColor = 'black';
    public $iDensity = 98, $iHeight = 0.65;

    public function Set($aProg)
    {
        if ($aProg < 0.0 || $aProg > 1.0) {
            Util\JpGraphError::RaiseL(6027);
            //("Progress value must in range [0, 1]");
        }
        $this->iProgress = $aProg;
    }

    public function SetPattern($aPattern, $aColor = "blue", $aDensity = 98)
    {
        $this->iPattern = $aPattern;
        $this->iColor = $aColor;
        $this->iDensity = $aDensity;
    }

    public function SetFillColor($aColor)
    {
        $this->iFillColor = $aColor;
    }

    public function SetHeight($aHeight)
    {
        $this->iHeight = $aHeight;
    }
}
