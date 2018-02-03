<?php

namespace Amenadiel\JpGraph\Graph;

//===================================================
// CLASS Ticks
// Description: Abstract base class for drawing linear and logarithmic
// tick marks on axis
//===================================================
class Ticks
{
    public $label_formatstr                  = ''; // C-style format string to use for labels
    public $label_formfunc                   = '';
    public $label_dateformatstr              = '';
    public $direction                        = 1; // Should ticks be in(=1) the plot area or outside (=-1)
    public $supress_last                     = false;
    public $supress_tickmarks                = false;
    public $supress_minor_tickmarks          = false;
    public $maj_ticks_pos                    = array();
    public $maj_ticklabels_pos               = array();
    public $ticks_pos                        = array();
    public $maj_ticks_label                  = array();
    public $precision;

    protected $minor_abs_size = 3;
    protected $major_abs_size = 5;
    protected $scale;
    protected $is_set              = false;
    protected $supress_zerolabel   = false;
    protected $supress_first       = false;
    protected $mincolor            = '';
    protected $majcolor            = '';
    protected $weight              = 1;
    protected $label_usedateformat = false;

    public function __construct($aScale)
    {
        $this->scale     = $aScale;
        $this->precision = -1;
    }

    // Set format string for automatic labels
    public function SetLabelFormat($aFormatString, $aDate = false)
    {
        $this->label_formatstr     = $aFormatString;
        $this->label_usedateformat = $aDate;
    }

    public function SetLabelDateFormat($aFormatString)
    {
        $this->label_dateformatstr = $aFormatString;
    }

    public function SetFormatCallback($aCallbackFuncName)
    {
        $this->label_formfunc = $aCallbackFuncName;
    }

    // Don't display the first zero label
    public function SupressZeroLabel($aFlag = true)
    {
        $this->supress_zerolabel = $aFlag;
    }

    // Don't display minor tick marks
    public function SupressMinorTickMarks($aHide = true)
    {
        $this->supress_minor_tickmarks = $aHide;
    }

    // Don't display major tick marks
    public function SupressTickMarks($aHide = true)
    {
        $this->supress_tickmarks = $aHide;
    }

    // Hide the first tick mark
    public function SupressFirst($aHide = true)
    {
        $this->supress_first = $aHide;
    }

    // Hide the last tick mark
    public function SupressLast($aHide = true)
    {
        $this->supress_last = $aHide;
    }

    // Size (in pixels) of minor tick marks
    public function GetMinTickAbsSize()
    {
        return $this->minor_abs_size;
    }

    // Size (in pixels) of major tick marks
    public function GetMajTickAbsSize()
    {
        return $this->major_abs_size;
    }

    public function SetSize($aMajSize, $aMinSize = 3)
    {
        $this->major_abs_size = $aMajSize;
        $this->minor_abs_size = $aMinSize;
    }

    // Have the ticks been specified
    public function IsSpecified()
    {
        return $this->is_set;
    }

    public function SetSide($aSide)
    {
        $this->direction = $aSide;
    }

    // Which side of the axis should the ticks be on
    public function SetDirection($aSide = SIDE_RIGHT)
    {
        $this->direction = $aSide;
    }

    // Set colors for major and minor tick marks
    public function SetMarkColor($aMajorColor, $aMinorColor = '')
    {
        $this->SetColor($aMajorColor, $aMinorColor);
    }

    public function SetColor($aMajorColor, $aMinorColor = '')
    {
        $this->majcolor = $aMajorColor;

        // If not specified use same as major
        if ($aMinorColor == '') {
            $this->mincolor = $aMajorColor;
        } else {
            $this->mincolor = $aMinorColor;
        }
    }

    public function SetWeight($aWeight)
    {
        $this->weight = $aWeight;
    }
} // Class
