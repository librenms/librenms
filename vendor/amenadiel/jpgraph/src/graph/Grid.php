<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS Grid
// Description: responsible for drawing grid lines in graph
//===================================================
class Grid
{
    protected $img;
    protected $scale;
    protected $majorcolor = '#CCCCCC', $minorcolor = '#DDDDDD';
    protected $majortype = 'solid', $minortype = 'solid';
    protected $show = false, $showMinor = false, $majorweight = 1, $minorweight = 1;
    protected $fill = false, $fillcolor = array('#EFEFEF', '#BBCCFF');

    public function __construct($aAxis)
    {
        $this->scale = $aAxis->scale;
        $this->img = $aAxis->img;
    }

    public function SetColor($aMajColor, $aMinColor = false)
    {
        $this->majorcolor = $aMajColor;
        if ($aMinColor === false) {
            $aMinColor = $aMajColor;
        }
        $this->minorcolor = $aMinColor;
    }

    public function SetWeight($aMajorWeight, $aMinorWeight = 1)
    {
        $this->majorweight = $aMajorWeight;
        $this->minorweight = $aMinorWeight;
    }

    // Specify if grid should be dashed, dotted or solid
    public function SetLineStyle($aMajorType, $aMinorType = 'solid')
    {
        $this->majortype = $aMajorType;
        $this->minortype = $aMinorType;
    }

    public function SetStyle($aMajorType, $aMinorType = 'solid')
    {
        $this->SetLineStyle($aMajorType, $aMinorType);
    }

    // Decide if both major and minor grid should be displayed
    public function Show($aShowMajor = true, $aShowMinor = false)
    {
        $this->show = $aShowMajor;
        $this->showMinor = $aShowMinor;
    }

    public function SetFill($aFlg = true, $aColor1 = 'lightgray', $aColor2 = 'lightblue')
    {
        $this->fill = $aFlg;
        $this->fillcolor = array($aColor1, $aColor2);
    }

    // Display the grid
    public function Stroke()
    {
        if ($this->showMinor && !$this->scale->textscale) {
            $this->DoStroke($this->scale->ticks->ticks_pos, $this->minortype, $this->minorcolor, $this->minorweight);
            $this->DoStroke($this->scale->ticks->maj_ticks_pos, $this->majortype, $this->majorcolor, $this->majorweight);
        } else {
            $this->DoStroke($this->scale->ticks->maj_ticks_pos, $this->majortype, $this->majorcolor, $this->majorweight);
        }
    }

    //--------------
    // Private methods
    // Draw the grid
    public function DoStroke($aTicksPos, $aType, $aColor, $aWeight)
    {
        if (!$this->show) {
            return;
        }

        $nbrgrids = count($aTicksPos);

        if ($this->scale->type == 'y') {
            $xl = $this->img->left_margin;
            $xr = $this->img->width - $this->img->right_margin;

            if ($this->fill) {
                // Draw filled areas
                $y2 = $aTicksPos[0];
                $i = 1;
                while ($i < $nbrgrids) {
                    $y1 = $y2;
                    $y2 = $aTicksPos[$i++];
                    $this->img->SetColor($this->fillcolor[$i & 1]);
                    $this->img->FilledRectangle($xl, $y1, $xr, $y2);
                }
            }

            $this->img->SetColor($aColor);
            $this->img->SetLineWeight($aWeight);

            // Draw grid lines
            switch ($aType) {
                case 'solid':$style = LINESTYLE_SOLID;
                    break;
                case 'dotted':$style = LINESTYLE_DOTTED;
                    break;
                case 'dashed':$style = LINESTYLE_DASHED;
                    break;
                case 'longdashed':$style = LINESTYLE_LONGDASH;
                    break;
                default:
                    $style = LINESTYLE_SOLID;
                    break;
            }

            for ($i = 0; $i < $nbrgrids; ++$i) {
                $y = $aTicksPos[$i];
                $this->img->StyleLine($xl, $y, $xr, $y, $style, true);
            }
        } elseif ($this->scale->type == 'x') {
            $yu = $this->img->top_margin;
            $yl = $this->img->height - $this->img->bottom_margin;
            $limit = $this->img->width - $this->img->right_margin;

            if ($this->fill) {
                // Draw filled areas
                $x2 = $aTicksPos[0];
                $i = 1;
                while ($i < $nbrgrids) {
                    $x1 = $x2;
                    $x2 = min($aTicksPos[$i++], $limit);
                    $this->img->SetColor($this->fillcolor[$i & 1]);
                    $this->img->FilledRectangle($x1, $yu, $x2, $yl);
                }
            }

            $this->img->SetColor($aColor);
            $this->img->SetLineWeight($aWeight);

            // We must also test for limit since we might have
            // an offset and the number of ticks is calculated with
            // assumption offset==0 so we might end up drawing one
            // to many gridlines
            $i = 0;
            $x = $aTicksPos[$i];
            while ($i < count($aTicksPos) && ($x = $aTicksPos[$i]) <= $limit) {
                if ($aType == 'solid') {
                    $this->img->Line($x, $yl, $x, $yu);
                } elseif ($aType == 'dotted') {
                    $this->img->DashedLineForGrid($x, $yl, $x, $yu, 1, 6);
                } elseif ($aType == 'dashed') {
                    $this->img->DashedLineForGrid($x, $yl, $x, $yu, 2, 4);
                } elseif ($aType == 'longdashed') {
                    $this->img->DashedLineForGrid($x, $yl, $x, $yu, 8, 6);
                }

                ++$i;
            }
        } else {
            Util\JpGraphError::RaiseL(25054, $this->scale->type); //('Internal error: Unknown grid axis ['.$this->scale->type.']');
        }
        return true;
    }
} // Class
