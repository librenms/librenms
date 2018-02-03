<?php
namespace Amenadiel\JpGraph\Graph;

//===================================================
// CLASS RadarLogTicks
// Description: Logarithmic ticks
//===================================================
class RadarLogTicks extends Ticks
{
    public function __construct()
    {
        // Empty
    }

    public function Stroke($aImg, &$grid, $aPos, $aAxisAngle, $aScale, &$aMajPos, &$aMajLabel)
    {
        $start     = $aScale->GetMinVal();
        $limit     = $aScale->GetMaxVal();
        $nextMajor = 10 * $start;
        $step      = $nextMajor / 10.0;
        $count     = 1;

        $ticklen_maj = 5;
        $dx_maj      = round(sin($aAxisAngle) * $ticklen_maj);
        $dy_maj      = round(cos($aAxisAngle) * $ticklen_maj);
        $ticklen_min = 3;
        $dx_min      = round(sin($aAxisAngle) * $ticklen_min);
        $dy_min      = round(cos($aAxisAngle) * $ticklen_min);

        $aMajPos   = array();
        $aMajLabel = array();

        if ($this->supress_first) {
            $aMajLabel[] = '';
        } else {
            $aMajLabel[] = $start;
        }

        $yr        = $aScale->RelTranslate($start);
        $xt        = round($yr * cos($aAxisAngle)) + $aScale->scale_abs[0];
        $yt        = $aPos - round($yr * sin($aAxisAngle));
        $aMajPos[] = $xt + 2 * $dx_maj;
        $aMajPos[] = $yt - $aImg->GetFontheight() / 2;
        $grid[]    = $xt;
        $grid[]    = $yt;

        $aImg->SetLineWeight($this->weight);

        for ($y = $start; $y <= $limit; $y += $step, ++$count) {
            $yr = $aScale->RelTranslate($y);
            $xt = round($yr * cos($aAxisAngle)) + $aScale->scale_abs[0];
            $yt = $aPos - round($yr * sin($aAxisAngle));
            if ($count % 10 == 0) {
                $grid[]    = $xt;
                $grid[]    = $yt;
                $aMajPos[] = $xt + 2 * $dx_maj;
                $aMajPos[] = $yt - $aImg->GetFontheight() / 2;
                if (!$this->supress_tickmarks) {
                    if ($this->majcolor != '') {
                        $aImg->PushColor($this->majcolor);
                    }
                    $aImg->Line($xt + $dx_maj, $yt + $dy_maj, $xt - $dx_maj, $yt - $dy_maj);
                    if ($this->majcolor != '') {
                        $aImg->PopColor();
                    }
                }
                if ($this->label_formfunc != '') {
                    $f = $this->label_formfunc;
                    $l = call_user_func($f, $nextMajor);
                } else {
                    $l = $nextMajor;
                }

                $aMajLabel[] = $l;
                $nextMajor *= 10;
                $step *= 10;
                $count = 1;
            } else {
                if (!$this->supress_minor_tickmarks) {
                    if ($this->mincolor != '') {
                        $aImg->PushColor($this->mincolor);
                    }
                    $aImg->Line($xt + $dx_min, $yt + $dy_min, $xt - $dx_min, $yt - $dy_min);
                    if ($this->mincolor != '') {
                        $aImg->PopColor();
                    }
                }
            }
        }
    }
}
