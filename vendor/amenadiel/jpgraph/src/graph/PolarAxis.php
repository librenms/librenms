<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//--------------------------------------------------------------------------
// class PolarAxis
//--------------------------------------------------------------------------
class PolarAxis extends Axis
{
    private $angle_step               = 15;
    private $angle_color              = 'lightgray';
    private $angle_label_color        = 'black';
    private $angle_fontfam            = FF_FONT1;
    private $angle_fontstyle          = FS_NORMAL;
    private $angle_fontsize           = 10;
    private $angle_fontcolor          = 'navy';
    private $gridminor_color          = 'lightgray';
    private $gridmajor_color          = 'lightgray';
    private $show_minor_grid          = false;
    private $show_major_grid          = true;
    private $show_angle_mark          = true;
    private $show_angle_grid          = true;
    private $show_angle_label         = true;
    private $angle_tick_len           = 3;
    private $angle_tick_len2          = 3;
    private $angle_tick_color         = 'black';
    private $show_angle_tick          = true;
    private $radius_tick_color        = 'black';

    public function __construct($img, $aScale)
    {
        parent::__construct($img, $aScale);
    }

    public function ShowAngleDegreeMark($aFlg = true)
    {
        $this->show_angle_mark = $aFlg;
    }

    public function SetAngleStep($aStep)
    {
        $this->angle_step = $aStep;
    }

    public function HideTicks($aFlg = true, $aAngleFlg = true)
    {
        parent::HideTicks($aFlg, $aFlg);
        $this->show_angle_tick = !$aAngleFlg;
    }

    public function ShowAngleLabel($aFlg = true)
    {
        $this->show_angle_label = $aFlg;
    }

    public function ShowGrid($aMajor = true, $aMinor = false, $aAngle = true)
    {
        $this->show_minor_grid = $aMinor;
        $this->show_major_grid = $aMajor;
        $this->show_angle_grid = $aAngle;
    }

    public function SetAngleFont($aFontFam, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->angle_fontfam   = $aFontFam;
        $this->angle_fontstyle = $aFontStyle;
        $this->angle_fontsize  = $aFontSize;
    }

    public function SetColor($aColor, $aRadColor = '', $aAngleColor = '')
    {
        if ($aAngleColor == '') {
            $aAngleColor = $aColor;
        }

        parent::SetColor($aColor, $aRadColor);
        $this->angle_fontcolor = $aAngleColor;
    }

    public function SetGridColor($aMajorColor, $aMinorColor = '', $aAngleColor = '')
    {
        if ($aMinorColor == '') {
            $aMinorColor = $aMajorColor;
        }

        if ($aAngleColor == '') {
            $aAngleColor = $aMajorColor;
        }

        $this->gridminor_color = $aMinorColor;
        $this->gridmajor_color = $aMajorColor;
        $this->angle_color     = $aAngleColor;
    }

    public function SetTickColors($aRadColor, $aAngleColor = '')
    {
        $this->radius_tick_color = $aRadColor;
        $this->angle_tick_color  = $aAngleColor;
    }

