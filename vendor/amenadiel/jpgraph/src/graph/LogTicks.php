<?php

namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS LogTicks
// Description:
//===================================================
class LogTicks extends Ticks
{
    private $label_logtype = LOGLABELS_MAGNITUDE;
    private $ticklabels_pos = array();
    //---------------
    // CONSTRUCTOR
    public function LogTicks()
    {
    }

    //---------------
    // PUBLIC METHODS
    public function IsSpecified()
    {
        return true;
    }

    public function SetLabelLogType($aType)
    {
        $this->label_logtype = $aType;
    }

    // For log scale it's meaningless to speak about a major step
    // We just return -1 to make the framework happy (specifically
    // StrokeLabels() )
    public function GetMajor()
    {
        return -1;
    }

    public function SetTextLabelStart($aStart)
    {
        Util\JpGraphError::RaiseL(11005);
        //('Specifying tick interval for a logarithmic scale is undefined. Remove any calls to SetTextLabelStart() or SetTextTickInterval() on the logarithmic scale.');
    }

    public function SetXLabelOffset($dummy)
    {
        // For log scales we dont care about XLabel offset
    }

    // Draw ticks on image "img" using scale "scale". The axis absolute
    // position in the image is specified in pos, i.e. for an x-axis
    // it specifies the absolute y-coord and for Y-ticks it specified the
    // absolute x-position.
    public function Stroke($img, $scale, $pos)
    {
        $start = $scale->GetMinVal();
        $limit = $scale->GetMaxVal();
        $nextMajor = 10 * $start;
        $step = $nextMajor / 10.0;

        $img->SetLineWeight($this->weight);

        if ($scale->type == "y") {
            // member direction specified if the ticks should be on
            // left or right side.
            $a = $pos + $this->direction * $this->GetMinTickAbsSize();
            $a2 = $pos + $this->direction * $this->GetMajTickAbsSize();

            $count = 1;
            $this->maj_ticks_pos[0] = $scale->Translate($start);
            $this->maj_ticklabels_pos[0] = $scale->Translate($start);
            if ($this->supress_first) {
                $this->maj_ticks_label[0] = "";
            } else {
                if ($this->label_formfunc != '') {
                    $f = $this->label_formfunc;
                    $this->maj_ticks_label[0] = call_user_func($f, $start);
                } elseif ($this->label_logtype == LOGLABELS_PLAIN) {
                    $this->maj_ticks_label[0] = $start;
                } else {
                    $this->maj_ticks_label[0] = '10^' . round(log10($start));
                }
            }
            $i = 1;
            for ($y = $start; $y <= $limit; $y += $step, ++$count) {
                $ys = $scale->Translate($y);
                $this->ticks_pos[] = $ys;
                $this->ticklabels_pos[] = $ys;
                if ($count % 10 == 0) {
                    if (!$this->supress_tickmarks) {
                        if ($this->majcolor != "") {
                            $img->PushColor($this->majcolor);
                            $img->Line($pos, $ys, $a2, $ys);
                            $img->PopColor();
                        } else {
                            $img->Line($pos, $ys, $a2, $ys);
                        }
                    }

                    $this->maj_ticks_pos[$i] = $ys;
                    $this->maj_ticklabels_pos[$i] = $ys;

                    if ($this->label_formfunc != '') {
                        $f = $this->label_formfunc;
                        $this->maj_ticks_label[$i] = call_user_func($f, $nextMajor);
                    } elseif ($this->label_logtype == 0) {
                        $this->maj_ticks_label[$i] = $nextMajor;
                    } else {
                        $this->maj_ticks_label[$i] = '10^' . round(log10($nextMajor));
                    }
                    ++$i;
                    $nextMajor *= 10;
                    $step *= 10;
                    $count = 1;
                } else {
                    if (!$this->supress_tickmarks && !$this->supress_minor_tickmarks) {
                        if ($this->mincolor != "") {
                            $img->PushColor($this->mincolor);
                        }
                        $img->Line($pos, $ys, $a, $ys);
                        if ($this->mincolor != "") {
                            $img->PopColor();
                        }
                    }
                }
            }
        } else {
            $a = $pos - $this->direction * $this->GetMinTickAbsSize();
            $a2 = $pos - $this->direction * $this->GetMajTickAbsSize();
            $count = 1;
            $this->maj_ticks_pos[0] = $scale->Translate($start);
            $this->maj_ticklabels_pos[0] = $scale->Translate($start);
            if ($this->supress_first) {
                $this->maj_ticks_label[0] = "";
            } else {
                if ($this->label_formfunc != '') {
                    $f = $this->label_formfunc;
                    $this->maj_ticks_label[0] = call_user_func($f, $start);
                } elseif ($this->label_logtype == 0) {
                    $this->maj_ticks_label[0] = $start;
                } else {
                    $this->maj_ticks_label[0] = '10^' . round(log10($start));
                }
            }
            $i = 1;
            for ($x = $start; $x <= $limit; $x += $step, ++$count) {
                $xs = $scale->Translate($x);
                $this->ticks_pos[] = $xs;
                $this->ticklabels_pos[] = $xs;
                if ($count % 10 == 0) {
                    if (!$this->supress_tickmarks) {
                        $img->Line($xs, $pos, $xs, $a2);
                    }
                    $this->maj_ticks_pos[$i] = $xs;
                    $this->maj_ticklabels_pos[$i] = $xs;

                    if ($this->label_formfunc != '') {
                        $f = $this->label_formfunc;
                        $this->maj_ticks_label[$i] = call_user_func($f, $nextMajor);
                    } elseif ($this->label_logtype == 0) {
                        $this->maj_ticks_label[$i] = $nextMajor;
                    } else {
                        $this->maj_ticks_label[$i] = '10^' . round(log10($nextMajor));
                    }
                    ++$i;
                    $nextMajor *= 10;
                    $step *= 10;
                    $count = 1;
                } else {
                    if (!$this->supress_tickmarks && !$this->supress_minor_tickmarks) {
                        $img->Line($xs, $pos, $xs, $a);
                    }
                }
            }
        }
        return true;
    }
} // Class
/* EOF */
