<?php

namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS LinearTicks
// Description: Draw linear ticks on axis
//===================================================
class LinearTicks extends Ticks
{
    public $minor_step    = 1;
    public $major_step    = 2;
    public $xlabel_offset = 0;
    public $xtick_offset  = 0;
    private $label_offset = 0; // What offset should the displayed label have
    // i.e should we display 0,1,2 or 1,2,3,4 or 2,3,4 etc
    private $text_label_start    = 0;
    private $iManualTickPos      = null;
    private $iManualMinTickPos   = null;
    private $iManualTickLabels   = null;
    private $iAdjustForDST       = false; // If a date falls within the DST period add one hour to the diaplyed time

    public function __construct()
    {
        $this->precision = -1;
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
            //(" Minor or major step size is 0. Check that you haven't got an accidental SetTextTicks(0) in your code. If this is not the case you might have stumbled upon a bug in JpGraph. Please report this and if possible include the data that caused the problem.");
        }

        $this->major_step = $aMajStep;
        $this->minor_step = $aMinStep;
        $this->is_set     = true;
    }

    public function SetMajTickPositions($aMajPos, $aLabels = null)
    {
        $this->SetTickPositions($aMajPos, null, $aLabels);
    }

    public function SetTickPositions($aMajPos, $aMinPos = null, $aLabels = null)
    {
        if (!is_array($aMajPos) || ($aMinPos !== null && !is_array($aMinPos))) {
            Util\JpGraphError::RaiseL(25065); //('Tick positions must be specifued as an array()');
            return;
        }
        $n = count($aMajPos);
        if (is_array($aLabels) && (count($aLabels) != $n)) {
            Util\JpGraphError::RaiseL(25066); //('When manually specifying tick positions and labels the number of labels must be the same as the number of specified ticks.');
        }
        $this->iManualTickPos    = $aMajPos;
        $this->iManualMinTickPos = $aMinPos;
        $this->iManualTickLabels = $aLabels;
    }

    public function HaveManualLabels()
    {
        return count($this->iManualTickLabels) > 0;
    }

    // Specify all the tick positions manually and possible also the exact labels
    public function _doManualTickPos($aScale)
    {
        $n     = count($this->iManualTickPos);
        $m     = count($this->iManualMinTickPos);
        $doLbl = count($this->iManualTickLabels) > 0;

        $this->maj_ticks_pos      = array();
        $this->maj_ticklabels_pos = array();
        $this->ticks_pos          = array();

        // Now loop through the supplied positions and translate them to screen coordinates
        // and store them in the maj_label_positions
        $minScale = $aScale->scale[0];
        $maxScale = $aScale->scale[1];
        $j        = 0;
        for ($i = 0; $i < $n; ++$i) {
            // First make sure that the first tick is not lower than the lower scale value
            if (!isset($this->iManualTickPos[$i]) || $this->iManualTickPos[$i] < $minScale || $this->iManualTickPos[$i] > $maxScale) {
                continue;
            }

            $this->maj_ticks_pos[$j]      = $aScale->Translate($this->iManualTickPos[$i]);
            $this->maj_ticklabels_pos[$j] = $this->maj_ticks_pos[$j];

            // Set the minor tick marks the same as major if not specified
            if ($m <= 0) {
                $this->ticks_pos[$j] = $this->maj_ticks_pos[$j];
            }
            if ($doLbl) {
                $this->maj_ticks_label[$j] = $this->iManualTickLabels[$i];
            } else {
                $this->maj_ticks_label[$j] = $this->_doLabelFormat($this->iManualTickPos[$i], $i, $n);
            }
            ++$j;
        }

        // Some sanity check
        if (count($this->maj_ticks_pos) < 2) {
            Util\JpGraphError::RaiseL(25067); //('Your manually specified scale and ticks is not correct. The scale seems to be too small to hold any of the specified tickl marks.');
        }

        // Setup the minor tick marks
        $j = 0;
        for ($i = 0; $i < $m; ++$i) {
            if (empty($this->iManualMinTickPos[$i]) || $this->iManualMinTickPos[$i] < $minScale || $this->iManualMinTickPos[$i] > $maxScale) {
                continue;
            }
            $this->ticks_pos[$j] = $aScale->Translate($this->iManualMinTickPos[$i]);
            ++$j;
        }
    }

    public function _doAutoTickPos($aScale)
    {
        $maj_step_abs = $aScale->scale_factor * $this->major_step;
        $min_step_abs = $aScale->scale_factor * $this->minor_step;

        if ($min_step_abs == 0 || $maj_step_abs == 0) {
            Util\JpGraphError::RaiseL(25068); //("A plot has an illegal scale. This could for example be that you are trying to use text autoscaling to draw a line plot with only one point or that the plot area is too small. It could also be that no input data value is numeric (perhaps only '-' or 'x')");
        }
        // We need to make this an int since comparing it below
        // with the result from round() can give wrong result, such that
        // (40 < 40) == TRUE !!!
        $limit = (int) $aScale->scale_abs[1];

        if ($aScale->textscale) {
            // This can only be true for a X-scale (horizontal)
            // Define ticks for a text scale. This is slightly different from a
            // normal linear type of scale since the position might be adjusted
            // and the labels start at on
            $label       = (float) $aScale->GetMinVal() + $this->text_label_start + $this->label_offset;
            $start_abs   = $aScale->scale_factor * $this->text_label_start;
            $nbrmajticks = round(($aScale->GetMaxVal() - $aScale->GetMinVal() - $this->text_label_start) / $this->major_step) + 1;

            $x = $aScale->scale_abs[0] + $start_abs + $this->xlabel_offset * $min_step_abs;
            for ($i = 0; $label <= $aScale->GetMaxVal() + $this->label_offset; ++$i) {
                // Apply format to label
                $this->maj_ticks_label[$i] = $this->_doLabelFormat($label, $i, $nbrmajticks);
                $label += $this->major_step;

                // The x-position of the tick marks can be different from the labels.
                // Note that we record the tick position (not the label) so that the grid
                // happen upon tick marks and not labels.
                $xtick                        = $aScale->scale_abs[0] + $start_abs + $this->xtick_offset * $min_step_abs + $i * $maj_step_abs;
                $this->maj_ticks_pos[$i]      = $xtick;
                $this->maj_ticklabels_pos[$i] = round($x);
                $x += $maj_step_abs;
            }
        } else {
            $label   = $aScale->GetMinVal();
            $abs_pos = $aScale->scale_abs[0];
            $j       = 0;
            $i       = 0;
            $step    = round($maj_step_abs / $min_step_abs);
            if ($aScale->type == "x") {
                // For a normal linear type of scale the major ticks will always be multiples
                // of the minor ticks. In order to avoid any rounding issues the major ticks are
                // defined as every "step" minor ticks and not calculated separately
                $nbrmajticks = round(($aScale->GetMaxVal() - $aScale->GetMinVal() - $this->text_label_start) / $this->major_step) + 1;
                while (round($abs_pos) <= $limit) {
                    $this->ticks_pos[]   = round($abs_pos);
                    $this->ticks_label[] = $label;
                    if ($step == 0 || $i % $step == 0 && $j < $nbrmajticks) {
                        $this->maj_ticks_pos[$j]      = round($abs_pos);
                        $this->maj_ticklabels_pos[$j] = round($abs_pos);
                        $this->maj_ticks_label[$j]    = $this->_doLabelFormat($label, $j, $nbrmajticks);
                        ++$j;
                    }
                    ++$i;
                    $abs_pos += $min_step_abs;
                    $label += $this->minor_step;
                }
            } elseif ($aScale->type == "y") {
                //@todo  s=2:20,12  s=1:50,6  $this->major_step:$nbr
                // abs_point,limit s=1:270,80 s=2:540,160
                // $this->major_step = 50;
                $nbrmajticks = round(($aScale->GetMaxVal() - $aScale->GetMinVal()) / $this->major_step) + 1;
                //                $step = 5;
                while (round($abs_pos) >= $limit) {
                    $this->ticks_pos[$i]   = round($abs_pos);
                    $this->ticks_label[$i] = $label;
                    if ($step == 0 || $i % $step == 0 && $j < $nbrmajticks) {
                        $this->maj_ticks_pos[$j]      = round($abs_pos);
                        $this->maj_ticklabels_pos[$j] = round($abs_pos);
                        $this->maj_ticks_label[$j]    = $this->_doLabelFormat($label, $j, $nbrmajticks);
                        ++$j;
                    }
                    ++$i;
                    $abs_pos += $min_step_abs;
                    $label += $this->minor_step;
                }
            }
        }
    }

    public function AdjustForDST($aFlg = true)
    {
        $this->iAdjustForDST = $aFlg;
    }

    public function _doLabelFormat($aVal, $aIdx, $aNbrTicks)
    {

        // If precision hasn't been specified set it to a sensible value
        if ($this->precision == -1) {
            $t = log10($this->minor_step);
            if ($t > 0) {
                $precision = 0;
            } else {
                $precision = -floor($t);
            }
        } else {
            $precision = $this->precision;
        }

        if ($this->label_formfunc != '') {
            $f = $this->label_formfunc;
            if ($this->label_formatstr == '') {
                $l = call_user_func($f, $aVal);
            } else {
                $l = sprintf($this->label_formatstr, call_user_func($f, $aVal));
            }
        } elseif ($this->label_formatstr != '' || $this->label_dateformatstr != '') {
            if ($this->label_usedateformat) {
                // Adjust the value to take daylight savings into account
                if (date("I", $aVal) == 1 && $this->iAdjustForDST) {
                    // DST
                    $aVal += 3600;
                }

                $l = date($this->label_formatstr, $aVal);
                if ($this->label_formatstr == 'W') {
                    // If we use week formatting then add a single 'w' in front of the
                    // week number to differentiate it from dates
                    $l = 'w' . $l;
                }
            } else {
                if ($this->label_dateformatstr !== '') {
                    // Adjust the value to take daylight savings into account
                    if (date("I", $aVal) == 1 && $this->iAdjustForDST) {
                        // DST
                        $aVal += 3600;
                    }

                    $l = date($this->label_dateformatstr, $aVal);
                    if ($this->label_formatstr == 'W') {
                        // If we use week formatting then add a single 'w' in front of the
                        // week number to differentiate it from dates
                        $l = 'w' . $l;
                    }
                } else {
                    $l = sprintf($this->label_formatstr, $aVal);
                }
            }
        } else {
            //FIX: if negative precision  is returned "0f" , instead of formatted values
            $format = $precision > 0?'%01.' . $precision . 'f':'%01.0f';
            $l      =  sprintf($format, round($aVal, $precision));
        }

        if (($this->supress_zerolabel && $l == 0) || ($this->supress_first && $aIdx == 0) || ($this->supress_last && $aIdx == $aNbrTicks - 1)) {
            $l = '';
        }
        return $l;
    }

    // Stroke ticks on either X or Y axis
    public function _StrokeTicks($aImg, $aScale, $aPos)
    {
        $hor = $aScale->type == 'x';
        $aImg->SetLineWeight($this->weight);

        // We need to make this an int since comparing it below
        // with the result from round() can give wrong result, such that
        // (40 < 40) == TRUE !!!
        $limit = (int) $aScale->scale_abs[1];

        // A text scale doesn't have any minor ticks
        if (!$aScale->textscale) {
            // Stroke minor ticks
            $yu = $aPos - $this->direction * $this->GetMinTickAbsSize();
            $xr = $aPos + $this->direction * $this->GetMinTickAbsSize();
            $n  = count($this->ticks_pos);
            for ($i = 0; $i < $n; ++$i) {
                if (!$this->supress_tickmarks && !$this->supress_minor_tickmarks) {
                    if ($this->mincolor != '') {
                        $aImg->PushColor($this->mincolor);
                    }
                    if ($hor) {
                        //if( $this->ticks_pos[$i] <= $limit )
                        $aImg->Line($this->ticks_pos[$i], $aPos, $this->ticks_pos[$i], $yu);
                    } else {
                        //if( $this->ticks_pos[$i] >= $limit )
                        $aImg->Line($aPos, $this->ticks_pos[$i], $xr, $this->ticks_pos[$i]);
                    }
                    if ($this->mincolor != '') {
                        $aImg->PopColor();
                    }
                }
            }
        }

        // Stroke major ticks
        $yu          = $aPos - $this->direction * $this->GetMajTickAbsSize();
        $xr          = $aPos + $this->direction * $this->GetMajTickAbsSize();
        $nbrmajticks = round(($aScale->GetMaxVal() - $aScale->GetMinVal() - $this->text_label_start) / $this->major_step) + 1;
        $n           = count($this->maj_ticks_pos);
        for ($i = 0; $i < $n; ++$i) {
            if (!($this->xtick_offset > 0 && $i == $nbrmajticks - 1) && !$this->supress_tickmarks) {
                if ($this->majcolor != '') {
                    $aImg->PushColor($this->majcolor);
                }
                if ($hor) {
                    //if( $this->maj_ticks_pos[$i] <= $limit )
                    $aImg->Line($this->maj_ticks_pos[$i], $aPos, $this->maj_ticks_pos[$i], $yu);
                } else {
                    //if( $this->maj_ticks_pos[$i] >= $limit )
                    $aImg->Line($aPos, $this->maj_ticks_pos[$i], $xr, $this->maj_ticks_pos[$i]);
                }
                if ($this->majcolor != '') {
                    $aImg->PopColor();
                }
            }
        }
    }

    // Draw linear ticks
    public function Stroke($aImg, $aScale, $aPos)
    {
        if ($this->iManualTickPos != null) {
            $this->_doManualTickPos($aScale);
        } else {
            $this->_doAutoTickPos($aScale);
        }
        $this->_StrokeTicks($aImg, $aScale, $aPos, $aScale->type == 'x');
    }

    //---------------
    // PRIVATE METHODS
    // Spoecify the offset of the displayed tick mark with the tick "space"
    // Legal values for $o is [0,1] used to adjust where the tick marks and label
    // should be positioned within the major tick-size
    // $lo specifies the label offset and $to specifies the tick offset
    // this comes in handy for example in bar graphs where we wont no offset for the
    // tick but have the labels displayed halfway under the bars.
    public function SetXLabelOffset($aLabelOff, $aTickOff = -1)
    {
        $this->xlabel_offset = $aLabelOff;
        if ($aTickOff == -1) {
            // Same as label offset
            $this->xtick_offset = $aLabelOff;
        } else {
            $this->xtick_offset = $aTickOff;
        }
        if ($aLabelOff > 0) {
            $this->SupressLast(); // The last tick wont fit
        }
    }

    // Which tick label should we start with?
    public function SetTextLabelStart($aTextLabelOff)
    {
        $this->text_label_start = $aTextLabelOff;
    }
} // Class