    // Private methods
    public function StrokeGrid($pos)
    {
        $x = round($this->img->left_margin + $this->img->plotwidth / 2);
        $this->scale->ticks->Stroke($this->img, $this->scale, $pos);

        // Stroke the minor arcs
        $pmin = [];
        $p    = $this->scale->ticks->ticks_pos;
        $n    = count($p);
        $i    = 0;
        $this->img->SetColor($this->gridminor_color);
        while ($i < $n) {
            $r      = $p[$i] - $x + 1;
            $pmin[] = $r;
            if ($this->show_minor_grid) {
                $this->img->Circle($x, $pos, $r);
            }
            $i++;
        }

        $limit = max($this->img->plotwidth, $this->img->plotheight) * 1.4;
        while ($r < $limit) {
            $off = $r;
            $i   = 1;
            $r   = $off + round($p[$i] - $x + 1);
            while ($r < $limit && $i < $n) {
                $r      = $off + $p[$i] - $x;
                $pmin[] = $r;
                if ($this->show_minor_grid) {
                    $this->img->Circle($x, $pos, $r);
                }
                $i++;
            }
        }

        // Stroke the major arcs
        if ($this->show_major_grid) {
            // First determine how many minor step on
            // every major step. We have recorded the minor radius
            // in pmin and use these values. This is done in order
            // to avoid rounding errors if we were to recalculate the
            // different major radius.
            $pmaj = $this->scale->ticks->maj_ticks_pos;
            $p    = $this->scale->ticks->ticks_pos;
            if ($this->scale->name == 'lin') {
                $step = round(($pmaj[1] - $pmaj[0]) / ($p[1] - $p[0]));
            } else {
                $step = 9;
            }
            $n = round(count($pmin) / $step);
            $i = 0;
            $this->img->SetColor($this->gridmajor_color);
            $limit = max($this->img->plotwidth, $this->img->plotheight) * 1.4;
            $off   = $r;
            $i     = 0;
            $r     = $pmin[$i * $step];
            while ($r < $limit && $i < $n) {
                $r = $pmin[$i * $step];
                $this->img->Circle($x, $pos, $r);
                $i++;
            }
        }

        // Draw angles
        if ($this->show_angle_grid) {
            $this->img->SetColor($this->angle_color);
            $d            = max($this->img->plotheight, $this->img->plotwidth) * 1.4;
            $a            = 0;
            $p            = $this->scale->ticks->ticks_pos;
            $start_radius = $p[1] - $x;
            while ($a < 360) {
                if ($a == 90 || $a == 270) {
                    // Make sure there are no rounding problem with
                    // exactly vertical lines
                    $this->img->Line($x + $start_radius * cos($a / 180 * M_PI) + 1,
                        $pos - $start_radius * sin($a / 180 * M_PI),
                        $x + $start_radius * cos($a / 180 * M_PI) + 1,
                        $pos - $d * sin($a / 180 * M_PI));
                } else {
                    $this->img->Line($x + $start_radius * cos($a / 180 * M_PI) + 1,
                        $pos - $start_radius * sin($a / 180 * M_PI),
                        $x + $d * cos($a / 180 * M_PI),
                        $pos - $d * sin($a / 180 * M_PI));
                }
                $a += $this->angle_step;
            }
        }
    }

