<?php
namespace Amenadiel\JpGraph\Graph;

//=====================================================================
// Class RectPattern3DPlane
// Implements "3D" plane pattern
//=====================================================================
class RectPattern3DPlane extends RectPattern
{
    private $alpha = 50; // Parameter that specifies the distance
    // to "simulated" horizon in pixel from the
    // top of the band. Specifies how fast the lines
    // converge.

    public function __construct($aColor = "black", $aWeight = 1)
    {
        parent::__construct($aColor, $aWeight);
        $this->SetDensity(10); // Slightly larger default
    }

    public function SetHorizon($aHorizon)
    {
        $this->alpha = $aHorizon;
    }

    public function DoPattern($aImg)
    {
        // "Fake" a nice 3D grid-effect.
        $x0 = $this->rect->x + $this->rect->w / 2;
        $y0 = $this->rect->y;
        $x1 = $x0;
        $y1 = $this->rect->ye;
        $x0_right = $x0;
        $x1_right = $x1;

        // BTW "apa" means monkey in Swedish but is really a shortform for
        // "alpha+a" which was the labels I used on paper when I derived the
        // geometric to get the 3D perspective right.
        // $apa is the height of the bounding rectangle plus the distance to the
        // artifical horizon (alpha)
        $apa = $this->rect->h + $this->alpha;

        // Three cases and three loops
        // 1) The endpoint of the line ends on the bottom line
        // 2) The endpoint ends on the side
        // 3) Horizontal lines

        // Endpoint falls on bottom line
        $middle = $this->rect->x + $this->rect->w / 2;
        $dist = $this->linespacing;
        $factor = $this->alpha / ($apa);
        while ($x1 > $this->rect->x) {
            $aImg->Line($x0, $y0, $x1, $y1);
            $aImg->Line($x0_right, $y0, $x1_right, $y1);
            $x1 = $middle - $dist;
            $x0 = $middle - $dist * $factor;
            $x1_right = $middle + $dist;
            $x0_right = $middle + $dist * $factor;
            $dist += $this->linespacing;
        }

        // Endpoint falls on sides
        $dist -= $this->linespacing;
        $d = $this->rect->w / 2;
        $c = $apa - $d * $apa / $dist;
        while ($x0 > $this->rect->x) {
            $aImg->Line($x0, $y0, $this->rect->x, $this->rect->ye - $c);
            $aImg->Line($x0_right, $y0, $this->rect->xe, $this->rect->ye - $c);
            $dist += $this->linespacing;
            $x0 = $middle - $dist * $factor;
            $x1 = $middle - $dist;
            $x0_right = $middle + $dist * $factor;
            $c = $apa - $d * $apa / $dist;
        }

        // Horizontal lines
        // They need some serious consideration since they are a function
        // of perspective depth (alpha) and density (linespacing)
        $x0 = $this->rect->x;
        $x1 = $this->rect->xe;
        $y = $this->rect->ye;

        // The first line is drawn directly. Makes the loop below slightly
        // more readable.
        $aImg->Line($x0, $y, $x1, $y);
        $hls = $this->linespacing;

        // A correction factor for vertical "brick" line spacing to account for
        // a) the difference in number of pixels hor vs vert
        // b) visual apperance to make the first layer of "bricks" look more
        // square.
        $vls = $this->linespacing * 0.6;

        $ds = $hls * ($apa - $vls) / $apa;
        // Get the slope for the "perspective line" going from bottom right
        // corner to top left corner of the "first" brick.

        // Uncomment the following lines if you want to get a visual understanding
        // of what this helpline does. BTW this mimics the way you would get the
        // perspective right when drawing on paper.
        /*
        $x0 = $middle;
        $y0 = $this->rect->ye;
        $len=floor(($this->rect->ye-$this->rect->y)/$vls);
        $x1 = $middle+round($len*$ds);
        $y1 = $this->rect->ye-$len*$vls;
        $aImg->PushColor("red");
        $aImg->Line($x0,$y0,$x1,$y1);
        $aImg->PopColor();
         */

        $y -= $vls;
        $k = ($this->rect->ye - ($this->rect->ye - $vls)) / ($middle - ($middle - $ds));
        $dist = $hls;
        while ($y > $this->rect->y) {
            $aImg->Line($this->rect->x, $y, $this->rect->xe, $y);
            $adj = $k * $dist / (1 + $dist * $k / $apa);
            if ($adj < 2) {
                $adj = 1;
            }

            $y = $this->rect->ye - round($adj);
            $dist += $hls;
        }
    }
}
