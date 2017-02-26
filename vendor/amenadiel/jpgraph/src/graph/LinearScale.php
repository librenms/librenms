<?php

namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS LinearScale
// Description: Handle linear scaling between screen and world
//===================================================
class LinearScale
{
    public $textscale = false; // Just a flag to let the Plot class find out if
    // we are a textscale or not. This is a cludge since
    // this information is available in Graph::axtype but
    // we don't have access to the graph object in the Plots
    // stroke method. So we let graph store the status here
    // when the linear scale is created. A real cludge...
    public $type; // is this x or y scale ?
    public $ticks = null; // Store ticks
    public $text_scale_off = 0;
    public $scale_abs = array(0, 0);
    public $scale_factor; // Scale factor between world and screen
    public $off; // Offset between image edge and plot area
    public $scale = array(0, 0);
    public $name = 'lin';
    public $auto_ticks = false; // When using manual scale should the ticks be automatically set?
    public $world_abs_size; // Plot area size in pixels (Needed public in jpgraph_radar.php)
    public $intscale = false; // Restrict autoscale to integers
    protected $autoscale_min = false; // Forced minimum value, auto determine max
    protected $autoscale_max = false; // Forced maximum value, auto determine min
    private $gracetop = 0, $gracebottom = 0;

    private $_world_size; // Plot area size in world coordinates

    public function __construct($aMin = 0, $aMax = 0, $aType = 'y')
    {
        assert($aType == 'x' || $aType == 'y');
        assert($aMin <= $aMax);

        $this->type = $aType;
        $this->scale = array($aMin, $aMax);
        $this->world_size = $aMax - $aMin;
        $this->ticks = new LinearTicks();
    }

    // Check if scale is set or if we should autoscale
    // We should do this is either scale or ticks has not been set
    public function IsSpecified()
    {
        if ($this->GetMinVal() == $this->GetMaxVal()) {
            // Scale not set
            return false;
        }
        return true;
    }

    // Set the minimum data value when the autoscaling is used.
    // Usefull if you want a fix minimum (like 0) but have an
    // automatic maximum
    public function SetAutoMin($aMin)
    {
        $this->autoscale_min = $aMin;
    }

    // Set the minimum data value when the autoscaling is used.
    // Usefull if you want a fix minimum (like 0) but have an
    // automatic maximum
    public function SetAutoMax($aMax)
    {
        $this->autoscale_max = $aMax;
    }

    // If the user manually specifies a scale should the ticks
    // still be set automatically?
    public function SetAutoTicks($aFlag = true)
    {
        $this->auto_ticks = $aFlag;
    }

    // Specify scale "grace" value (top and bottom)
    public function SetGrace($aGraceTop, $aGraceBottom = 0)
    {
        if ($aGraceTop < 0 || $aGraceBottom < 0) {
            Util\JpGraphError::RaiseL(25069); //(" Grace must be larger then 0");
        }
        $this->gracetop = $aGraceTop;
        $this->gracebottom = $aGraceBottom;
    }

    // Get the minimum value in the scale
    public function GetMinVal()
    {
        return $this->scale[0];
    }

    // get maximum value for scale
    public function GetMaxVal()
    {
        return $this->scale[1];
    }

    // Specify a new min/max value for sclae
    public function Update($aImg, $aMin, $aMax)
    {
        $this->scale = array($aMin, $aMax);
        $this->world_size = $aMax - $aMin;
        $this->InitConstants($aImg);
    }

    // Translate between world and screen
    public function Translate($aCoord)
    {
        if (!is_numeric($aCoord)) {
            if ($aCoord != '' && $aCoord != '-' && $aCoord != 'x') {
                Util\JpGraphError::RaiseL(25070); //('Your data contains non-numeric values.');
            }
            return 0;
        } else {
            return round($this->off + ($aCoord - $this->scale[0]) * $this->scale_factor);
        }
    }

    // Relative translate (don't include offset) usefull when we just want
    // to know the relative position (in pixels) on the axis
    public function RelTranslate($aCoord)
    {
        if (!is_numeric($aCoord)) {
            if ($aCoord != '' && $aCoord != '-' && $aCoord != 'x') {
                Util\JpGraphError::RaiseL(25070); //('Your data contains non-numeric values.');
            }
            return 0;
        } else {
            return ($aCoord - $this->scale[0]) * $this->scale_factor;
        }
    }