    public function StrokeAngleLabels($pos, $type)
    {
        if (!$this->show_angle_label) {
            return;
        }

        $x0 = round($this->img->left_margin + $this->img->plotwidth / 2) + 1;

        $d = max($this->img->plotwidth, $this->img->plotheight) * 1.42;
        $a = $this->angle_step;
        $t = new Text();
        $t->SetColor($this->angle_fontcolor);
        $t->SetFont($this->angle_fontfam, $this->angle_fontstyle, $this->angle_fontsize);
        $xright  = $this->img->width - $this->img->right_margin;
        $ytop    = $this->img->top_margin;
        $xleft   = $this->img->left_margin;
        $ybottom = $this->img->height - $this->img->bottom_margin;
        $ha      = 'left';
        $va      = 'center';
        $w       = $this->img->plotwidth / 2;
        $h       = $this->img->plotheight / 2;
        $xt      = $x0;
        $yt      = $pos;
        $margin  = 5;

        $tl  = $this->angle_tick_len; // Outer len
        $tl2 = $this->angle_tick_len2; // Interior len

        $this->img->SetColor($this->angle_tick_color);
        $rot90 = $this->img->a == 90;

        if ($type == POLAR_360) {

            // Corner angles of the four corners
            $ca1 = atan($h / $w) / M_PI * 180;
            $ca2 = 180 - $ca1;
            $ca3 = $ca1 + 180;
            $ca4 = 360 - $ca1;
            $end = 360;

            while ($a < $end) {
                $ca = cos($a / 180 * M_PI);
                $sa = sin($a / 180 * M_PI);
                $x  = $d * $ca;
                $y  = $d * $sa;
                $xt = 1000;
                $yt = 1000;
                if ($a <= $ca1 || $a >= $ca4) {
                    $yt = $pos - $w * $y / $x;
                    $xt = $xright + $margin;
                    if ($rot90) {
                        $ha = 'center';
                        $va = 'top';
                    } else {
                        $ha = 'left';
                        $va = 'center';
                    }
                    $x1 = $xright - $tl2;
                    $x2 = $xright + $tl;
                    $y1 = $y2 = $yt;
                } elseif ($a > $ca1 && $a < $ca2) {
                    $xt = $x0 + $h * $x / $y;
                    $yt = $ytop - $margin;
                    if ($rot90) {
                        $ha = 'left';
                        $va = 'center';
                    } else {
                        $ha = 'center';
                        $va = 'bottom';
                    }
                    $y1 = $ytop + $tl2;
                    $y2 = $ytop - $tl;
                    $x1 = $x2 = $xt;
                } elseif ($a >= $ca2 && $a <= $ca3) {
                    $yt = $pos + $w * $y / $x;
                    $xt = $xleft - $margin;
                    if ($rot90) {
                        $ha = 'center';
                        $va = 'bottom';
                    } else {
                        $ha = 'right';
                        $va = 'center';
                    }
                    $x1 = $xleft + $tl2;
                    $x2 = $xleft - $tl;
                    $y1 = $y2 = $yt;
                } else {
                    $xt = $x0 - $h * $x / $y;
                    $yt = $ybottom + $margin;
                    if ($rot90) {
                        $ha = 'right';
                        $va = 'center';
                    } else {
                        $ha = 'center';
                        $va = 'top';
                    }
                    $y1 = $ybottom - $tl2;
                    $y2 = $ybottom + $tl;
                    $x1 = $x2 = $xt;
                }
                if ($a != 0 && $a != 180) {
                    $t->Align($ha, $va);
                    if ($this->scale->clockwise) {
                        $t->Set(360 - $a);
                    } else {
                        $t->Set($a);
                    }
                    if ($this->show_angle_mark && $t->font_family > 4) {
                        $a .= SymChar::Get('degree');
                    }
                    $t->Stroke($this->img, $xt, $yt);
                    if ($this->show_angle_tick) {
                        $this->img->Line($x1, $y1, $x2, $y2);
                    }
                }
                $a += $this->angle_step;
            }
        } else {
            // POLAR_HALF
            $ca1 = atan($h / $w * 2) / M_PI * 180;
            $ca2 = 180 - $ca1;
            $end = 180;
            while ($a < $end) {
                $ca = cos($a / 180 * M_PI);
                $sa = sin($a / 180 * M_PI);
                $x  = $d * $ca;
                $y  = $d * $sa;
                if ($a <= $ca1) {
                    $yt = $pos - $w * $y / $x;
                    $xt = $xright + $margin;
                    if ($rot90) {
                        $ha = 'center';
                        $va = 'top';
                    } else {
                        $ha = 'left';
                        $va = 'center';
                    }
                    $x1 = $xright - $tl2;
                    $x2 = $xright + $tl;
                    $y1 = $y2 = $yt;
                } elseif ($a > $ca1 && $a < $ca2) {
                    $xt = $x0 + 2 * $h * $x / $y;
                    $yt = $ytop - $margin;
                    if ($rot90) {
                        $ha = 'left';
                        $va = 'center';
                    } else {
                        $ha = 'center';
                        $va = 'bottom';
                    }
                    $y1 = $ytop + $tl2;
                    $y2 = $ytop - $tl;
                    $x1 = $x2 = $xt;
                } elseif ($a >= $ca2) {
                    $yt = $pos + $w * $y / $x;
                    $xt = $xleft - $margin;
                    if ($rot90) {
                        $ha = 'center';
                        $va = 'bottom';
                    } else {
                        $ha = 'right';
                        $va = 'center';
                    }
                    $x1 = $xleft + $tl2;
                    $x2 = $xleft - $tl;
                    $y1 = $y2 = $yt;
                }
                $t->Align($ha, $va);
                if ($this->show_angle_mark && $t->font_family > 4) {
                    $a .= SymChar::Get('degree');
                }
                $t->Set($a);
                $t->Stroke($this->img, $xt, $yt);
                if ($this->show_angle_tick) {
                    $this->img->Line($x1, $y1, $x2, $y2);
                }
                $a += $this->angle_step;
            }
        }
    }

