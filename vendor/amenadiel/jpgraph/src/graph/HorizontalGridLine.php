<?php
namespace Amenadiel\JpGraph\Graph;

define('GANTT_HGRID1', 0);
define('GANTT_HGRID2', 1);

//===================================================
// CLASS HorizontalGridLine
// Responsible for drawinf horizontal gridlines and filled alternatibg rows
//===================================================
class HorizontalGridLine
{
    private $iGraph = null;
    private $iRowColor1 = '', $iRowColor2 = '';
    private $iShow = false;
    private $line = null;
    private $iStart = 0; // 0=from left margin, 1=just along header

    public function __construct()
    {
        $this->line = new LineProperty();
        $this->line->SetColor('gray@0.4');
        $this->line->SetStyle('dashed');
    }

    public function Show($aShow = true)
    {
        $this->iShow = $aShow;
    }

    public function SetRowFillColor($aColor1, $aColor2 = '')
    {
        $this->iRowColor1 = $aColor1;
        $this->iRowColor2 = $aColor2;
    }

    public function SetStart($aStart)
    {
        $this->iStart = $aStart;
    }

    public function Stroke($aImg, $aScale)
    {

        if (!$this->iShow) {
            return;
        }

        // Get horizontal width of line
        /*
        $limst = $aScale->iStartDate;
        $limen = $aScale->iEndDate;
        $xt = round($aScale->TranslateDate($aScale->iStartDate));
        $xb = round($aScale->TranslateDate($limen));
         */

        if ($this->iStart === 0) {
            $xt = $aImg->left_margin - 1;
        } else {
            $xt = round($aScale->TranslateDate($aScale->iStartDate)) + 1;
        }

        $xb = $aImg->width - $aImg->right_margin;

        $yt = round($aScale->TranslateVertPos(0));
        $yb = round($aScale->TranslateVertPos(1));
        $height = $yb - $yt;

        // Loop around for all lines in the chart
        for ($i = 0; $i < $aScale->iVertLines; ++$i) {
            $yb = $yt - $height;
            $this->line->Stroke($aImg, $xt, $yb, $xb, $yb);
            if ($this->iRowColor1 !== '') {
                if ($i % 2 == 0) {
                    $aImg->PushColor($this->iRowColor1);
                    $aImg->FilledRectangle($xt, $yt, $xb, $yb);
                    $aImg->PopColor();
                } elseif ($this->iRowColor2 !== '') {
                    $aImg->PushColor($this->iRowColor2);
                    $aImg->FilledRectangle($xt, $yt, $xb, $yb);
                    $aImg->PopColor();
                }
            }
            $yt = round($aScale->TranslateVertPos($i + 1));
        }
        $yb = $yt - $height;
        $this->line->Stroke($aImg, $xt, $yb, $xb, $yb);
    }
}