    // Restrict autoscaling to only use integers
    public function SetIntScale($aIntScale = true)
    {
        $this->intscale = $aIntScale;
    }

    // Calculate an integer autoscale
    public function IntAutoScale($img, $min, $max, $maxsteps, $majend = true)
    {
        // Make sure limits are integers
        $min = floor($min);
        $max = ceil($max);
        if (abs($min - $max) == 0) {
            --$min; ++$max;
        }
        $maxsteps = floor($maxsteps);

        $gracetop = round(($this->gracetop / 100.0) * abs($max - $min));
        $gracebottom = round(($this->gracebottom / 100.0) * abs($max - $min));
        if (is_numeric($this->autoscale_min)) {
            $min = ceil($this->autoscale_min);
            if ($min >= $max) {
                Util\JpGraphError::RaiseL(25071); //('You have specified a min value with SetAutoMin() which is larger than the maximum value used for the scale. This is not possible.');
            }
        }

        if (is_numeric($this->autoscale_max)) {
            $max = ceil($this->autoscale_max);
            if ($min >= $max) {
                Util\JpGraphError::RaiseL(25072); //('You have specified a max value with SetAutoMax() which is smaller than the miminum value used for the scale. This is not possible.');
            }
        }

        if (abs($min - $max) == 0) {
            ++$max;
            --$min;
        }

        $min -= $gracebottom;
        $max += $gracetop;

        // First get tickmarks as multiples of 1, 10, ...
        if ($majend) {
            list($num1steps, $adj1min, $adj1max, $maj1step) = $this->IntCalcTicks($maxsteps, $min, $max, 1);
        } else {
            $adj1min = $min;
            $adj1max = $max;
            list($num1steps, $maj1step) = $this->IntCalcTicksFreeze($maxsteps, $min, $max, 1);
        }

        if (abs($min - $max) > 2) {
            // Then get tick marks as 2:s 2, 20, ...
            if ($majend) {
                list($num2steps, $adj2min, $adj2max, $maj2step) = $this->IntCalcTicks($maxsteps, $min, $max, 5);
            } else {
                $adj2min = $min;
                $adj2max = $max;
                list($num2steps, $maj2step) = $this->IntCalcTicksFreeze($maxsteps, $min, $max, 5);
            }
        } else {
            $num2steps = 10000; // Dummy high value so we don't choose this
        }

        if (abs($min - $max) > 5) {
            // Then get tickmarks as 5:s 5, 50, 500, ...
            if ($majend) {
                list($num5steps, $adj5min, $adj5max, $maj5step) = $this->IntCalcTicks($maxsteps, $min, $max, 2);
            } else {
                $adj5min = $min;
                $adj5max = $max;
                list($num5steps, $maj5step) = $this->IntCalcTicksFreeze($maxsteps, $min, $max, 2);
            }
        } else {
            $num5steps = 10000; // Dummy high value so we don't choose this
        }

        // Check to see whichof 1:s, 2:s or 5:s fit better with
        // the requested number of major ticks
        $match1 = abs($num1steps - $maxsteps);
        $match2 = abs($num2steps - $maxsteps);
        if (!empty($maj5step) && $maj5step > 1) {
            $match5 = abs($num5steps - $maxsteps);
        } else {
            $match5 = 10000; // Dummy high value
        }

        // Compare these three values and see which is the closest match
        // We use a 0.6 weight to gravitate towards multiple of 5:s
        if ($match1 < $match2) {
            if ($match1 < $match5) {
                $r = 1;
            } else {
                $r = 3;
            }

        } else {
            if ($match2 < $match5) {
                $r = 2;
            } else {
                $r = 3;
            }

        }
        // Minsteps are always the same as maxsteps for integer scale
        switch ($r) {
            case 1:
                $this->ticks->Set($maj1step, $maj1step);
                $this->Update($img, $adj1min, $adj1max);
                break;
            case 2:
                $this->ticks->Set($maj2step, $maj2step);
                $this->Update($img, $adj2min, $adj2max);
                break;
            case 3:
                $this->ticks->Set($maj5step, $maj5step);
                $this->Update($img, $adj5min, $adj5max);
                break;
            default:
                Util\JpGraphError::RaiseL(25073, $r); //('Internal error. Integer scale algorithm comparison out of bound (r=$r)');
        }
    }

