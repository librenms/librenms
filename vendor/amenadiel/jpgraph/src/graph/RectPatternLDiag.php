<?php
namespace Amenadiel\JpGraph\Graph;

//=====================================================================
// Class RectPatternLDiag
// Implements left diagonal pattern
//=====================================================================
class RectPatternLDiag extends RectPattern
{

    public function __construct($aColor = "black", $aWeight = 1, $aLineSpacing = 12)
    {
        $this->linespacing = $aLineSpacing;
        parent::__construct($aColor, $aWeight);
    }

    public function DoPattern($aImg)
    {
        //  --------------------
        //  |\   \   \   \   \ |
        //  | \   \   \   \   \|
        //  |  \   \   \   \   |
        //  |------------------|
        $xe = $this->rect->xe;
        $ye = $this->rect->ye;
        $x0 = $this->rect->x + round($this->linespacing / 2);
        $y0 = $this->rect->ye;
        $x1 = $this->rect->x;
        $y1 = $this->rect->ye - round($this->linespacing / 2);

        while ($x0 <= $xe && $y1 >= $this->rect->y) {
            $aImg->Line($x0, $y0, $x1, $y1);
            $x0 += $this->linespacing;
            $y1 -= $this->linespacing;
        }
        if ($xe - $x1 > $ye - $this->rect->y) {
            // Width larger than height
            $x1 = $this->rect->x + ($this->rect->y - $y1);
            $y0 = $ye;
            $y1 = $this->rect->y;
            while ($x0 <= $xe) {
                $aImg->Line($x0, $y0, $x1, $y1);
                $x0 += $this->linespacing;
                $x1 += $this->linespacing;
            }

            $y0 = $this->rect->ye - ($x0 - $xe);
            $x0 = $xe;
        } else {
            // Height larger than width
            $diff = $x0 - $xe;
            $y0 = $ye - $diff;
            $x0 = $xe;
            while ($y1 >= $this->rect->y) {
                $aImg->Line($x0, $y0, $x1, $y1);
                $y0 -= $this->linespacing;
                $y1 -= $this->linespacing;
            }
            $diff = $this->rect->y - $y1;
            $x1 = $this->rect->x + $diff;
            $y1 = $this->rect->y;
        }
        while ($y0 >= $this->rect->y) {
            $aImg->Line($x0, $y0, $x1, $y1);
            $y0 -= $this->linespacing;
            $x1 += $this->linespacing;
        }
    }
}
