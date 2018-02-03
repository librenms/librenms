<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS GanttBar
// Responsible for formatting individual gantt bars
//===================================================
class GanttBar extends GanttPlotObject
{
    public $progress;
    public $leftMark;
    public $rightMark;
    private $iEnd;
    private $iHeightFactor        = 0.5;
    private $iFillColor           = "white";
    private $iFrameColor          = "black";
    private $iShadow              = false;
    private $iShadowColor         = "darkgray";
    private $iShadowWidth         = 1;
    private $iShadowFrame         = "black";
    private $iPattern             = GANTT_RDIAG;
    private $iPatternColor        = "blue";
    private $iPatternDensity      = 95;
    private $iBreakStyle          = false;
    private $iBreakLineStyle      = 'dotted';
    private $iBreakLineWeight     = 1;
    //---------------
    // CONSTRUCTOR
    public function __construct($aPos, $aLabel, $aStart, $aEnd, $aCaption = "", $aHeightFactor = 0.6)
    {
        parent::__construct();
        $this->iStart = $aStart;
        // Is the end date given as a date or as number of days added to start date?
        if (is_string($aEnd)) {
            // If end date has been specified without a time we will asssume
            // end date is at the end of that date
            if (strpos($aEnd, ':') === false) {
                $this->iEnd = strtotime($aEnd) + SECPERDAY - 1;
            } else {
                $this->iEnd = $aEnd;
            }
        } elseif (is_int($aEnd) || is_float($aEnd)) {
            $this->iEnd = strtotime($aStart) + round($aEnd * SECPERDAY);
        }
        $this->iVPos         = $aPos;
        $this->iHeightFactor = $aHeightFactor;
        $this->title->Set($aLabel);
        $this->caption = new TextProperty($aCaption);
        $this->caption->Align("left", "center");
        $this->leftMark = new PlotMark();
        $this->leftMark->Hide();
        $this->rightMark = new PlotMark();
        $this->rightMark->Hide();
        $this->progress = new Progress();
    }

    //---------------
    // PUBLIC METHODS
    public function SetShadow($aShadow = true, $aColor = "gray")
    {
        $this->iShadow      = $aShadow;
        $this->iShadowColor = $aColor;
    }

    public function SetBreakStyle($aFlg = true, $aLineStyle = 'dotted', $aLineWeight = 1)
    {
        $this->iBreakStyle      = $aFlg;
        $this->iBreakLineStyle  = $aLineStyle;
        $this->iBreakLineWeight = $aLineWeight;
    }

    public function GetMaxDate()
    {
        return $this->iEnd;
    }

    public function SetHeight($aHeight)
    {
        $this->iHeightFactor = $aHeight;
    }

    public function SetColor($aColor)
    {
        $this->iFrameColor = $aColor;
    }

    public function SetFillColor($aColor)
    {
        $this->iFillColor = $aColor;
    }

    public function GetAbsHeight($aImg)
    {
        if (is_int($this->iHeightFactor) || $this->leftMark->show || $this->rightMark->show) {
            $m = -1;
            if (is_int($this->iHeightFactor)) {
                $m = $this->iHeightFactor;
            }

            if ($this->leftMark->show) {
                $m = max($m, $this->leftMark->width * 2);
            }

            if ($this->rightMark->show) {
                $m = max($m, $this->rightMark->width * 2);
            }

            return $m;
        } else {
            return -1;
        }
    }

    public function SetPattern($aPattern, $aColor = "blue", $aDensity = 95)
    {
        $this->iPattern        = $aPattern;
        $this->iPatternColor   = $aColor;
        $this->iPatternDensity = $aDensity;
    }

