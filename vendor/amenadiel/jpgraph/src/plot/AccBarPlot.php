<?php
namespace Amenadiel\JpGraph\Plot;

//===================================================
// CLASS AccBarPlot
// Description: Produce accumulated bar plots
//===================================================
class AccBarPlot extends BarPlot
{
    public $plots     = null;
    private $nbrplots = 0;
    //---------------
    // CONSTRUCTOR
    public function __construct($plots)
    {
        $this->plots    = $plots;
        $this->nbrplots = count($plots);
        if ($this->nbrplots < 1) {
            Util\JpGraphError::RaiseL(2010); //('Cannot create AccBarPlot from empty plot array.');
        }
        for ($i = 0; $i < $this->nbrplots; ++$i) {
            if (empty($this->plots[$i]) || !isset($this->plots[$i])) {
                Util\JpGraphError::RaiseL(2011, $i); //("Acc bar plot element nbr $i is undefined or empty.");
            }
        }

        // We can only allow individual plost which do not have specified X-positions
        for ($i = 0; $i < $this->nbrplots; ++$i) {
            if (!empty($this->plots[$i]->coords[1])) {
                Util\JpGraphError::RaiseL(2015);
                //'Individual bar plots in an AccBarPlot or GroupBarPlot can not have specified X-positions.');
            }
        }

        // Use 0 weight by default which means that the individual bar
        // weights will be used per part n the accumulated bar
        $this->SetWeight(0);

        $this->numpoints = $plots[0]->numpoints;
        $this->value     = new DisplayValue();
    }

    //---------------
    // PUBLIC METHODS
    public function Legend($graph)
    {
        $n = count($this->plots);
        for ($i = $n - 1; $i >= 0; --$i) {
            $c = get_class($this->plots[$i]);
            if (!($this->plots[$i] instanceof BarPlot)) {
                Util\JpGraphError::RaiseL(2012, $c);
                //('One of the objects submitted to AccBar is not a BarPlot. Make sure that you create the AccBar plot from an array of BarPlot objects.(Class='.$c.')');
            }
            $this->plots[$i]->DoLegend($graph);
        }
    }

    public function Max()
    {
        list($xmax) = $this->plots[0]->Max();
        $nmax       = 0;
        for ($i = 0; $i < count($this->plots); ++$i) {
            $n       = count($this->plots[$i]->coords[0]);
            $nmax    = max($nmax, $n);
            list($x) = $this->plots[$i]->Max();
            $xmax    = max($xmax, $x);
        }
        for ($i = 0; $i < $nmax; $i++) {
            // Get y-value for bar $i by adding the
            // individual bars from all the plots added.
            // It would be wrong to just add the
            // individual plots max y-value since that
            // would in most cases give to large y-value.
            $y = 0;
            if (!isset($this->plots[0]->coords[0][$i])) {
                Util\JpGraphError::RaiseL(2014);
            }
            if ($this->plots[0]->coords[0][$i] > 0) {
                $y = $this->plots[0]->coords[0][$i];
            }

            for ($j = 1; $j < $this->nbrplots; $j++) {
                if (!isset($this->plots[$j]->coords[0][$i])) {
                    Util\JpGraphError::RaiseL(2014);
                }
                if ($this->plots[$j]->coords[0][$i] > 0) {
                    $y += $this->plots[$j]->coords[0][$i];
                }
            }
            $ymax[$i] = $y;
        }
        $ymax = max($ymax);

        // Bar always start at baseline
        if ($ymax <= $this->ybase) {
            $ymax = $this->ybase;
        }

        return [$xmax, $ymax];
    }

    public function Min()
    {
        $nmax                 = 0;
        list($xmin, $ysetmin) = $this->plots[0]->Min();
        for ($i = 0; $i < count($this->plots); ++$i) {
            $n           = count($this->plots[$i]->coords[0]);
            $nmax        = max($nmax, $n);
            list($x, $y) = $this->plots[$i]->Min();
            $xmin        = Min($xmin, $x);
            $ysetmin     = Min($y, $ysetmin);
        }
        for ($i = 0; $i < $nmax; $i++) {
            // Get y-value for bar $i by adding the
            // individual bars from all the plots added.
            // It would be wrong to just add the
            // individual plots max y-value since that
            // would in most cases give to large y-value.
            $y = 0;
            if ($this->plots[0]->coords[0][$i] < 0) {
                $y = $this->plots[0]->coords[0][$i];
            }

            for ($j = 1; $j < $this->nbrplots; $j++) {
                if ($this->plots[$j]->coords[0][$i] < 0) {
                    $y += $this->plots[$j]->coords[0][$i];
                }
            }
            $ymin[$i] = $y;
        }
        $ymin = Min($ysetmin, Min($ymin));
        // Bar always start at baseline
        if ($ymin >= $this->ybase) {
            $ymin = $this->ybase;
        }

        return [$xmin, $ymin];
    }

