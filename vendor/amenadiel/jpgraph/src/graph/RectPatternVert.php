<?php
namespace Amenadiel\JpGraph\Graph;

//=====================================================================
// Class RectPatternVert
// Implements vertical line pattern
//=====================================================================
class RectPatternVert extends RectPattern
{
    public function __construct($aColor = "black", $aWeight = 1, $aLineSpacing = 7)
    {
        parent::__construct($aColor, $aWeight);
        $this->linespacing = $aLineSpacing;
    }

    //--------------------
    // Private methods
    //
    public function DoPattern($aImg)
    {
        $x  = $this->rect->x;
        $y0 = $this->rect->y;
        $y1 = $this->rect->ye;
        while ($x < $this->rect->xe) {
            $aImg->Line($x, $y0, $x, $y1);
            $x += $this->linespacing;
        }
    }
}
