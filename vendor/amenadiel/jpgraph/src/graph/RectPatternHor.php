<?php
namespace Amenadiel\JpGraph\Graph;

//=====================================================================
// Class RectPatternHor
// Implements horizontal line pattern
//=====================================================================
class RectPatternHor extends RectPattern
{

    public function __construct($aColor = "black", $aWeight = 1, $aLineSpacing = 7)
    {
        parent::__construct($aColor, $aWeight);
        $this->linespacing = $aLineSpacing;
    }

    public function DoPattern($aImg)
    {
        $x0 = $this->rect->x;
        $x1 = $this->rect->xe;
        $y = $this->rect->y;
        while ($y < $this->rect->ye) {
            $aImg->Line($x0, $y, $x1, $y);
            $y += $this->linespacing;
        }
    }
}
