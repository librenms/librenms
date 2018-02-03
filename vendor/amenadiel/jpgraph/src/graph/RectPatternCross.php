<?php
namespace Amenadiel\JpGraph\Graph;

//=====================================================================
// Class RectPatternCross
// Vert/Hor crosses
//=====================================================================
class RectPatternCross extends RectPattern
{
    private $vert = null;
    private $hor  = null;
    public function __construct($aColor = "black", $aWeight = 1)
    {
        parent::__construct($aColor, $aWeight);
        $this->vert = new RectPatternVert($aColor, $aWeight);
        $this->hor  = new RectPatternHor($aColor, $aWeight);
    }

    public function SetOrder($aDepth)
    {
        $this->vert->SetOrder($aDepth);
        $this->hor->SetOrder($aDepth);
    }

    public function SetPos($aRect)
    {
        parent::SetPos($aRect);
        $this->vert->SetPos($aRect);
        $this->hor->SetPos($aRect);
    }

    public function SetDensity($aDens)
    {
        $this->vert->SetDensity($aDens);
        $this->hor->SetDensity($aDens);
    }

    public function DoPattern($aImg)
    {
        $this->vert->DoPattern($aImg);
        $this->hor->DoPattern($aImg);
    }
}
