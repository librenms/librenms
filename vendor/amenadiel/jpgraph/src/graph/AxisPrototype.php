<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;
use \Amenadiel\JpGraph\Text\Text;

//===================================================
// CLASS Axis
// Description: Defines X and Y axis. Notes that at the
// moment the code is not really good since the axis on
// several occasion must know wheter it's an X or Y axis.
// This was a design decision to make the code easier to
// follow.
//===================================================
class AxisPrototype
{
    public $scale              = null;
    public $img                = null;
    public $hide               = false;
    public $hide_labels        = false;
    public $title              = null;
    public $font_family        = FF_DEFAULT;
    public $font_style         = FS_NORMAL;
    public $font_size          = 8;
    public $label_angle        = 0;
    public $tick_step          = 1;
    public $pos                = false;
    public $ticks_label        = array();

    protected $weight                   = 1;
    protected $color                    = array(0, 0, 0);
    protected $label_color              = array(0, 0, 0);
    protected $ticks_label_colors       = null;
    protected $show_first_label         = true;
    protected $show_last_label          = true;
    protected $label_step               = 1; // Used by a text axis to specify what multiple of major steps
    // should be labeled.
    protected $labelPos                                 = 0; // Which side of the axis should the labels be?
    protected $title_adjust;
    protected $title_margin;
    protected $title_side                                   = SIDE_LEFT;
    protected $tick_label_margin                            = 5;
    protected $label_halign                                 = '';
    protected $label_valign                                 = '';
    protected $label_para_align                             = 'left';
    protected $hide_line                                    = false;
    protected $iDeltaAbsPos                                 = 0;

    public function __construct($img, $aScale, $color = array(0, 0, 0))
    {
        $this->img   = $img;
        $this->scale = $aScale;
        $this->color = $color;
        $this->title = new Text('');

        if ($aScale->type == 'y') {
            $this->title_margin = 25;
            $this->title_adjust = 'middle';
            $this->title->SetOrientation(90);
            $this->tick_label_margin = 7;
            $this->labelPos          = SIDE_LEFT;
        } else {
            $this->title_margin = 5;
            $this->title_adjust = 'high';
            $this->title->SetOrientation(0);
            $this->tick_label_margin = 5;
            $this->labelPos          = SIDE_DOWN;
            $this->title_side        = SIDE_DOWN;
        }
    }

    public function SetLabelFormat($aFormStr)
    {
        $this->scale->ticks->SetLabelFormat($aFormStr);
    }

    public function SetLabelFormatString($aFormStr, $aDate = false)
    {
        $this->scale->ticks->SetLabelFormat($aFormStr, $aDate);
    }

    public function SetLabelFormatCallback($aFuncName)
    {
        $this->scale->ticks->SetFormatCallback($aFuncName);
    }

    public function SetLabelAlign($aHAlign, $aVAlign = 'top', $aParagraphAlign = 'left')
    {
        $this->label_halign     = $aHAlign;
        $this->label_valign     = $aVAlign;
        $this->label_para_align = $aParagraphAlign;
    }

    // Don't display the first label
    public function HideFirstTickLabel($aShow = false)
    {
        $this->show_first_label = $aShow;
    }

    public function HideLastTickLabel($aShow = false)
    {
        $this->show_last_label = $aShow;
    }

    // Manually specify the major and (optional) minor tick position and labels
    public function SetTickPositions($aMajPos, $aMinPos = null, $aLabels = null)
    {
        $this->scale->ticks->SetTickPositions($aMajPos, $aMinPos, $aLabels);
    }

    // Manually specify major tick positions and optional labels
    public function SetMajTickPositions($aMajPos, $aLabels = null)
    {
        $this->scale->ticks->SetTickPositions($aMajPos, null, $aLabels);
    }

    // Hide minor or major tick marks
    public function HideTicks($aHideMinor = true, $aHideMajor = true)
    {
        $this->scale->ticks->SupressMinorTickMarks($aHideMinor);
        $this->scale->ticks->SupressTickMarks($aHideMajor);
    }