    // Calculate autoscale. Used if user hasn't given a scale and ticks
    // $maxsteps is the maximum number of major tickmarks allowed.
    public function AutoScale($img, $min, $max, $maxsteps, $majend = true)
    {

        if (!is_numeric($min) || !is_numeric($max)) {
            Util\JpGraphError::Raise(25044);
        }

        if ($this->intscale) {
            $this->IntAutoScale($img, $min, $max, $maxsteps, $majend);
            return;
        }
        if (abs($min - $max) < 0.00001) {
            // We need some difference to be able to autoscale
            // make it 5% above and 5% below value
            if ($min == 0 && $max == 0) {
                // Special case
                $min = -1;
                $max = 1;
            } else {
                $delta = (abs($max) + abs($min)) * 0.005;
                $min -= $delta;
                $max += $delta;
            }
        }

        $gracetop = ($this->gracetop / 100.0) * abs($max - $min);
        $gracebottom = ($this->gracebottom / 100.0) * abs($max - $min);
        if (is_numeric($this->autoscale_min)) {
            $min = $this->autoscale_min;
            if ($min >= $max) {
                Util\JpGraphError::RaiseL(25071); //('You have specified a min value with SetAutoMin() which is larger than the maximum value used for the scale. This is not possible.');
            }
            if (abs($min - $max) < 0.001) {
                $max *= 1.2;
            }
        }

        if (is_numeric($this->autoscale_max)) {
            $max = $this->autoscale_max;
            if ($min >= $max) {
                Util\JpGraphError::RaiseL(25072); //('You have specified a max value with SetAutoMax() which is smaller than the miminum value used for the scale. This is not possible.');
            }
            if (abs($min - $max) < 0.001) {
                $min *= 0.8;
            }
        }

        $min -= $gracebottom;
        $max += $gracetop;

        // First get tickmarks as multiples of 0.1, 1, 10, ...
        if ($majend) {
            list($num1steps, $adj1min, $adj1max, $min1step, $maj1step) = $this->CalcTicks($maxsteps, $min, $max, 1, 2);
        } else {
            $adj1min = $min;
            $adj1max = $max;
            list($num1steps, $min1step, $maj1step) = $this->CalcTicksFreeze($maxsteps, $min, $max, 1, 2, false);
        }

        // Then get tick marks as 2:s 0.2, 2, 20, ...
        if ($majend) {
            list($num2steps, $adj2min, $adj2max, $min2step, $maj2step) = $this->CalcTicks($maxsteps, $min, $max, 5, 2);
        } else {
            $adj2min = $min;
            $adj2max = $max;
            list($num2steps, $min2step, $maj2step) = $this->CalcTicksFreeze($maxsteps, $min, $max, 5, 2, false);
        }

        // Then get tickmarks as 5:s 0.05, 0.5, 5, 50, ...
        if ($majend) {
            list($num5steps, $adj5min, $adj5max, $min5step, $maj5step) = $this->CalcTicks($maxsteps, $min, $max, 2, 5);
        } else {
            $adj5min = $min;
            $adj5max = $max;
            list($num5steps, $min5step, $maj5step) = $this->CalcTicksFreeze($maxsteps, $min, $max, 2, 5, false);
        }

        // Check to see whichof 1:s, 2:s or 5:s fit better with
        // the requested number of major ticks
        $match1 = abs($num1steps - $maxsteps);
        $match2 = abs($num2steps - $maxsteps);
        $match5 = abs($num5steps - $maxsteps);

        // Compare these three values and see which is the closest match
        // We use a 0.8 weight to gravitate towards multiple of 5:s
        $r = $this->MatchMin3($match1, $match2, $match5, 0.8);
        switch ($r) {
            case 1:
                $this->Update($img, $adj1min, $adj1max);
                $this->ticks->Set($maj1step, $min1step);
                break;
            case 2:
                $this->Update($img, $adj2min, $adj2max);
                $this->ticks->Set($maj2step, $min2step);
                break;
            case 3:
                $this->Update($img, $adj5min, $adj5max);
                $this->ticks->Set($maj5step, $min5step);
                break;
        }
    }