    public function Stroke($aImg, $aScale)
    {
        $factory = new RectPatternFactory();
        $prect   = $factory->Create($this->iPattern, $this->iPatternColor);
        $prect->SetDensity($this->iPatternDensity);

        // If height factor is specified as a float between 0,1 then we take it as meaning
        // percetage of the scale width between horizontal line.
        // If it is an integer > 1 we take it to mean the absolute height in pixels
        if ($this->iHeightFactor > -0.0 && $this->iHeightFactor <= 1.1) {
            $vs = $aScale->GetVertSpacing() * $this->iHeightFactor;
        } elseif (is_int($this->iHeightFactor) && $this->iHeightFactor > 2 && $this->iHeightFactor < 200) {
            $vs = $this->iHeightFactor;
        } else {
            Util\JpGraphError::RaiseL(6028, $this->iHeightFactor);
            //    ("Specified height (".$this->iHeightFactor.") for gantt bar is out of range.");
        }

        // Clip date to min max dates to show
        $st = $aScale->NormalizeDate($this->iStart);
        $en = $aScale->NormalizeDate($this->iEnd);

        $limst = max($st, $aScale->iStartDate);
        $limen = min($en, $aScale->iEndDate);

        $xt     = round($aScale->TranslateDate($limst));
        $xb     = round($aScale->TranslateDate($limen));
        $yt     = round($aScale->TranslateVertPos($this->iVPos) - $vs - ($aScale->GetVertSpacing() / 2 - $vs / 2));
        $yb     = round($aScale->TranslateVertPos($this->iVPos) - ($aScale->GetVertSpacing() / 2 - $vs / 2));
        $middle = round($yt + ($yb - $yt) / 2);
        $this->StrokeActInfo($aImg, $aScale, $middle);

        // CSIM for title
        if (!empty($this->title->csimtarget)) {
            $colwidth  = $this->title->GetColWidth($aImg);
            $colstarts = [];
            $aScale->actinfo->GetColStart($aImg, $colstarts, true);
            $n = min(count($colwidth), count($this->title->csimtarget));
            for ($i = 0; $i < $n; ++$i) {
                $title_xt = $colstarts[$i];
                $title_xb = $title_xt + $colwidth[$i];
                $coords   = "$title_xt,$yt,$title_xb,$yt,$title_xb,$yb,$title_xt,$yb";

                if (!empty($this->title->csimtarget[$i])) {
                    $this->csimarea .= "<area shape=\"poly\" coords=\"$coords\" href=\"" . $this->title->csimtarget[$i] . "\"";

                    if (!empty($this->title->csimwintarget[$i])) {
                        $this->csimarea .= "target=\"" . $this->title->csimwintarget[$i] . "\" ";
                    }

                    if (!empty($this->title->csimalt[$i])) {
                        $tmp = $this->title->csimalt[$i];
                        $this->csimarea .= " title=\"$tmp\" alt=\"$tmp\" ";
                    }
                    $this->csimarea .= " />\n";
                }
            }
        }

        // Check if the bar is totally outside the current scale range
        if ($en < $aScale->iStartDate || $st > $aScale->iEndDate) {
            return;
        }

        // Remember the positions for the bar
        $this->SetConstrainPos($xt, $yt, $xb, $yb);

        $prect->ShowFrame(false);
        $prect->SetBackground($this->iFillColor);
        if ($this->iBreakStyle) {
            $aImg->SetColor($this->iFrameColor);
            $olds = $aImg->SetLineStyle($this->iBreakLineStyle);
            $oldw = $aImg->SetLineWeight($this->iBreakLineWeight);
            $aImg->StyleLine($xt, $yt, $xb, $yt);
            $aImg->StyleLine($xt, $yb, $xb, $yb);
            $aImg->SetLineStyle($olds);
            $aImg->SetLineWeight($oldw);
        } else {
            if ($this->iShadow) {
                $aImg->SetColor($this->iFrameColor);
                $aImg->ShadowRectangle($xt, $yt, $xb, $yb, $this->iFillColor, $this->iShadowWidth, $this->iShadowColor);
                $prect->SetPos(new Util\Rectangle($xt + 1, $yt + 1, $xb - $xt - $this->iShadowWidth - 2, $yb - $yt - $this->iShadowWidth - 2));
                $prect->Stroke($aImg);
            } else {
                $prect->SetPos(new Util\Rectangle($xt, $yt, $xb - $xt + 1, $yb - $yt + 1));
                $prect->Stroke($aImg);
                $aImg->SetColor($this->iFrameColor);
                $aImg->Rectangle($xt, $yt, $xb, $yb);
            }
        }
        // CSIM for bar
        if (!empty($this->csimtarget)) {
            $coords = "$xt,$yt,$xb,$yt,$xb,$yb,$xt,$yb";
            $this->csimarea .= "<area shape=\"poly\" coords=\"$coords\" href=\"" . $this->csimtarget . "\"";

            if (!empty($this->csimwintarget)) {
                $this->csimarea .= " target=\"" . $this->csimwintarget . "\" ";
            }

            if ($this->csimalt != '') {
                $tmp = $this->csimalt;
                $this->csimarea .= " title=\"$tmp\" alt=\"$tmp\" ";
            }
            $this->csimarea .= " />\n";
        }

        // Draw progress bar inside activity bar
        if ($this->progress->iProgress > 0) {
            $xtp = $aScale->TranslateDate($st);
            $xbp = $aScale->TranslateDate($en);
            $len = ($xbp - $xtp) * $this->progress->iProgress;

            $endpos = $xtp + $len;
            if ($endpos > $xt) {

                // Take away the length of the progress that is not visible (before the start date)
                $len -= ($xt - $xtp);

                // Is the the progress bar visible after the start date?
                if ($xtp < $xt) {
                    $xtp = $xt;
                }

                // Make sure that the progess bar doesn't extend over the end date
                if ($xtp + $len - 1 > $xb) {
                    $len = $xb - $xtp;
                }

                $prog = $factory->Create($this->progress->iPattern, $this->progress->iColor);
                $prog->SetDensity($this->progress->iDensity);
                $prog->SetBackground($this->progress->iFillColor);
                $barheight = ($yb - $yt + 1);
                if ($this->iShadow) {
                    $barheight -= $this->iShadowWidth;
                }

                $progressheight = floor($barheight * $this->progress->iHeight);
                $marg           = ceil(($barheight - $progressheight) / 2);
                $pos            = new Util\Rectangle($xtp, $yt + $marg, $len, $barheight - 2 * $marg);
                $prog->SetPos($pos);
                $prog->Stroke($aImg);
            }
        }

        // We don't plot the end mark if the bar has been capped
        if ($limst == $st) {
            $y = $middle;
            // We treat the RIGHT and LEFT triangle mark a little bi
            // special so that these marks are placed right under the
            // bar.
            if ($this->leftMark->GetType() == MARK_LEFTTRIANGLE) {
                $y = $yb;
            }
            $this->leftMark->Stroke($aImg, $xt, $y);
        }
        if ($limen == $en) {
            $y = $middle;
            // We treat the RIGHT and LEFT triangle mark a little bi
            // special so that these marks are placed right under the
            // bar.
            if ($this->rightMark->GetType() == MARK_RIGHTTRIANGLE) {
                $y = $yb;
            }
            $this->rightMark->Stroke($aImg, $xb, $y);

            $margin = $this->iCaptionMargin;
            if ($this->rightMark->show) {
                $margin += $this->rightMark->GetWidth();
            }

            $this->caption->Stroke($aImg, $xb + $margin, $middle);
        }
    }
}
