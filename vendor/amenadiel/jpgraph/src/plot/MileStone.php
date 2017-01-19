<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS MileStone
// Responsible for formatting individual milestones
//===================================================
class MileStone extends GanttPlotObject
{
    public $mark;

    //---------------
    // CONSTRUCTOR
    public function __construct($aVPos, $aLabel, $aDate, $aCaption = "")
    {
        GanttPlotObject::__construct();
        $this->caption->Set($aCaption);
        $this->caption->Align("left", "center");
        $this->caption->SetFont(FF_FONT1, FS_BOLD);
        $this->title->Set($aLabel);
        $this->title->SetColor("darkred");
        $this->mark = new PlotMark();
        $this->mark->SetWidth(10);
        $this->mark->SetType(MARK_DIAMOND);
        $this->mark->SetColor("darkred");
        $this->mark->SetFillColor("darkred");
        $this->iVPos = $aVPos;
        $this->iStart = $aDate;
    }

    //---------------
    // PUBLIC METHODS

    public function GetAbsHeight($aImg)
    {
        return max($this->title->GetHeight($aImg), $this->mark->GetWidth());
    }

    public function Stroke($aImg, $aScale)
    {
        // Put the mark in the middle at the middle of the day
        $d = $aScale->NormalizeDate($this->iStart) + SECPERDAY / 2;
        $x = $aScale->TranslateDate($d);
        $y = $aScale->TranslateVertPos($this->iVPos) - ($aScale->GetVertSpacing() / 2);

        $this->StrokeActInfo($aImg, $aScale, $y);

        // CSIM for title
        if (!empty($this->title->csimtarget)) {

            $yt = round($y - $this->title->GetHeight($aImg) / 2);
            $yb = round($y + $this->title->GetHeight($aImg) / 2);

            $colwidth = $this->title->GetColWidth($aImg);
            $colstarts = array();
            $aScale->actinfo->GetColStart($aImg, $colstarts, true);
            $n = min(count($colwidth), count($this->title->csimtarget));
            for ($i = 0; $i < $n; ++$i) {
                $title_xt = $colstarts[$i];
                $title_xb = $title_xt + $colwidth[$i];
                $coords = "$title_xt,$yt,$title_xb,$yt,$title_xb,$yb,$title_xt,$yb";

                if (!empty($this->title->csimtarget[$i])) {

                    $this->csimarea .= "<area shape=\"poly\" coords=\"$coords\" href=\"" . $this->title->csimtarget[$i] . "\"";

                    if (!empty($this->title->csimwintarget[$i])) {
                        $this->csimarea .= "target=\"" . $this->title->csimwintarget[$i] . "\"";
                    }

                    if (!empty($this->title->csimalt[$i])) {
                        $tmp = $this->title->csimalt[$i];
                        $this->csimarea .= " title=\"$tmp\" alt=\"$tmp\" ";
                    }
                    $this->csimarea .= " />\n";
                }
            }
        }

        if ($d < $aScale->iStartDate || $d > $aScale->iEndDate) {
            return;
        }

        // Remember the coordinates for any constrains linking to
        // this milestone
        $w = $this->mark->GetWidth() / 2;
        $this->SetConstrainPos($x, round($y - $w), $x, round($y + $w));

        // Setup CSIM
        if ($this->csimtarget != '') {
            $this->mark->SetCSIMTarget($this->csimtarget);
            $this->mark->SetCSIMAlt($this->csimalt);
        }

        $this->mark->Stroke($aImg, $x, $y);
        $this->caption->Stroke($aImg, $x + $this->mark->width / 2 + $this->iCaptionMargin, $y);

        $this->csimarea .= $this->mark->GetCSIMAreas();
    }
}