    //---------------
    // PRIVATE METHODS

    // This method recalculates all constants that are depending on the
    // margins in the image. If the margins in the image are changed
    // this method should be called for every scale that is registred with
    // that image. Should really be installed as an observer of that image.
    public function InitConstants($img)
    {
        if ($this->type == 'x') {
            $this->world_abs_size = $img->width - $img->left_margin - $img->right_margin;
            $this->off = $img->left_margin;
            $this->scale_factor = 0;
            if ($this->world_size > 0) {
                $this->scale_factor = $this->world_abs_size / ($this->world_size * 1.0);
            }
        } else {
            // y scale
            $this->world_abs_size = $img->height - $img->top_margin - $img->bottom_margin;
            $this->off = $img->top_margin + $this->world_abs_size;
            $this->scale_factor = 0;
            if ($this->world_size > 0) {
                $this->scale_factor = -$this->world_abs_size / ($this->world_size * 1.0);
            }
        }
        $size = $this->world_size * $this->scale_factor;
        $this->scale_abs = array($this->off, $this->off + $size);
    }

    // Initialize the conversion constants for this scale
    // This tries to pre-calculate as much as possible to speed up the
    // actual conversion (with Translate()) later on
    // $start =scale start in absolute pixels (for x-scale this is an y-position
    //     and for an y-scale this is an x-position
    // $len   =absolute length in pixels of scale
    public function SetConstants($aStart, $aLen)
    {
        $this->world_abs_size = $aLen;
        $this->off = $aStart;

        if ($this->world_size <= 0) {
            // This should never ever happen !!
            Util\JpGraphError::RaiseL(25074);
            //("You have unfortunately stumbled upon a bug in JpGraph. It seems like the scale range is ".$this->world_size." [for ".$this->type." scale] <br> Please report Bug #01 to info@jpgraph.net and include the script that gave this error. This problem could potentially be caused by trying to use \"illegal\" values in the input data arrays (like trying to send in strings or only NULL values) which causes the autoscaling to fail.");
        }

        // scale_factor = number of pixels per world unit
        $this->scale_factor = $this->world_abs_size / ($this->world_size * 1.0);

        // scale_abs = start and end points of scale in absolute pixels
        $this->scale_abs = array($this->off, $this->off + $this->world_size * $this->scale_factor);
    }

    // Calculate number of ticks steps with a specific division
    // $a is the divisor of 10**x to generate the first maj tick intervall
    // $a=1, $b=2 give major ticks with multiple of 10, ...,0.1,1,10,...
    // $a=5, $b=2 give major ticks with multiple of 2:s ...,0.2,2,20,...
    // $a=2, $b=5 give major ticks with multiple of 5:s ...,0.5,5,50,...
    // We return a vector of
    //  [$numsteps,$adjmin,$adjmax,$minstep,$majstep]
    // If $majend==true then the first and last marks on the axis will be major
    // labeled tick marks otherwise it will be adjusted to the closest min tick mark
    public function CalcTicks($maxsteps, $min, $max, $a, $b, $majend = true)
    {
        $diff = $max - $min;
        if ($diff == 0) {
            $ld = 0;
        } else {
            $ld = floor(log10($diff));
        }

        // Gravitate min towards zero if we are close
        if ($min > 0 && $min < pow(10, $ld)) {
            $min = 0;
        }

        //$majstep=pow(10,$ld-1)/$a;
        $majstep = pow(10, $ld) / $a;
        $minstep = $majstep / $b;

        $adjmax = ceil($max / $minstep) * $minstep;
        $adjmin = floor($min / $minstep) * $minstep;
        $adjdiff = $adjmax - $adjmin;
        $numsteps = $adjdiff / $majstep;

        while ($numsteps > $maxsteps) {
            $majstep = pow(10, $ld) / $a;
            $numsteps = $adjdiff / $majstep;
            ++$ld;
        }

        $minstep = $majstep / $b;
        $adjmin = floor($min / $minstep) * $minstep;
        $adjdiff = $adjmax - $adjmin;
        if ($majend) {
            $adjmin = floor($min / $majstep) * $majstep;
            $adjdiff = $adjmax - $adjmin;
            $adjmax = ceil($adjdiff / $majstep) * $majstep + $adjmin;
        } else {
            $adjmax = ceil($max / $minstep) * $minstep;
        }

        return array($numsteps, $adjmin, $adjmax, $minstep, $majstep);
    }

