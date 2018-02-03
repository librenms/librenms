<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS GanttPlotObject
// The common signature for a Gantt object
//===================================================
class GanttPlotObject
{
    public $title;
    public $caption;
    public $csimarea                 = '';
    public $csimtarget               = '';
    public $csimwintarget            = '';
    public $csimalt                  = '';
    public $constraints              = array();
    public $iCaptionMargin           = 5;
    public $iConstrainPos            = array();
    protected $iStart                = ""; // Start date
    public $iVPos                    = 0; // Vertical position
    protected $iLabelLeftMargin      = 2; // Title margin

    public function __construct()
    {
        $this->title = new TextProperty();
        $this->title->Align('left', 'center');
        $this->caption = new TextProperty();
    }

    public function GetCSIMArea()
    {
        return $this->csimarea;
    }

    public function SetCSIMTarget($aTarget, $aAlt = '', $aWinTarget = '')
    {
        if (!is_string($aTarget)) {
            $tv = substr(var_export($aTarget, true), 0, 40);
            Util\JpGraphError::RaiseL(6024, $tv);
            //('CSIM Target must be specified as a string.'."\nStart of target is:\n$tv");
        }
        if (!is_string($aAlt)) {
            $tv = substr(var_export($aAlt, true), 0, 40);
            Util\JpGraphError::RaiseL(6025, $tv);
            //('CSIM Alt text must be specified as a string.'."\nStart of alt text is:\n$tv");
        }

        $this->csimtarget    = $aTarget;
        $this->csimwintarget = $aWinTarget;
        $this->csimalt       = $aAlt;
    }

    public function SetCSIMAlt($aAlt)
    {
        if (!is_string($aAlt)) {
            $tv = substr(var_export($aAlt, true), 0, 40);
            Util\JpGraphError::RaiseL(6025, $tv);
            //('CSIM Alt text must be specified as a string.'."\nStart of alt text is:\n$tv");
        }
        $this->csimalt = $aAlt;
    }

    public function SetConstrain($aRow, $aType, $aColor = 'black', $aArrowSize = ARROW_S2, $aArrowType = ARROWT_SOLID)
    {
        $this->constraints[] = new GanttConstraint($aRow, $aType, $aColor, $aArrowSize, $aArrowType);
    }

    public function SetConstrainPos($xt, $yt, $xb, $yb)
    {
        $this->iConstrainPos = array($xt, $yt, $xb, $yb);
    }

    public function GetMinDate()
    {
        return $this->iStart;
    }

    public function GetMaxDate()
    {
        return $this->iStart;
    }

    public function SetCaptionMargin($aMarg)
    {
        $this->iCaptionMargin = $aMarg;
    }

    public function GetAbsHeight($aImg)
    {
        return 0;
    }

    public function GetLineNbr()
    {
        return $this->iVPos;
    }

    public function SetLabelLeftMargin($aOff)
    {
        $this->iLabelLeftMargin = $aOff;
    }

    public function StrokeActInfo($aImg, $aScale, $aYPos)
    {
        $cols = array();
        $aScale->actinfo->GetColStart($aImg, $cols, true);
        $this->title->Stroke($aImg, $cols, $aYPos);
    }
}
