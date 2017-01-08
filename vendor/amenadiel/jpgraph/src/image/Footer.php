<?php
namespace Amenadiel\JpGraph\Image;

use \Amenadiel\JpGraph\Text\Text;

//=======================================================
// CLASS Footer
// Description: Encapsulates the footer line in the Graph
//=======================================================
class Footer
{
    public $iLeftMargin = 3, $iRightMargin = 3, $iBottomMargin = 3;
    public $left, $center, $right;
    private $iTimer = null, $itimerpoststring = '';

    public function __construct()
    {
        $this->left = new Text();
        $this->left->ParagraphAlign('left');
        $this->center = new Text();
        $this->center->ParagraphAlign('center');
        $this->right = new Text();
        $this->right->ParagraphAlign('right');
    }

    public function SetTimer($aTimer, $aTimerPostString = '')
    {
        $this->iTimer = $aTimer;
        $this->itimerpoststring = $aTimerPostString;
    }

    public function SetMargin($aLeft = 3, $aRight = 3, $aBottom = 3)
    {
        $this->iLeftMargin = $aLeft;
        $this->iRightMargin = $aRight;
        $this->iBottomMargin = $aBottom;
    }

    public function Stroke($aImg)
    {
        $y = $aImg->height - $this->iBottomMargin;
        $x = $this->iLeftMargin;
        $this->left->Align('left', 'bottom');
        $this->left->Stroke($aImg, $x, $y);

        $x = ($aImg->width - $this->iLeftMargin - $this->iRightMargin) / 2;
        $this->center->Align('center', 'bottom');
        $this->center->Stroke($aImg, $x, $y);

        $x = $aImg->width - $this->iRightMargin;
        $this->right->Align('right', 'bottom');
        if ($this->iTimer != null) {
            $this->right->Set($this->right->t . sprintf('%.3f', $this->iTimer->Pop() / 1000.0) . $this->itimerpoststring);
        }
        $this->right->Stroke($aImg, $x, $y);
    }
}
