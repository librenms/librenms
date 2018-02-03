<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS Axis
// Description: Defines X and Y axis. Notes that at the
// moment the code is not really good since the axis on
// several occasion must know wheter it's an X or Y axis.
// This was a design decision to make the code easier to
// follow.
//===================================================
class Axis extends AxisPrototype
{
    public function __construct($img, $aScale, $color = 'black')
    {
        parent::__construct($img, $aScale, $color);
    }

    // Stroke the axis.
    public function Stroke($aOtherAxisScale, $aStrokeLabels = true)
    {
        if ($this->hide) {
            return;
        }

        if (is_numeric($this->pos)) {
            $pos = $aOtherAxisScale->Translate($this->pos);
        } else {
            // Default to minimum of other scale if pos not set
            if (($aOtherAxisScale->GetMinVal() >= 0 && $this->pos == false) || $this->pos == 'min') {
                $pos = $aOtherAxisScale->scale_abs[0];
            } elseif ($this->pos == "max") {
                $pos = $aOtherAxisScale->scale_abs[1];
            } else {
                // If negative set x-axis at 0
                $this->pos = 0;
                $pos       = $aOtherAxisScale->Translate(0);
            }
        }

        $pos += $this->iDeltaAbsPos;
        $this->img->SetLineWeight($this->weight);
        $this->img->SetColor($this->color);
        $this->img->SetFont($this->font_family, $this->font_style, $this->font_size);

        if ($this->scale->type == "x") {
            if (!$this->hide_line) {
                // Stroke X-axis
                $this->img->FilledRectangle(
                    $this->img->left_margin,
                    $pos,
                    $this->img->width - $this->img->right_margin,
                    $pos + $this->weight - 1
                );
            }
            if ($this->title_side == SIDE_DOWN) {
                $y      = $pos + $this->img->GetFontHeight() + $this->title_margin + $this->title->margin;
                $yalign = 'top';
            } else {
                $y      = $pos - $this->img->GetFontHeight() - $this->title_margin - $this->title->margin;
                $yalign = 'bottom';
            }

            if ($this->title_adjust == 'high') {
                $this->title->SetPos($this->img->width - $this->img->right_margin, $y, 'right', $yalign);
            } elseif ($this->title_adjust == 'middle' || $this->title_adjust == 'center') {
                $this->title->SetPos(($this->img->width - $this->img->left_margin - $this->img->right_margin) / 2 + $this->img->left_margin, $y, 'center', $yalign);
            } elseif ($this->title_adjust == 'low') {
                $this->title->SetPos($this->img->left_margin, $y, 'left', $yalign);
            } else {
                Util\JpGraphError::RaiseL(25060, $this->title_adjust); //('Unknown alignment specified for X-axis title. ('.$this->title_adjust.')');
            }
        } elseif ($this->scale->type == "y") {
            // Add line weight to the height of the axis since
            // the x-axis could have a width>1 and we want the axis to fit nicely together.
            if (!$this->hide_line) {
                // Stroke Y-axis
                $this->img->FilledRectangle(
                    $pos - $this->weight + 1,
                    $this->img->top_margin,
                    $pos,
                    $this->img->height - $this->img->bottom_margin + $this->weight - 1
                );
            }

            $x = $pos;
            if ($this->title_side == SIDE_LEFT) {
                $x -= $this->title_margin;
                $x -= $this->title->margin;
                $halign = 'right';
            } else {
                $x += $this->title_margin;
                $x += $this->title->margin;
                $halign = 'left';
            }
            // If the user has manually specified an hor. align
            // then we override the automatic settings with this
            // specifed setting. Since default is 'left' we compare
            // with that. (This means a manually set 'left' align
            // will have no effect.)
            if ($this->title->halign != 'left') {
                $halign = $this->title->halign;
            }
            if ($this->title_adjust == 'high') {
                $this->title->SetPos($x, $this->img->top_margin, $halign, 'top');
            } elseif ($this->title_adjust == 'middle' || $this->title_adjust == 'center') {
                $this->title->SetPos($x, ($this->img->height - $this->img->top_margin - $this->img->bottom_margin) / 2 + $this->img->top_margin, $halign, "center");
            } elseif ($this->title_adjust == 'low') {
                $this->title->SetPos($x, $this->img->height - $this->img->bottom_margin, $halign, 'bottom');
            } else {
                Util\JpGraphError::RaiseL(25061, $this->title_adjust); //('Unknown alignment specified for Y-axis title. ('.$this->title_adjust.')');
            }
        }
        $this->scale->ticks->Stroke($this->img, $this->scale, $pos);
        if ($aStrokeLabels) {
            if (!$this->hide_labels) {
                $this->StrokeLabels($pos);
            }
            $this->title->Stroke($this->img);
        }
    }

