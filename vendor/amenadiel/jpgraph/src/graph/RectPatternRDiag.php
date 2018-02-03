<?php
namespace Amenadiel\JpGraph\Graph;

//=====================================================================
// Class RectPatternRDiag
// Implements right diagonal pattern
//=====================================================================
class RectPatternRDiag extends RectPattern
{
    public function __construct($aColor = "black", $aWeight = 1, $aLineSpacing = 12)
    {
        parent::__construct($aColor, $aWeight);
        $this->linespacing = $aLineSpacing;
    }

    public function DoPattern($aImg)
    {
        //  --------------------
        //  | /   /   /   /   /|
        //  |/   /   /   /   / |
        //  |   /   /   /   /  |
        //  --------------------
        $xe = $this->rect->xe;
        $ye = $this->rect->ye;
        $x0 = $this->rect->x + round($this->linespacing / 2);
        $y0 = $this->rect->y;
        $x1 = $this->rect->x;
        $y1 = $this->rect->y + round($this->linespacing / 2);

        while ($x0 <= $xe && $y1 <= $ye) {
            $aImg->Line($x0, $y0, $x1, $y1);
            $x0 += $this->linespacing;
            $y1 += $this->linespacing;
        }

        if ($xe - $x1 > $ye - $y0) {
            // Width larger than height
            $x1 = $this->rect->x + ($y1 - $ye);
            $y1 = $ye;
            $y0 = $this->rect->y;
            while ($x0 <= $xe) {
                $aImg->Line($x0, $y0, $x1, $y1);
                $x0 += $this->linespacing;
                $x1 += $this->linespacing;
            }

            $y0 = $this->rect->y + ($x0 - $xe);
            $x0 = $xe;
        } else {
            // Height larger than width
            $diff = $x0 - $xe;
            $y0   = $diff + $this->rect->y;
            $x0   = $xe;
            $x1   = $this->rect->x;
            while ($y1 <= $ye) {
                $aImg->Line($x0, $y0, $x1, $y1);
                $y1 += $this->linespacing;
                $y0 += $this->linespacing;
            }

            $diff = $y1 - $ye;
            $y1   = $ye;
            $x1   = $diff + $this->rect->x;
        }

        while ($y0 <= $ye) {
            $aImg->Line($x0, $y0, $x1, $y1);
            $y0 += $this->linespacing;
            $x1 += $this->linespacing;
        }
    }
}
