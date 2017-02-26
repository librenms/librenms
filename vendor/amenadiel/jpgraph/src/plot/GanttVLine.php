<?php
namespace Amenadiel\JpGraph\Plot;

use Amenadiel\JpGraph\Graph;

class GanttVLine extends GanttPlotObject
{

    private $iLine, $title_margin = 3, $iDayOffset = 0.5;
    private $iStartRow = -1, $iEndRow = -1;

    //---------------
    // CONSTRUCTOR
    public function __construct($aDate, $aTitle = "", $aColor = "darkred", $aWeight = 2, $aStyle = "solid")
    {
        GanttPlotObject::__construct();
        $this->iLine = new Graph\LineProperty();
        $this->iLine->SetColor($aColor);
        $this->iLine->SetWeight($aWeight);
        $this->iLine->SetStyle($aStyle);
        $this->iStart = $aDate;
        $this->title = new TextPropertyBelow();
        $this->title->Set($aTitle);
    }

    //---------------
    // PUBLIC METHODS

    // Set start and end rows for the VLine. By default the entire heigh of the
    // Gantt chart is used
    public function SetRowSpan($aStart, $aEnd = -1)
    {
        $this->iStartRow = $aStart;
        $this->iEndRow = $aEnd;
    }

    public function SetDayOffset($aOff = 0.5)
    {
        if ($aOff < 0.0 || $aOff > 1.0) {
            Util\JpGraphError::RaiseL(6029);
            //("Offset for vertical line must be in range [0,1]");
        }
        $this->iDayOffset = $aOff;
    }

    public function SetTitleMargin($aMarg)
    {
        $this->title_margin = $aMarg;
    }

    public function SetWeight($aWeight)
    {
        $this->iLine->SetWeight($aWeight);
    }

    public function Stroke($aImg, $aScale)
    {
        $d = $aScale->NormalizeDate($this->iStart);
        if ($d < $aScale->iStartDate || $d > $aScale->iEndDate) {
            return;
        }

        if ($this->iDayOffset != 0.0) {
            $d += 24 * 60 * 60 * $this->iDayOffset;
        }

        $x = $aScale->TranslateDate($d); //d=1006858800,

        if ($this->iStartRow > -1) {
            $y1 = $aScale->TranslateVertPos($this->iStartRow, true);
        } else {
            $y1 = $aScale->iVertHeaderSize + $aImg->top_margin;
        }

        if ($this->iEndRow > -1) {
            $y2 = $aScale->TranslateVertPos($this->iEndRow);
        } else {
            $y2 = $aImg->height - $aImg->bottom_margin;
        }

        $this->iLine->Stroke($aImg, $x, $y1, $x, $y2);
        $this->title->Align("center", "top");
        $this->title->Stroke($aImg, $x, $y2 + $this->title_margin);
    }
}