    // Stroke acc bar plot
    public function Stroke($img, $xscale, $yscale)
    {
        $pattern = null;
        $img->SetLineWeight($this->weight);
        $grad = null;
        for ($i = 0; $i < $this->numpoints - 1; $i++) {
            $accy     = 0;
            $accy_neg = 0;
            for ($j = 0; $j < $this->nbrplots; ++$j) {
                $img->SetColor($this->plots[$j]->color);

                if ($this->plots[$j]->coords[0][$i] >= 0) {
                    $yt    = $yscale->Translate($this->plots[$j]->coords[0][$i] + $accy);
                    $accyt = $yscale->Translate($accy);
                    $accy += $this->plots[$j]->coords[0][$i];
                } else {
                    //if ( $this->plots[$j]->coords[0][$i] < 0 || $accy_neg < 0 ) {
                    $yt    = $yscale->Translate($this->plots[$j]->coords[0][$i] + $accy_neg);
                    $accyt = $yscale->Translate($accy_neg);
                    $accy_neg += $this->plots[$j]->coords[0][$i];
                }

                $xt = $xscale->Translate($i);

                if ($this->abswidth > -1) {
                    $abswidth = $this->abswidth;
                } else {
                    $abswidth = round($this->width * $xscale->scale_factor, 0);
                }

                $pts = [$xt, $accyt, $xt, $yt, $xt + $abswidth, $yt, $xt + $abswidth, $accyt];

                if ($this->bar_shadow) {
                    $ssh = $this->bar_shadow_hsize;
                    $ssv = $this->bar_shadow_vsize;

                    // We must also differ if we are a positive or negative bar.
                    if ($j === 0) {
                        // This gets extra complicated since we have to
                        // see all plots to see if we are negative. It could
                        // for example be that all plots are 0 until the very
                        // last one. We therefore need to save the initial setup
                        // for both the negative and positive case

                        // In case the final bar is positive
                        $sp[0] = $pts[6] + 1;
                        $sp[1] = $pts[7];
                        $sp[2] = $pts[6] + $ssh;
                        $sp[3] = $pts[7] - $ssv;

                        // In case the final bar is negative
                        $nsp[0]  = $pts[0];
                        $nsp[1]  = $pts[1];
                        $nsp[2]  = $pts[0] + $ssh;
                        $nsp[3]  = $pts[1] - $ssv;
                        $nsp[4]  = $pts[6] + $ssh;
                        $nsp[5]  = $pts[7] - $ssv;
                        $nsp[10] = $pts[6] + 1;
                        $nsp[11] = $pts[7];
                    }

                    if ($j === $this->nbrplots - 1) {
                        // If this is the last plot of the bar and
                        // the total value is larger than 0 then we
                        // add the shadow.
                        if (is_array($this->bar_shadow_color)) {
                            $numcolors = count($this->bar_shadow_color);
                            if ($numcolors == 0) {
                                Util\JpGraphError::RaiseL(2013); //('You have specified an empty array for shadow colors in the bar plot.');
                            }
                            $img->PushColor($this->bar_shadow_color[$i % $numcolors]);
                        } else {
                            $img->PushColor($this->bar_shadow_color);
                        }

                        if ($accy > 0) {
                            $sp[4]  = $pts[4] + $ssh;
                            $sp[5]  = $pts[5] - $ssv;
                            $sp[6]  = $pts[2] + $ssh;
                            $sp[7]  = $pts[3] - $ssv;
                            $sp[8]  = $pts[2];
                            $sp[9]  = $pts[3] - 1;
                            $sp[10] = $pts[4] + 1;
                            $sp[11] = $pts[5];
                            $img->FilledPolygon($sp, 4);
                        } elseif ($accy_neg < 0) {
                            $nsp[6] = $pts[4] + $ssh;
                            $nsp[7] = $pts[5] - $ssv;
                            $nsp[8] = $pts[4] + 1;
                            $nsp[9] = $pts[5];
                            $img->FilledPolygon($nsp, 4);
                        }
                        $img->PopColor();
                    }
                }

                // If value is NULL or 0, then don't draw a bar at all
                if ($this->plots[$j]->coords[0][$i] == 0) {
                    continue;
                }

                if ($this->plots[$j]->grad) {
                    if ($grad === null) {
                        $grad = new Gradient($img);
                    }
                    if (is_array($this->plots[$j]->grad_fromcolor)) {
                        // The first argument (grad_fromcolor) can be either an array or a single color. If it is an array
                        // then we have two choices. It can either a) be a single color specified as an RGB triple or it can be
                        // an array to specify both (from, to style) for each individual bar. The way to know the difference is
                        // to investgate the first element. If this element is an integer [0,255] then we assume it is an RGB
                        // triple.
                        $ng = count($this->plots[$j]->grad_fromcolor);
                        if ($ng === 3) {
                            if (is_numeric($this->plots[$j]->grad_fromcolor[0]) && $this->plots[$j]->grad_fromcolor[0] > 0 &&
                                $this->plots[$j]->grad_fromcolor[0] < 256) {
                                // RGB Triple
                                $fromcolor = $this->plots[$j]->grad_fromcolor;
                                $tocolor   = $this->plots[$j]->grad_tocolor;
                                $style     = $this->plots[$j]->grad_style;
                            } else {
                                $fromcolor = $this->plots[$j]->grad_fromcolor[$i % $ng][0];
                                $tocolor   = $this->plots[$j]->grad_fromcolor[$i % $ng][1];
                                $style     = $this->plots[$j]->grad_fromcolor[$i % $ng][2];
                            }
                        } else {
                            $fromcolor = $this->plots[$j]->grad_fromcolor[$i % $ng][0];
                            $tocolor   = $this->plots[$j]->grad_fromcolor[$i % $ng][1];
                            $style     = $this->plots[$j]->grad_fromcolor[$i % $ng][2];
                        }
                        $grad->FilledRectangle($pts[2], $pts[3],
                            $pts[6], $pts[7],
                            $fromcolor, $tocolor, $style);
                    } else {
                        $grad->FilledRectangle($pts[2], $pts[3],
                            $pts[6], $pts[7],
                            $this->plots[$j]->grad_fromcolor,
                            $this->plots[$j]->grad_tocolor,
                            $this->plots[$j]->grad_style);
                    }
                } else {
                    if (is_array($this->plots[$j]->fill_color)) {
                        $numcolors = count($this->plots[$j]->fill_color);
                        $fillcolor = $this->plots[$j]->fill_color[$i % $numcolors];
                        // If the bar is specified to be non filled then the fill color is false
                        if ($fillcolor !== false) {
                            $img->SetColor($this->plots[$j]->fill_color[$i % $numcolors]);
                        }
                    } else {
                        $fillcolor = $this->plots[$j]->fill_color;
                        if ($fillcolor !== false) {
                            $img->SetColor($this->plots[$j]->fill_color);
                        }
                    }
                    if ($fillcolor !== false) {
                        $img->FilledPolygon($pts);
                    }
                }

                $img->SetColor($this->plots[$j]->color);

                // Stroke the pattern
                if ($this->plots[$j]->iPattern > -1) {
                    if ($pattern === null) {
                        $pattern = new RectPatternFactory();
                    }

                    $prect = $pattern->Create($this->plots[$j]->iPattern, $this->plots[$j]->iPatternColor, 1);
                    $prect->SetDensity($this->plots[$j]->iPatternDensity);
                    if ($this->plots[$j]->coords[0][$i] < 0) {
                        $rx = $pts[0];
                        $ry = $pts[1];
                    } else {
                        $rx = $pts[2];
                        $ry = $pts[3];
                    }
                    $width  = abs($pts[4] - $pts[0]) + 1;
                    $height = abs($pts[1] - $pts[3]) + 1;
                    $prect->SetPos(new Util\Rectangle($rx, $ry, $width, $height));
                    $prect->Stroke($img);
                }

                // CSIM array

                if ($i < count($this->plots[$j]->csimtargets)) {
                    // Create the client side image map
                    $rpts      = $img->ArrRotate($pts);
                    $csimcoord = round($rpts[0]) . ", " . round($rpts[1]);
                    for ($k = 1; $k < 4; ++$k) {
                        $csimcoord .= ", " . round($rpts[2 * $k]) . ", " . round($rpts[2 * $k + 1]);
                    }
                    if (!empty($this->plots[$j]->csimtargets[$i])) {
                        $this->csimareas .= '<area shape="poly" coords="' . $csimcoord . '" ';
                        $this->csimareas .= " href=\"" . $this->plots[$j]->csimtargets[$i] . "\" ";

                        if (!empty($this->plots[$j]->csimwintargets[$i])) {
                            $this->csimareas .= " target=\"" . $this->plots[$j]->csimwintargets[$i] . "\" ";
                        }

                        $sval = '';
                        if (!empty($this->plots[$j]->csimalts[$i])) {
                            $sval = sprintf($this->plots[$j]->csimalts[$i], $this->plots[$j]->coords[0][$i]);
                            $this->csimareas .= " title=\"$sval\" ";
                        }
                        $this->csimareas .= " alt=\"$sval\" />\n";
                    }
                }

                $pts[] = $pts[0];
                $pts[] = $pts[1];
                $img->SetLineWeight($this->plots[$j]->weight);
                $img->Polygon($pts);
                $img->SetLineWeight(1);
            }

            // Daw potential bar around the entire accbar bar
            if ($this->weight > 0) {
                $y = $yscale->Translate(0);
                $img->SetColor($this->color);
                $img->SetLineWeight($this->weight);
                $img->Rectangle($pts[0], $y, $pts[6], $pts[5]);
            }

            // Draw labels for each acc.bar

            $x = $pts[2] + ($pts[4] - $pts[2]) / 2;
            if ($this->bar_shadow) {
                $x += $ssh;
            }

            // First stroke the accumulated value for the entire bar
            // This value is always placed at the top/bottom of the bars
            if ($accy_neg < 0) {
                $y = $yscale->Translate($accy_neg);
                $this->value->Stroke($img, $accy_neg, $x, $y);
            } else {
                $y = $yscale->Translate($accy);
                $this->value->Stroke($img, $accy, $x, $y);
            }

            $accy     = 0;
            $accy_neg = 0;
            for ($j = 0; $j < $this->nbrplots; ++$j) {

                // We don't print 0 values in an accumulated bar plot
                if ($this->plots[$j]->coords[0][$i] == 0) {
                    continue;
                }

                if ($this->plots[$j]->coords[0][$i] > 0) {
                    $yt    = $yscale->Translate($this->plots[$j]->coords[0][$i] + $accy);
                    $accyt = $yscale->Translate($accy);
                    if ($this->plots[$j]->valuepos == 'center') {
                        $y = $accyt - ($accyt - $yt) / 2;
                    } elseif ($this->plots[$j]->valuepos == 'bottom') {
                        $y = $accyt;
                    } else {
                        // top or max
                        $y = $accyt - ($accyt - $yt);
                    }
                    $accy += $this->plots[$j]->coords[0][$i];
                    if ($this->plots[$j]->valuepos == 'center') {
                        $this->plots[$j]->value->SetAlign("center", "center");
                        $this->plots[$j]->value->SetMargin(0);
                    } elseif ($this->plots[$j]->valuepos == 'bottom') {
                        $this->plots[$j]->value->SetAlign('center', 'bottom');
                        $this->plots[$j]->value->SetMargin(2);
                    } else {
                        $this->plots[$j]->value->SetAlign('center', 'top');
                        $this->plots[$j]->value->SetMargin(1);
                    }
                } else {
                    $yt    = $yscale->Translate($this->plots[$j]->coords[0][$i] + $accy_neg);
                    $accyt = $yscale->Translate($accy_neg);
                    $accy_neg += $this->plots[$j]->coords[0][$i];
                    if ($this->plots[$j]->valuepos == 'center') {
                        $y = $accyt - ($accyt - $yt) / 2;
                    } elseif ($this->plots[$j]->valuepos == 'bottom') {
                        $y = $accyt;
                    } else {
                        $y = $accyt - ($accyt - $yt);
                    }
                    if ($this->plots[$j]->valuepos == 'center') {
                        $this->plots[$j]->value->SetAlign("center", "center");
                        $this->plots[$j]->value->SetMargin(0);
                    } elseif ($this->plots[$j]->valuepos == 'bottom') {
                        $this->plots[$j]->value->SetAlign('center', $j == 0 ? 'bottom' : 'top');
                        $this->plots[$j]->value->SetMargin(-2);
                    } else {
                        $this->plots[$j]->value->SetAlign('center', 'bottom');
                        $this->plots[$j]->value->SetMargin(-1);
                    }
                }
                $this->plots[$j]->value->Stroke($img, $this->plots[$j]->coords[0][$i], $x, $y);
            }
        }
        return true;
    }
} // Class