    public function Stroke($pos, $dummy = true)
    {
        $this->img->SetLineWeight($this->weight);
        $this->img->SetColor($this->color);
        $this->img->SetFont($this->font_family, $this->font_style, $this->font_size);
        if (!$this->hide_line) {
            $this->img->FilledRectangle($this->img->left_margin, $pos,
                $this->img->width - $this->img->right_margin,
                $pos + $this->weight - 1);
        }
        $y = $pos + $this->img->GetFontHeight() + $this->title_margin + $this->title->margin;
        if ($this->title_adjust == "high") {
            $this->title->SetPos($this->img->width - $this->img->right_margin, $y, "right", "top");
        } elseif ($this->title_adjust == "middle" || $this->title_adjust == "center") {
            $this->title->SetPos(($this->img->width - $this->img->left_margin - $this->img->right_margin) / 2 + $this->img->left_margin,
                $y, "center", "top");
        } elseif ($this->title_adjust == "low") {
            $this->title->SetPos($this->img->left_margin, $y, "left", "top");
        } else {
            Util\JpGraphError::RaiseL(17002, $this->title_adjust);
            //('Unknown alignment specified for X-axis title. ('.$this->title_adjust.')');
        }

        if (!$this->hide_labels) {
            $this->StrokeLabels($pos, false);
        }
        $this->img->SetColor($this->radius_tick_color);
        $this->scale->ticks->Stroke($this->img, $this->scale, $pos);

        //
        // Mirror the positions for the left side of the scale
        //
        $mid = 2 * ($this->img->left_margin + $this->img->plotwidth / 2);
        $n   = count($this->scale->ticks->ticks_pos);
        $i   = 0;
        while ($i < $n) {
            $this->scale->ticks->ticks_pos[$i] =
            $mid - $this->scale->ticks->ticks_pos[$i];
            ++$i;
        }

        $n = count($this->scale->ticks->maj_ticks_pos);
        $i = 0;
        while ($i < $n) {
            $this->scale->ticks->maj_ticks_pos[$i] =
            $mid - $this->scale->ticks->maj_ticks_pos[$i];
            ++$i;
        }

        $n = count($this->scale->ticks->maj_ticklabels_pos);
        $i = 1;
        while ($i < $n) {
            $this->scale->ticks->maj_ticklabels_pos[$i] =
            $mid - $this->scale->ticks->maj_ticklabels_pos[$i];
            ++$i;
        }

        // Draw the left side of the scale
        $n  = count($this->scale->ticks->ticks_pos);
        $yu = $pos - $this->scale->ticks->direction * $this->scale->ticks->GetMinTickAbsSize();

        // Minor ticks
        if (!$this->scale->ticks->supress_minor_tickmarks) {
            $i = 1;
            while ($i < $n / 2) {
                $x = round($this->scale->ticks->ticks_pos[$i]);
                $this->img->Line($x, $pos, $x, $yu);
                ++$i;
            }
        }

        $n  = count($this->scale->ticks->maj_ticks_pos);
        $yu = $pos - $this->scale->ticks->direction * $this->scale->ticks->GetMajTickAbsSize();

        // Major ticks
        if (!$this->scale->ticks->supress_tickmarks) {
            $i = 1;
            while ($i < $n / 2) {
                $x = round($this->scale->ticks->maj_ticks_pos[$i]);
                $this->img->Line($x, $pos, $x, $yu);
                ++$i;
            }
        }
        if (!$this->hide_labels) {
            $this->StrokeLabels($pos, false);
        }
        $this->title->Stroke($this->img);
    }
}