    public function CalcTicksFreeze($maxsteps, $min, $max, $a, $b)
    {
        // Same as CalcTicks but don't adjust min/max values
        $diff = $max - $min;
        if ($diff == 0) {
            $ld = 0;
        } else {
            $ld = floor(log10($diff));
        }

        //$majstep=pow(10,$ld-1)/$a;
        $majstep = pow(10, $ld) / $a;
        $minstep = $majstep / $b;
        $numsteps = floor($diff / $majstep);

        while ($numsteps > $maxsteps) {
            $majstep = pow(10, $ld) / $a;
            $numsteps = floor($diff / $majstep);
            ++$ld;
        }
        $minstep = $majstep / $b;
        return array($numsteps, $minstep, $majstep);
    }

    public function IntCalcTicks($maxsteps, $min, $max, $a, $majend = true)
    {
        $diff = $max - $min;
        if ($diff == 0) {
            Util\JpGraphError::RaiseL(25075); //('Can\'t automatically determine ticks since min==max.');
        } else {
            $ld = floor(log10($diff));
        }

        // Gravitate min towards zero if we are close
        if ($min > 0 && $min < pow(10, $ld)) {
            $min = 0;
        }
        if ($ld == 0) {
            $ld = 1;
        }
        if ($a == 1) {
            $majstep = 1;
        } else {
            $majstep = pow(10, $ld) / $a;
        }
        $adjmax = ceil($max / $majstep) * $majstep;

        $adjmin = floor($min / $majstep) * $majstep;
        $adjdiff = $adjmax - $adjmin;
        $numsteps = $adjdiff / $majstep;
        while ($numsteps > $maxsteps) {
            $majstep = pow(10, $ld) / $a;
            $numsteps = $adjdiff / $majstep;
            ++$ld;
        }

        $adjmin = floor($min / $majstep) * $majstep;
        $adjdiff = $adjmax - $adjmin;
        if ($majend) {
            $adjmin = floor($min / $majstep) * $majstep;
            $adjdiff = $adjmax - $adjmin;
            $adjmax = ceil($adjdiff / $majstep) * $majstep + $adjmin;
        } else {
            $adjmax = ceil($max / $majstep) * $majstep;
        }

        return array($numsteps, $adjmin, $adjmax, $majstep);
    }

    public function IntCalcTicksFreeze($maxsteps, $min, $max, $a)
    {
        // Same as IntCalcTick but don't change min/max values
        $diff = $max - $min;
        if ($diff == 0) {
            Util\JpGraphError::RaiseL(25075); //('Can\'t automatically determine ticks since min==max.');
        } else {
            $ld = floor(log10($diff));
        }
        if ($ld == 0) {
            $ld = 1;
        }
        if ($a == 1) {
            $majstep = 1;
        } else {
            $majstep = pow(10, $ld) / $a;
        }

        $numsteps = floor($diff / $majstep);
        while ($numsteps > $maxsteps) {
            $majstep = pow(10, $ld) / $a;
            $numsteps = floor($diff / $majstep);
            ++$ld;
        }

        return array($numsteps, $majstep);
    }

    // Determine the minimum of three values witha  weight for last value
    public function MatchMin3($a, $b, $c, $weight)
    {
        if ($a < $b) {
            if ($a < ($c * $weight)) {
                return 1; // $a smallest
            } else {
                return 3; // $c smallest
            }
        } elseif ($b < ($c * $weight)) {
            return 2; // $b smallest
        }
        return 3; // $c smallest
    }

    public function __get($name)
    {
        $variable_name = '_' . $name;

        if (isset($this->$variable_name)) {
            return $this->$variable_name * SUPERSAMPLING_SCALE;
        } else {
            Util\JpGraphError::RaiseL('25132', $name);
        }
    }

    public function __set($name, $value)
    {
        $this->{'_' . $name} = $value;
    }
} // Class