    //---------------
    // PRIVATE METHODS
    // Draw all the tick labels on major tick marks
    public function StrokeLabels($aPos, $aMinor = false, $aAbsLabel = false)
    {
        if (is_array($this->label_color) && count($this->label_color) > 3) {
            $this->ticks_label_colors = $this->label_color;
            $this->img->SetColor($this->label_color[0]);
        } else {
            $this->img->SetColor($this->label_color);
        }
        $this->img->SetFont($this->font_family, $this->font_style, $this->font_size);
        $yoff = $this->img->GetFontHeight() / 2;

        // Only draw labels at major tick marks
        $nbr = count($this->scale->ticks->maj_ticks_label);

        // We have the option to not-display the very first mark
        // (Usefull when the first label might interfere with another
        // axis.)
        $i = $this->show_first_label ? 0 : 1;
        if (!$this->show_last_label) {
            --$nbr;
        }
        // Now run through all labels making sure we don't overshoot the end
        // of the scale.
        $ncolor = 0;
        if (isset($this->ticks_label_colors)) {
            $ncolor = count($this->ticks_label_colors);
        }
        while ($i < $nbr) {
            // $tpos holds the absolute text position for the label
            $tpos = $this->scale->ticks->maj_ticklabels_pos[$i];

            // Note. the $limit is only used for the x axis since we
            // might otherwise overshoot if the scale has been centered
            // This is due to us "loosing" the last tick mark if we center.
            if ($this->scale->type == 'x' && $tpos > $this->img->width - $this->img->right_margin + 1) {
                return;
            }
            // we only draw every $label_step label
            if (($i % $this->label_step) == 0) {

                // Set specific label color if specified
                if ($ncolor > 0) {
                    $this->img->SetColor($this->ticks_label_colors[$i % $ncolor]);
                }

                // If the label has been specified use that and in other case
                // just label the mark with the actual scale value
                $m = $this->scale->ticks->GetMajor();

                // ticks_label has an entry for each data point and is the array
                // that holds the labels set by the user. If the user hasn't
                // specified any values we use whats in the automatically asigned
                // labels in the maj_ticks_label
                if (isset($this->ticks_label[$i * $m])) {
                    $label = $this->ticks_label[$i * $m];
                } else {
                    if ($aAbsLabel) {
                        $label = abs($this->scale->ticks->maj_ticks_label[$i]);
                    } else {
                        $label = $this->scale->ticks->maj_ticks_label[$i];
                    }

                    // We number the scale from 1 and not from 0 so increase by one
                    if ($this->scale->textscale &&
                        $this->scale->ticks->label_formfunc == '' &&
                        !$this->scale->ticks->HaveManualLabels()) {
                        ++$label;
                    }
                }

                if ($this->scale->type == "x") {
                    if ($this->labelPos == SIDE_DOWN) {
                        if ($this->label_angle == 0 || $this->label_angle == 90) {
                            if ($this->label_halign == '' && $this->label_valign == '') {
                                $this->img->SetTextAlign('center', 'top');
                            } else {
                                $this->img->SetTextAlign($this->label_halign, $this->label_valign);
                            }
                        } else {
                            if ($this->label_halign == '' && $this->label_valign == '') {
                                $this->img->SetTextAlign("right", "top");
                            } else {
                                $this->img->SetTextAlign($this->label_halign, $this->label_valign);
                            }
                        }
                        $this->img->StrokeText($tpos, $aPos + $this->tick_label_margin, $label,
                            $this->label_angle, $this->label_para_align);
                    } else {
                        if ($this->label_angle == 0 || $this->label_angle == 90) {
                            if ($this->label_halign == '' && $this->label_valign == '') {
                                $this->img->SetTextAlign("center", "bottom");
                            } else {
                                $this->img->SetTextAlign($this->label_halign, $this->label_valign);
                            }
                        } else {
                            if ($this->label_halign == '' && $this->label_valign == '') {
                                $this->img->SetTextAlign("right", "bottom");
                            } else {
                                $this->img->SetTextAlign($this->label_halign, $this->label_valign);
                            }
                        }
                        $this->img->StrokeText($tpos, $aPos - $this->tick_label_margin - 1, $label,
                            $this->label_angle, $this->label_para_align);
                    }
                } else {
                    // scale->type == "y"
                    //if( $this->label_angle!=0 )
                    //Util\JpGraphError::Raise(" Labels at an angle are not supported on Y-axis");
                    if ($this->labelPos == SIDE_LEFT) {
                        // To the left of y-axis
                        if ($this->label_halign == '' && $this->label_valign == '') {
                            $this->img->SetTextAlign("right", "center");
                        } else {
                            $this->img->SetTextAlign($this->label_halign, $this->label_valign);
                        }
                        $this->img->StrokeText($aPos - $this->tick_label_margin, $tpos, $label, $this->label_angle, $this->label_para_align);
                    } else {
                        // To the right of the y-axis
                        if ($this->label_halign == '' && $this->label_valign == '') {
                            $this->img->SetTextAlign("left", "center");
                        } else {
                            $this->img->SetTextAlign($this->label_halign, $this->label_valign);
                        }
                        $this->img->StrokeText($aPos + $this->tick_label_margin, $tpos, $label, $this->label_angle, $this->label_para_align);
                    }
                }
            }
            ++$i;
        }
    }
}
