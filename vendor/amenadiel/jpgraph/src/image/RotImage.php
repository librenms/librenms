<?php
namespace Amenadiel\JpGraph\Image;

//===================================================
// CLASS RotImage
// Description: Exactly as Image but draws the image at
// a specified angle around a specified rotation point.
//===================================================
class RotImage extends Image
{
    public $a = 0;
    public $dx = 0, $dy = 0, $transx = 0, $transy = 0;
    private $m = array();

    public function __construct($aWidth, $aHeight, $a = 0, $aFormat = DEFAULT_GFORMAT, $aSetAutoMargin = true)
    {
        parent::__construct($aWidth, $aHeight, $aFormat, $aSetAutoMargin);
        $this->dx = $this->left_margin + $this->plotwidth / 2;
        $this->dy = $this->top_margin + $this->plotheight / 2;
        $this->SetAngle($a);
    }

    public function SetCenter($dx, $dy)
    {
        $old_dx = $this->dx;
        $old_dy = $this->dy;
        $this->dx = $dx;
        $this->dy = $dy;
        $this->SetAngle($this->a);
        return array($old_dx, $old_dy);
    }

    public function SetTranslation($dx, $dy)
    {
        $old = array($this->transx, $this->transy);
        $this->transx = $dx;
        $this->transy = $dy;
        return $old;
    }

    public function UpdateRotMatrice()
    {
        $a = $this->a;
        $a *= M_PI / 180;
        $sa = sin($a);
        $ca = cos($a);
        // Create the rotation matrix
        $this->m[0][0] = $ca;
        $this->m[0][1] = -$sa;
        $this->m[0][2] = $this->dx * (1 - $ca) + $sa * $this->dy;
        $this->m[1][0] = $sa;
        $this->m[1][1] = $ca;
        $this->m[1][2] = $this->dy * (1 - $ca) - $sa * $this->dx;
    }

    public function SetAngle($a)
    {
        $tmp = $this->a;
        $this->a = $a;
        $this->UpdateRotMatrice();
        return $tmp;
    }

    public function Circle($xc, $yc, $r)
    {
        list($xc, $yc) = $this->Rotate($xc, $yc);
        parent::Circle($xc, $yc, $r);
    }

    public function FilledCircle($xc, $yc, $r)
    {
        list($xc, $yc) = $this->Rotate($xc, $yc);
        parent::FilledCircle($xc, $yc, $r);
    }

    public function Arc($xc, $yc, $w, $h, $s, $e)
    {
        list($xc, $yc) = $this->Rotate($xc, $yc);
        $s += $this->a;
        $e += $this->a;
        parent::Arc($xc, $yc, $w, $h, $s, $e);
    }

    public function FilledArc($xc, $yc, $w, $h, $s, $e, $style = '')
    {
        list($xc, $yc) = $this->Rotate($xc, $yc);
        $s += $this->a;
        $e += $this->a;
        parent::FilledArc($xc, $yc, $w, $h, $s, $e);
    }

    public function SetMargin($lm, $rm, $tm, $bm)
    {
        parent::SetMargin($lm, $rm, $tm, $bm);
        $this->dx = $this->left_margin + $this->plotwidth / 2;
        $this->dy = $this->top_margin + $this->plotheight / 2;
        $this->UpdateRotMatrice();
    }

    public function Rotate($x, $y)
    {
        // Optimization. Ignore rotation if Angle==0 || Angle==360
        if ($this->a == 0 || $this->a == 360) {
            return array($x + $this->transx, $y + $this->transy);
        } else {
            $x1 = round($this->m[0][0] * $x + $this->m[0][1] * $y, 1) + $this->m[0][2] + $this->transx;
            $y1 = round($this->m[1][0] * $x + $this->m[1][1] * $y, 1) + $this->m[1][2] + $this->transy;
            return array($x1, $y1);
        }
    }

    public function CopyMerge($fromImg, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth = -1, $fromHeight = -1, $aMix = 100)
    {
        list($toX, $toY) = $this->Rotate($toX, $toY);
        parent::CopyMerge($fromImg, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth, $fromHeight, $aMix);

    }

    public function ArrRotate($pnts)
    {
        $n = count($pnts) - 1;
        for ($i = 0; $i < $n; $i += 2) {
            list($x, $y) = $this->Rotate($pnts[$i], $pnts[$i + 1]);
            $pnts[$i] = $x;
            $pnts[$i + 1] = $y;
        }
        return $pnts;
    }

    public function DashedLine($x1, $y1, $x2, $y2, $dash_length = 1, $dash_space = 4)
    {
        list($x1, $y1) = $this->Rotate($x1, $y1);
        list($x2, $y2) = $this->Rotate($x2, $y2);
        parent::DashedLine($x1, $y1, $x2, $y2, $dash_length, $dash_space);
    }

    public function Line($x1, $y1, $x2, $y2)
    {
        list($x1, $y1) = $this->Rotate($x1, $y1);
        list($x2, $y2) = $this->Rotate($x2, $y2);
        parent::Line($x1, $y1, $x2, $y2);
    }

    public function Rectangle($x1, $y1, $x2, $y2)
    {
        // Rectangle uses Line() so it will be rotated through that call
        parent::Rectangle($x1, $y1, $x2, $y2);
    }

    public function FilledRectangle($x1, $y1, $x2, $y2)
    {
        if ($y1 == $y2 || $x1 == $x2) {
            $this->Line($x1, $y1, $x2, $y2);
        } else {
            $this->FilledPolygon(array($x1, $y1, $x2, $y1, $x2, $y2, $x1, $y2));
        }

    }

    public function Polygon($pnts, $closed = false, $fast = false)
    {
        // Polygon uses Line() so it will be rotated through that call unless
        // fast drawing routines are used in which case a rotate is needed
        if ($fast) {
            parent::Polygon($this->ArrRotate($pnts));
        } else {
            parent::Polygon($pnts, $closed, $fast);
        }
    }

    public function FilledPolygon($pnts)
    {
        parent::FilledPolygon($this->ArrRotate($pnts));
    }

    public function Point($x, $y)
    {
        list($xp, $yp) = $this->Rotate($x, $y);
        parent::Point($xp, $yp);
    }

    public function StrokeText($x, $y, $txt, $dir = 0, $paragraph_align = "left", $debug = false)
    {
        list($xp, $yp) = $this->Rotate($x, $y);
        return parent::StrokeText($xp, $yp, $txt, $dir, $paragraph_align, $debug);
    }
}
