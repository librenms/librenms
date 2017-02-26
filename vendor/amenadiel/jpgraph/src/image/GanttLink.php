<?php
namespace Amenadiel\JpGraph\Image;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS GanttLink
// Handles the drawing of a link line between 2 points
//===================================================

class GanttLink
{
    private $ix1, $ix2, $iy1, $iy2;
    private $iPathType = 2, $iPathExtend = 15;
    private $iColor = 'black', $iWeight = 1;
    private $iArrowSize = ARROW_S2, $iArrowType = ARROWT_SOLID;

    public function __construct($x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0)
    {
        $this->ix1 = $x1;
        $this->ix2 = $x2;
        $this->iy1 = $y1;
        $this->iy2 = $y2;
    }

    public function SetPos($x1, $y1, $x2, $y2)
    {
        $this->ix1 = $x1;
        $this->ix2 = $x2;
        $this->iy1 = $y1;
        $this->iy2 = $y2;
    }

    public function SetPath($aPath)
    {
        $this->iPathType = $aPath;
    }

    public function SetColor($aColor)
    {
        $this->iColor = $aColor;
    }

    public function SetArrow($aSize, $aType = ARROWT_SOLID)
    {
        $this->iArrowSize = $aSize;
        $this->iArrowType = $aType;
    }

    public function SetWeight($aWeight)
    {
        $this->iWeight = $aWeight;
    }

    public function Stroke($aImg)
    {
        // The way the path for the arrow is constructed is partly based
        // on some heuristics. This is not an exact science but draws the
        // path in a way that, for me, makes esthetic sence. For example
        // if the start and end activities are very close we make a small
        // detour to endter the target horixontally. If there are more
        // space between axctivities then no suh detour is made and the
        // target is "hit" directly vertical. I have tried to keep this
        // simple. no doubt this could become almost infinitive complex
        // and have some real AI. Feel free to modify this.
        // This will no-doubt be tweaked as times go by. One design aim
        // is to avoid having the user choose what types of arrow
        // he wants.

        // The arrow is drawn between (x1,y1) to (x2,y2)
        $x1 = $this->ix1;
        $x2 = $this->ix2;
        $y1 = $this->iy1;
        $y2 = $this->iy2;

        // Depending on if the target is below or above we have to
        // handle thi different.
        if ($y2 > $y1) {
            $arrowtype = ARROW_DOWN;
            $midy = round(($y2 - $y1) / 2 + $y1);
            if ($x2 > $x1) {
                switch ($this->iPathType) {
                    case 0:
                        $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        break;
                    case 1:
                    case 2:
                    case 3:
                        $c = array($x1, $y1, $x2, $y1, $x2, $y2);
                        break;
                    default:
                        Util\JpGraphError::RaiseL(6032, $this->iPathType);
                        //('Internal error: Unknown path type (='.$this->iPathType .') specified for link.');
                        exit(1);
                        break;
                }
            } else {
                switch ($this->iPathType) {
                    case 0:
                    case 1:
                        $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        break;
                    case 2:
                        // Always extend out horizontally a bit from the first point
                        // If we draw a link back in time (end to start) and the bars
                        // are very close we also change the path so it comes in from
                        // the left on the activity
                        $c = array($x1, $y1, $x1 + $this->iPathExtend, $y1,
                            $x1 + $this->iPathExtend, $midy,
                            $x2, $midy, $x2, $y2);
                        break;
                    case 3:
                        if ($y2 - $midy < 6) {
                            $c = array($x1, $y1, $x1, $midy,
                                $x2 - $this->iPathExtend, $midy,
                                $x2 - $this->iPathExtend, $y2,
                                $x2, $y2);
                            $arrowtype = ARROW_RIGHT;
                        } else {
                            $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        }
                        break;
                    default:
                        Util\JpGraphError::RaiseL(6032, $this->iPathType);
                        //('Internal error: Unknown path type specified for link.');
                        exit(1);
                        break;
                }
            }
            $arrow = new LinkArrow($x2, $y2, $arrowtype);
        } else {
            // Y2 < Y1
            $arrowtype = ARROW_UP;
            $midy = round(($y1 - $y2) / 2 + $y2);
            if ($x2 > $x1) {
                switch ($this->iPathType) {
                    case 0:
                    case 1:
                        $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        break;
                    case 3:
                        if ($midy - $y2 < 8) {
                            $arrowtype = ARROW_RIGHT;
                            $c = array($x1, $y1, $x1, $y2, $x2, $y2);
                        } else {
                            $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        }
                        break;
                    default:
                        Util\JpGraphError::RaiseL(6032, $this->iPathType);
                        //('Internal error: Unknown path type specified for link.');
                        break;
                }
            } else {
                switch ($this->iPathType) {
                    case 0:
                    case 1:
                        $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        break;
                    case 2:
                        // Always extend out horizontally a bit from the first point
                        $c = array($x1, $y1, $x1 + $this->iPathExtend, $y1,
                            $x1 + $this->iPathExtend, $midy,
                            $x2, $midy, $x2, $y2);
                        break;
                    case 3:
                        if ($midy - $y2 < 16) {
                            $arrowtype = ARROW_RIGHT;
                            $c = array($x1, $y1, $x1, $midy, $x2 - $this->iPathExtend, $midy,
                                $x2 - $this->iPathExtend, $y2,
                                $x2, $y2);
                        } else {
                            $c = array($x1, $y1, $x1, $midy, $x2, $midy, $x2, $y2);
                        }
                        break;
                    default:
                        Util\JpGraphError::RaiseL(6032, $this->iPathType);
                        //('Internal error: Unknown path type specified for link.');
                        break;
                }
            }
            $arrow = new LinkArrow($x2, $y2, $arrowtype);
        }
        $aImg->SetColor($this->iColor);
        $aImg->SetLineWeight($this->iWeight);
        $aImg->Polygon($c);
        $aImg->SetLineWeight(1);
        $arrow->SetColor($this->iColor);
        $arrow->SetSize($this->iArrowSize);
        $arrow->SetType($this->iArrowType);
        $arrow->Stroke($aImg);
    }
}