    // Hide zero label
    public function HideZeroLabel($aFlag = true)
    {
        $this->scale->ticks->SupressZeroLabel();
    }

    public function HideFirstLastLabel()
    {
        // The two first calls to ticks method will supress
        // automatically generated scale values. However, that
        // will not affect manually specified value, e.g text-scales.
        // therefor we also make a kludge here to supress manually
        // specified scale labels.
        $this->scale->ticks->SupressLast();
        $this->scale->ticks->SupressFirst();
        $this->show_first_label = false;
        $this->show_last_label  = false;
    }

    // Hide the axis
    public function Hide($aHide = true)
    {
        $this->hide = $aHide;
    }

    // Hide the actual axis-line, but still print the labels
    public function HideLine($aHide = true)
    {
        $this->hide_line = $aHide;
    }

    public function HideLabels($aHide = true)
    {
        $this->hide_labels = $aHide;
    }

    // Weight of axis
    public function SetWeight($aWeight)
    {
        $this->weight = $aWeight;
    }

    // Axis color
    public function SetColor($aColor, $aLabelColor = false)
    {
        $this->color = $aColor;
        if (!$aLabelColor) {
            $this->label_color = $aColor;
        } else {
            $this->label_color = $aLabelColor;
        }
    }

    // Title on axis
    public function SetTitle($aTitle, $aAdjustAlign = 'high')
    {
        $this->title->Set($aTitle);
        $this->title_adjust = $aAdjustAlign;
    }

    // Specify distance from the axis
    public function SetTitleMargin($aMargin)
    {
        $this->title_margin = $aMargin;
    }

    // Which side of the axis should the axis title be?
    public function SetTitleSide($aSideOfAxis)
    {
        $this->title_side = $aSideOfAxis;
    }

    public function SetTickSide($aDir)
    {
        $this->scale->ticks->SetSide($aDir);
    }

    public function SetTickSize($aMajSize, $aMinSize = 3)
    {
        $this->scale->ticks->SetSize($aMajSize, $aMinSize = 3);
    }

    // Specify text labels for the ticks. One label for each data point
    public function SetTickLabels($aLabelArray, $aLabelColorArray = null)
    {
        $this->ticks_label        = $aLabelArray;
        $this->ticks_label_colors = $aLabelColorArray;
    }

    public function SetLabelMargin($aMargin)
    {
        $this->tick_label_margin = $aMargin;
    }

    // Specify that every $step of the ticks should be displayed starting
    // at $start
    public function SetTextTickInterval($aStep, $aStart = 0)
    {
        $this->scale->ticks->SetTextLabelStart($aStart);
        $this->tick_step = $aStep;
    }

    // Specify that every $step tick mark should have a label
    // should be displayed starting
    public function SetTextLabelInterval($aStep)
    {
        if ($aStep < 1) {
            Util\JpGraphError::RaiseL(25058); //(" Text label interval must be specified >= 1.");
        }
        $this->label_step = $aStep;
    }

    public function SetLabelSide($aSidePos)
    {
        $this->labelPos = $aSidePos;
    }

    // Set the font
    public function SetFont($aFamily, $aStyle = FS_NORMAL, $aSize = 10)
    {
        $this->font_family = $aFamily;
        $this->font_style  = $aStyle;
        $this->font_size   = $aSize;
    }

    // Position for axis line on the "other" scale
    public function SetPos($aPosOnOtherScale)
    {
        $this->pos = $aPosOnOtherScale;
    }

    // Set the position of the axis to be X-pixels delta to the right
    // of the max X-position (used to position the multiple Y-axis)
    public function SetPosAbsDelta($aDelta)
    {
        $this->iDeltaAbsPos = $aDelta;
    }

    // Specify the angle for the tick labels
    public function SetLabelAngle($aAngle)
    {
        $this->label_angle = $aAngle;
    }
} // Class
