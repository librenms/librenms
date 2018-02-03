<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS RadarLinear
// Description: Linear ticks
//===================================================
class RadarLinearTicks extends Ticks
{
    private $minor_step    = 1;
    private $major_step    = 2;
    private $xlabel_offset = 0;
    private $xtick_offset  = 0;

    public function __construct()
    {
        // Empty
    }

    // Return major step size in world coordinates
    public function GetMajor()
    {
        return $this->major_step;
    }

    // Return minor step size in world coordinates
    public function GetMinor()
    {
        return $this->minor_step;
    }

    // Set Minor and Major ticks (in world coordinates)
    public function Set($aMajStep, $aMinStep = false)
    {
        if ($aMinStep == false) {
            $aMinStep = $aMajStep;
        }

        if ($aMajStep <= 0 || $aMinStep <= 0) {
            Util\JpGraphError::RaiseL(25064);
            //Util\JpGraphError::Raise(" Minor or major step size is 0. Check that you haven't got an accidental SetTextTicks(0) in your code. If this is not the case you might have stumbled upon a bug in JpGraph. Please report this and if possible include the data that caused the problem.");
        }

        $this->major_step = $aMajStep;
        $this->minor_step = $aMinStep;
        $this->is_set     = true;
    }

    public function Stroke($aImg, &$grid, $aPos, $aAxisAngle, $aScale, &$aMajPos, &$aMajLabel)
    {
        // Prepare to draw linear ticks
        $maj_step_abs = abs($aScale->scale_factor * $this->major_step);
        $min_step_abs = abs($aScale->scale_factor * $this->minor_step);
        $nbrmaj       = round($aScale->world_abs_size / $maj_step_abs);
        $nbrmin       = round($aScale->world_abs_size / $min_step_abs);
        $skip         = round($nbrmin / $nbrmaj); // Don't draw minor on top of major

        // Draw major ticks
        $ticklen2 = $this->major_abs_size;
        $dx       = round(sin($aAxisAngle) * $ticklen2);
        $dy       = round(cos($aAxisAngle) * $ticklen2);
        $label    = $aScale->scale[0] + $this->major_step;

        $aImg->SetLineWeight($this->weight);

        $aMajPos   = array();
        $aMajLabel = array();

        for ($i = 1; $i <= $nbrmaj; ++$i) {
            $xt = round($i * $maj_step_abs * cos($aAxisAngle)) + $aScale->scale_abs[0];
            $yt = $aPos - round($i * $maj_step_abs * sin($aAxisAngle));

            if ($this->label_formfunc != '') {
                $f = $this->label_formfunc;
                $l = call_user_func($f, $label);
            } else {
                $l = $label;
            }

            $aMajLabel[] = $l;
            $label += $this->major_step;
            $grid[]                    = $xt;
            $grid[]                    = $yt;
            $aMajPos[($i - 1) * 2]     = $xt + 2 * $dx;
            $aMajPos[($i - 1) * 2 + 1] = $yt - $aImg->GetFontheight() / 2;
            if (!$this->supress_tickmarks) {
                if ($this->majcolor != '') {
                    $aImg->PushColor($this->majcolor);
                }
                $aImg->Line($xt + $dx, $yt + $dy, $xt - $dx, $yt - $dy);
                if ($this->majcolor != '') {
                    $aImg->PopColor();
                }
            }
        }

        // Draw minor ticks
        $ticklen2 = $this->minor_abs_size;
        $dx       = round(sin($aAxisAngle) * $ticklen2);
        $dy       = round(cos($aAxisAngle) * $ticklen2);
        if (!$this->supress_tickmarks && !$this->supress_minor_tickmarks) {
            if ($this->mincolor != '') {
                $aImg->PushColor($this->mincolor);
            }
            for ($i = 1; $i <= $nbrmin; ++$i) {
                if (($i % $skip) == 0) {
                    continue;
                }
                $xt = round($i * $min_step_abs * cos($aAxisAngle)) + $aScale->scale_abs[0];
                $yt = $aPos - round($i * $min_step_abs * sin($aAxisAngle));
                $aImg->Line($xt + $dx, $yt + $dy, $xt - $dx, $yt - $dy);
            }
            if ($this->mincolor != '') {
                $aImg->PopColor();
            }
        }
    }
}
