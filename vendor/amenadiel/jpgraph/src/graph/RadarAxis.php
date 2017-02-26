<?php
namespace Amenadiel\JpGraph\Graph;

//===================================================
// CLASS RadarAxis
// Description: Implements axis for the radar graph
//===================================================
class RadarAxis extends AxisPrototype
{
    public $title = null;
    private $title_color = 'navy';
    private $len = 0;

    public function __construct($img, $aScale, $color = array(0, 0, 0))
    {
        parent::__construct($img, $aScale, $color);
        $this->len = $img->plotheight;
        $this->title = new Text();
        $this->title->SetFont(FF_FONT1, FS_BOLD);
        $this->color = array(0, 0, 0);
    }

    // Stroke the axis
    // $pos    = Vertical position of axis
    // $aAxisAngle = Axis angle
    // $grid   = Returns an array with positions used to draw the grid
    // $lf   = Label flag, TRUE if the axis should have labels
    public function Stroke($pos, $aAxisAngle, &$grid, $title, $lf)
    {
        $this->img->SetColor($this->color);

        // Determine end points for the axis
        $x = round($this->scale->world_abs_size * cos($aAxisAngle) + $this->scale->scale_abs[0]);
        $y = round($pos - $this->scale->world_abs_size * sin($aAxisAngle));

        // Draw axis
        $this->img->SetColor($this->color);
        $this->img->SetLineWeight($this->weight);
        if (!$this->hide) {
            $this->img->Line($this->scale->scale_abs[0], $pos, $x, $y);
        }

        $this->scale->ticks->Stroke($this->img, $grid, $pos, $aAxisAngle, $this->scale, $majpos, $majlabel);
        $ncolor = 0;
        if (isset($this->ticks_label_colors)) {
            $ncolor = count($this->ticks_label_colors);
        }

        // Draw labels
        if ($lf && !$this->hide) {
            $this->img->SetFont($this->font_family, $this->font_style, $this->font_size);
            $this->img->SetTextAlign('left', 'top');
            $this->img->SetColor($this->label_color);

            // majpos contains (x,y) coordinates for labels
            if (!$this->hide_labels) {
                $n = floor(count($majpos) / 2);
                for ($i = 0; $i < $n; ++$i) {
                    // Set specific label color if specified
                    if ($ncolor > 0) {
                        $this->img->SetColor($this->ticks_label_colors[$i % $ncolor]);
                    }

                    if ($this->ticks_label != null && isset($this->ticks_label[$i])) {
                        $this->img->StrokeText($majpos[$i * 2], $majpos[$i * 2 + 1], $this->ticks_label[$i]);
                    } else {
                        $this->img->StrokeText($majpos[$i * 2], $majpos[$i * 2 + 1], $majlabel[$i]);
                    }
                }
            }
        }
        $this->_StrokeAxisTitle($pos, $aAxisAngle, $title);
    }

    public function _StrokeAxisTitle($pos, $aAxisAngle, $title)
    {
        $this->title->Set($title);
        $marg = 6 + $this->title->margin;
        $xt = round(($this->scale->world_abs_size + $marg) * cos($aAxisAngle) + $this->scale->scale_abs[0]);
        $yt = round($pos - ($this->scale->world_abs_size + $marg) * sin($aAxisAngle));

        // Position the axis title.
        // dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
        // that intersects with the extension of the corresponding axis. The code looks a little
        // bit messy but this is really the only way of having a reasonable position of the
        // axis titles.
        if ($this->title->iWordwrap > 0) {
            $title = wordwrap($title, $this->title->iWordwrap, "\n");
        }

        $h = $this->img->GetTextHeight($title) * 1.2;
        $w = $this->img->GetTextWidth($title) * 1.2;

        while ($aAxisAngle > 2 * M_PI) {
            $aAxisAngle -= 2 * M_PI;
        }

        // Around 3 a'clock
        if ($aAxisAngle >= 7 * M_PI / 4 || $aAxisAngle <= M_PI / 4) {
            $dx = -0.15;
        }
        // Small trimming to make the dist to the axis more even

        // Around 12 a'clock
        if ($aAxisAngle >= M_PI / 4 && $aAxisAngle <= 3 * M_PI / 4) {
            $dx = ($aAxisAngle - M_PI / 4) * 2 / M_PI;
        }

        // Around 9 a'clock
        if ($aAxisAngle >= 3 * M_PI / 4 && $aAxisAngle <= 5 * M_PI / 4) {
            $dx = 1;
        }

        // Around 6 a'clock
        if ($aAxisAngle >= 5 * M_PI / 4 && $aAxisAngle <= 7 * M_PI / 4) {
            $dx = (1 - ($aAxisAngle - M_PI * 5 / 4) * 2 / M_PI);
        }

        if ($aAxisAngle >= 7 * M_PI / 4) {
            $dy = (($aAxisAngle - M_PI) - 3 * M_PI / 4) * 2 / M_PI;
        }

        if ($aAxisAngle <= M_PI / 12) {
            $dy = (0.5 - $aAxisAngle * 2 / M_PI);
        }

        if ($aAxisAngle <= M_PI / 4 && $aAxisAngle > M_PI / 12) {
            $dy = (1 - $aAxisAngle * 2 / M_PI);
        }

        if ($aAxisAngle >= M_PI / 4 && $aAxisAngle <= 3 * M_PI / 4) {
            $dy = 1;
        }

        if ($aAxisAngle >= 3 * M_PI / 4 && $aAxisAngle <= 5 * M_PI / 4) {
            $dy = (1 - ($aAxisAngle - 3 * M_PI / 4) * 2 / M_PI);
        }

        if ($aAxisAngle >= 5 * M_PI / 4 && $aAxisAngle <= 7 * M_PI / 4) {
            $dy = 0;
        }

        if (!$this->hide) {
            $this->title->Stroke($this->img, $xt - $dx * $w, $yt - $dy * $h, $title);
        }
    }

} // Class
