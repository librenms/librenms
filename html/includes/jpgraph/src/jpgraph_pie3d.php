<?php
/*=======================================================================
 // File:        JPGRAPH_PIE3D.PHP
 // Description: 3D Pie plot extension for JpGraph
 // Created:     2001-03-24
 // Ver:         $Id: jpgraph_pie3d.php 1329 2009-06-20 19:23:30Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

//===================================================
// CLASS PiePlot3D
// Description: Plots a 3D pie with a specified projection
// angle between 20 and 70 degrees.
//===================================================
class PiePlot3D extends PiePlot {
    private $labelhintcolor="red",$showlabelhint=true;
    private $angle=50;
    private $edgecolor="", $edgeweight=1;
    private $iThickness=false;

    //---------------
    // CONSTRUCTOR
    function __construct($data) {
        $this->radius = 0.5;
        $this->data = $data;
        $this->title = new Text("");
        $this->title->SetFont(FF_FONT1,FS_BOLD);
        $this->value = new DisplayValue();
        $this->value->Show();
        $this->value->SetFormat('%.0f%%');
    }

    //---------------
    // PUBLIC METHODS

    // Set label arrays
    function SetLegends($aLegend) {
        $this->legends = array_reverse(array_slice($aLegend,0,count($this->data)));
    }

    function SetSliceColors($aColors) {
        $this->setslicecolors = $aColors;
    }

    function Legend($aGraph) {
        parent::Legend($aGraph);
        $aGraph->legend->txtcol = array_reverse($aGraph->legend->txtcol);
    }

    function SetCSIMTargets($aTargets,$aAlts='',$aWinTargets='') {
        $this->csimtargets = $aTargets;
        $this->csimwintargets = $aWinTargets;
        $this->csimalts = $aAlts;
    }

    // Should the slices be separated by a line? If color is specified as "" no line
    // will be used to separate pie slices.
    function SetEdge($aColor='black',$aWeight=1) {
        $this->edgecolor = $aColor;
        $this->edgeweight = $aWeight;
    }

    // Specify projection angle for 3D in degrees
    // Must be between 20 and 70 degrees
    function SetAngle($a) {
        if( $a<5 || $a>90 ) {
            JpGraphError::RaiseL(14002);
            //("PiePlot3D::SetAngle() 3D Pie projection angle must be between 5 and 85 degrees.");
        }
        else {
            $this->angle = $a;
        }
    }

    function Add3DSliceToCSIM($i,$xc,$yc,$height,$width,$thick,$sa,$ea) {  //Slice number, ellipse centre (x,y), height, width, start angle, end angle

        $sa *= M_PI/180;
        $ea *= M_PI/180;

        //add coordinates of the centre to the map
        $coords = "$xc, $yc";

        //add coordinates of the first point on the arc to the map
        $xp = floor($width*cos($sa)/2+$xc);
        $yp = floor($yc-$height*sin($sa)/2);
        $coords.= ", $xp, $yp";

        //If on the front half, add the thickness offset
        if ($sa >= M_PI && $sa <= 2*M_PI*1.01) {
            $yp = floor($yp+$thick);
            $coords.= ", $xp, $yp";
        }

        //add coordinates every 0.2 radians
        $a=$sa+0.2;
        while ($a<$ea) {
            $xp = floor($width*cos($a)/2+$xc);
            if ($a >= M_PI && $a <= 2*M_PI*1.01) {
                $yp = floor($yc-($height*sin($a)/2)+$thick);
            } else {
                $yp = floor($yc-$height*sin($a)/2);
            }
            $coords.= ", $xp, $yp";
            $a += 0.2;
        }

        //Add the last point on the arc
        $xp = floor($width*cos($ea)/2+$xc);
        $yp = floor($yc-$height*sin($ea)/2);


        if ($ea >= M_PI && $ea <= 2*M_PI*1.01) {
            $coords.= ", $xp, ".floor($yp+$thick);
        }
        $coords.= ", $xp, $yp";
        $alt='';

        if( !empty($this->csimtargets[$i]) ) {
            $this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->csimtargets[$i]."\"";

            if( !empty($this->csimwintargets[$i]) ) {
                $this->csimareas .= " target=\"".$this->csimwintargets[$i]."\" ";
            }
             
            if( !empty($this->csimalts[$i]) ) {
                $tmp=sprintf($this->csimalts[$i],$this->data[$i]);
                $this->csimareas .= "alt=\"$tmp\" title=\"$tmp\" ";
            }
            $this->csimareas .=  " />\n";
        }

    }

    function SetLabels($aLabels,$aLblPosAdj="auto") {
        $this->labels = $aLabels;
        $this->ilabelposadj=$aLblPosAdj;
    }


    // Distance from the pie to the labels
    function SetLabelMargin($m) {
        $this->value->SetMargin($m);
    }

    // Show a thin line from the pie to the label for a specific slice
    function ShowLabelHint($f=true) {
        $this->showlabelhint=$f;
    }

    // Set color of hint line to label for each slice
    function SetLabelHintColor($c) {
        $this->labelhintcolor=$c;
    }

    function SetHeight($aHeight) {
        $this->iThickness = $aHeight;
    }


    // Normalize Angle between 0-360
    function NormAngle($a) {
        // Normalize anle to 0 to 2M_PI
        //
        if( $a > 0 ) {
            while($a > 360) $a -= 360;
        }
        else {
            while($a < 0) $a += 360;
        }
        if( $a < 0 )
        $a = 360 + $a;

        if( $a == 360 ) $a=0;
        return $a;
    }



    // Draw one 3D pie slice at position ($xc,$yc) with height $z
    function Pie3DSlice($img,$xc,$yc,$w,$h,$sa,$ea,$z,$fillcolor,$shadow=0.65) {

        // Due to the way the 3D Pie algorithm works we are
        // guaranteed that any slice we get into this method
        // belongs to either the left or right side of the
        // pie ellipse. Hence, no slice will cross 90 or 270
        // point.
        if( ($sa < 90 && $ea > 90) || ( ($sa > 90 && $sa < 270) && $ea > 270) ) {
            JpGraphError::RaiseL(14003);//('Internal assertion failed. Pie3D::Pie3DSlice');
            exit(1);
        }

        $p[] = array();

        // Setup pre-calculated values
        $rsa = $sa/180*M_PI; // to Rad
        $rea = $ea/180*M_PI; // to Rad
        $sinsa = sin($rsa);
        $cossa = cos($rsa);
        $sinea = sin($rea);
        $cosea = cos($rea);

        // p[] is the points for the overall slice and
        // pt[] is the points for the top pie

        // Angular step when approximating the arc with a polygon train.
        $step = 0.05;

        if( $sa >= 270 ) {
            if( $ea > 360 || ($ea > 0 && $ea <= 90) ) {
                if( $ea > 0 && $ea <= 90 ) {
                    // Adjust angle to simplify conditions in loops
                    $rea += 2*M_PI;
                }

                $p = array($xc,$yc,$xc,$yc+$z,
                $xc+$w*$cossa,$z+$yc-$h*$sinsa);
                $pt = array($xc,$yc,$xc+$w*$cossa,$yc-$h*$sinsa);

                for( $a=$rsa; $a < 2*M_PI; $a += $step ) {
                    $tca = cos($a);
                    $tsa = sin($a);
                    $p[] = $xc+$w*$tca;
                    $p[] = $z+$yc-$h*$tsa;
                    $pt[] = $xc+$w*$tca;
                    $pt[] = $yc-$h*$tsa;
                }

                $pt[] = $xc+$w;
                $pt[] = $yc;

                $p[] = $xc+$w;
                $p[] = $z+$yc;
                $p[] = $xc+$w;
                $p[] = $yc;
                $p[] = $xc;
                $p[] = $yc;

                for( $a=2*M_PI+$step; $a < $rea; $a += $step ) {
                    $pt[] = $xc + $w*cos($a);
                    $pt[] = $yc - $h*sin($a);
                }

                $pt[] = $xc+$w*$cosea;
                $pt[] = $yc-$h*$sinea;
                $pt[] = $xc;
                $pt[] = $yc;

            }
            else {
                $p = array($xc,$yc,$xc,$yc+$z,
                $xc+$w*$cossa,$z+$yc-$h*$sinsa);
                $pt = array($xc,$yc,$xc+$w*$cossa,$yc-$h*$sinsa);

                $rea = $rea == 0.0 ? 2*M_PI : $rea;
                for( $a=$rsa; $a < $rea; $a += $step ) {
                    $tca = cos($a);
                    $tsa = sin($a);
                    $p[] = $xc+$w*$tca;
                    $p[] = $z+$yc-$h*$tsa;
                    $pt[] = $xc+$w*$tca;
                    $pt[] = $yc-$h*$tsa;
                }

                $pt[] = $xc+$w*$cosea;
                $pt[] = $yc-$h*$sinea;
                $pt[] = $xc;
                $pt[] = $yc;

                $p[] = $xc+$w*$cosea;
                $p[] = $z+$yc-$h*$sinea;
                $p[] = $xc+$w*$cosea;
                $p[] = $yc-$h*$sinea;
                $p[] = $xc;
                $p[] = $yc;
            }
        }
        elseif( $sa >= 180 ) {
            $p = array($xc,$yc,$xc,$yc+$z,$xc+$w*$cosea,$z+$yc-$h*$sinea);
            $pt = array($xc,$yc,$xc+$w*$cosea,$yc-$h*$sinea);

            for( $a=$rea; $a>$rsa; $a -= $step ) {
                $tca = cos($a);
                $tsa = sin($a);
                $p[] = $xc+$w*$tca;
                $p[] = $z+$yc-$h*$tsa;
                $pt[] = $xc+$w*$tca;
                $pt[] = $yc-$h*$tsa;
            }

            $pt[] = $xc+$w*$cossa;
            $pt[] = $yc-$h*$sinsa;
            $pt[] = $xc;
            $pt[] = $yc;

            $p[] = $xc+$w*$cossa;
            $p[] = $z+$yc-$h*$sinsa;
            $p[] = $xc+$w*$cossa;
            $p[] = $yc-$h*$sinsa;
            $p[] = $xc;
            $p[] = $yc;

        }
        elseif( $sa >= 90 ) {
            if( $ea > 180 ) {
                $p = array($xc,$yc,$xc,$yc+$z,$xc+$w*$cosea,$z+$yc-$h*$sinea);
                $pt = array($xc,$yc,$xc+$w*$cosea,$yc-$h*$sinea);

                for( $a=$rea; $a > M_PI; $a -= $step ) {
                    $tca = cos($a);
                    $tsa = sin($a);
                    $p[] = $xc+$w*$tca;
                    $p[] = $z + $yc - $h*$tsa;
                    $pt[] = $xc+$w*$tca;
                    $pt[] = $yc-$h*$tsa;
                }

                $p[] = $xc-$w;
                $p[] = $z+$yc;
                $p[] = $xc-$w;
                $p[] = $yc;
                $p[] = $xc;
                $p[] = $yc;

                $pt[] = $xc-$w;
                $pt[] = $z+$yc;
                $pt[] = $xc-$w;
                $pt[] = $yc;

                for( $a=M_PI-$step; $a > $rsa; $a -= $step ) {
                    $pt[] = $xc + $w*cos($a);
                    $pt[] = $yc - $h*sin($a);
                }

                $pt[] = $xc+$w*$cossa;
                $pt[] = $yc-$h*$sinsa;
                $pt[] = $xc;
                $pt[] = $yc;

            }
            else { // $sa >= 90 && $ea <= 180
                $p = array($xc,$yc,$xc,$yc+$z,
                $xc+$w*$cosea,$z+$yc-$h*$sinea,
                $xc+$w*$cosea,$yc-$h*$sinea,
                $xc,$yc);

                $pt = array($xc,$yc,$xc+$w*$cosea,$yc-$h*$sinea);

                for( $a=$rea; $a>$rsa; $a -= $step ) {
                    $pt[] = $xc + $w*cos($a);
                    $pt[] = $yc - $h*sin($a);
                }

                $pt[] = $xc+$w*$cossa;
                $pt[] = $yc-$h*$sinsa;
                $pt[] = $xc;
                $pt[] = $yc;

            }
        }
        else { // sa > 0 && ea < 90

            $p = array($xc,$yc,$xc,$yc+$z,
            $xc+$w*$cossa,$z+$yc-$h*$sinsa,
            $xc+$w*$cossa,$yc-$h*$sinsa,
            $xc,$yc);

            $pt = array($xc,$yc,$xc+$w*$cossa,$yc-$h*$sinsa);

            for( $a=$rsa; $a < $rea; $a += $step ) {
                $pt[] = $xc + $w*cos($a);
                $pt[] = $yc - $h*sin($a);
            }

            $pt[] = $xc+$w*$cosea;
            $pt[] = $yc-$h*$sinea;
            $pt[] = $xc;
            $pt[] = $yc;
        }
         
        $img->PushColor($fillcolor.":".$shadow);
        $img->FilledPolygon($p);
        $img->PopColor();

        $img->PushColor($fillcolor);
        $img->FilledPolygon($pt);
        $img->PopColor();
    }

    function SetStartAngle($aStart) {
        if( $aStart < 0 || $aStart > 360 ) {
            JpGraphError::RaiseL(14004);//('Slice start angle must be between 0 and 360 degrees.');
        }
        $this->startangle = $aStart;
    }

    // Draw a 3D Pie
    function Pie3D($aaoption,$img,$data,$colors,$xc,$yc,$d,$angle,$z,
                   $shadow=0.65,$startangle=0,$edgecolor="",$edgeweight=1) {

        //---------------------------------------------------------------------------
        // As usual the algorithm get more complicated than I originally
        // envisioned. I believe that this is as simple as it is possible
        // to do it with the features I want. It's a good exercise to start
        // thinking on how to do this to convince your self that all this
        // is really needed for the general case.
        //
        // The algorithm two draw 3D pies without "real 3D" is done in
        // two steps.
        // First imagine the pie cut in half through a thought line between
        // 12'a clock and 6'a clock. It now easy to imagine that we can plot
        // the individual slices for each half by starting with the topmost
        // pie slice and continue down to 6'a clock.
        //
        // In the algortithm this is done in three principal steps
        // Step 1. Do the knife cut to ensure by splitting slices that extends
        // over the cut line. This is done by splitting the original slices into
        // upto 3 subslices.
        // Step 2. Find the top slice for each half
        // Step 3. Draw the slices from top to bottom
        //
        // The thing that slightly complicates this scheme with all the
        // angle comparisons below is that we can have an arbitrary start
        // angle so we must take into account the different equivalence classes.
        // For the same reason we must walk through the angle array in a
        // modulo fashion.
        //
        // Limitations of algorithm:
        // * A small exploded slice which crosses the 270 degree point
        //   will get slightly nagged close to the center due to the fact that
        //   we print the slices in Z-order and that the slice left part
        //   get printed first and might get slightly nagged by a larger
        //   slice on the right side just before the right part of the small
        //   slice. Not a major problem though.
        //---------------------------------------------------------------------------


        // Determine the height of the ellippse which gives an
        // indication of the inclination angle
        $h = ($angle/90.0)*$d;
        $sum = 0;
        for($i=0; $i<count($data); ++$i ) {
            $sum += $data[$i];
        }

        // Special optimization
        if( $sum==0 ) return;

        if( $this->labeltype == 2 ) {
            $this->adjusted_data = $this->AdjPercentage($data);
        }

        // Setup the start
        $accsum = 0;
        $a = $startangle;
        $a = $this->NormAngle($a);

        //
        // Step 1 . Split all slices that crosses 90 or 270
        //
        $idx=0;
        $adjexplode=array();
        $numcolors = count($colors);
        for($i=0; $i<count($data); ++$i, ++$idx ) {
            $da = $data[$i]/$sum * 360;

            if( empty($this->explode_radius[$i]) ) {
                $this->explode_radius[$i]=0;
            }

            $expscale=1;
            if( $aaoption == 1 ) {
                $expscale=2;
            }

            $la = $a + $da/2;
            $explode = array( $xc + $this->explode_radius[$i]*cos($la*M_PI/180)*$expscale,
            $yc - $this->explode_radius[$i]*sin($la*M_PI/180) * ($h/$d) *$expscale );
            $adjexplode[$idx] = $explode;
            $labeldata[$i] = array($la,$explode[0],$explode[1]);
            $originalangles[$i] = array($a,$a+$da);

            $ne = $this->NormAngle($a+$da);
            if( $da <= 180 ) {
                // If the slice size is <= 90 it can at maximum cut across
                // one boundary (either 90 or 270) where it needs to be split
                $split=-1; // no split
                if( ($da<=90 && ($a <= 90 && $ne > 90)) ||
                (($da <= 180 && $da >90)  && (($a < 90 || $a >= 270) && $ne > 90)) ) {
                    $split = 90;
                }
                elseif( ($da<=90 && ($a <= 270 && $ne > 270)) ||
                (($da<=180 && $da>90) && ($a >= 90 && $a < 270 && ($a+$da) > 270 )) ) {
                    $split = 270;
                }
                if( $split > 0 ) { // split in two
                    $angles[$idx] = array($a,$split);
                    $adjcolors[$idx] = $colors[$i % $numcolors];
                    $adjexplode[$idx] = $explode;
                    $angles[++$idx] = array($split,$ne);
                    $adjcolors[$idx] = $colors[$i % $numcolors];
                    $adjexplode[$idx] = $explode;
                }
                else { // no split
                    $angles[$idx] = array($a,$ne);
                    $adjcolors[$idx] = $colors[$i  % $numcolors];
                    $adjexplode[$idx] = $explode;
                }
            }
            else {
                // da>180
                // Slice may, depending on position, cross one or two
                // bonudaries

                if( $a < 90 )        $split = 90;
                elseif( $a <= 270 )  $split = 270;
                else                 $split = 90;

                $angles[$idx] = array($a,$split);
                $adjcolors[$idx] = $colors[$i % $numcolors];
                $adjexplode[$idx] = $explode;
                //if( $a+$da > 360-$split ) {
                // For slices larger than 270 degrees we might cross
                // another boundary as well. This means that we must
                // split the slice further. The comparison gets a little
                // bit complicated since we must take into accound that
                // a pie might have a startangle >0 and hence a slice might
                // wrap around the 0 angle.
                // Three cases:
                //  a) Slice starts before 90 and hence gets a split=90, but
                //     we must also check if we need to split at 270
                //  b) Slice starts after 90 but before 270 and slices
                //     crosses 90 (after a wrap around of 0)
                //  c) If start is > 270 (hence the firstr split is at 90)
                //     and the slice is so large that it goes all the way
                //     around 270.
                if( ($a < 90 && ($a+$da > 270)) || ($a > 90 && $a<=270 && ($a+$da>360+90) ) || ($a > 270 && $this->NormAngle($a+$da)>270) ) {
                    $angles[++$idx] = array($split,360-$split);
                    $adjcolors[$idx] = $colors[$i % $numcolors];
                    $adjexplode[$idx] = $explode;
                    $angles[++$idx] = array(360-$split,$ne);
                    $adjcolors[$idx] = $colors[$i % $numcolors];
                    $adjexplode[$idx] = $explode;
                }
                else {
                    // Just a simple split to the previous decided
                    // angle.
                    $angles[++$idx] = array($split,$ne);
                    $adjcolors[$idx] = $colors[$i % $numcolors];
                    $adjexplode[$idx] = $explode;
                }
            }
            $a += $da;
            $a = $this->NormAngle($a);
        }

        // Total number of slices
        $n = count($angles);

        for($i=0; $i<$n; ++$i) {
            list($dbgs,$dbge) = $angles[$i];
        }

        //
        // Step 2. Find start index (first pie that starts in upper left quadrant)
        //
        $minval = $angles[0][0];
        $min = 0;
        for( $i=0; $i<$n; ++$i ) {
            if( $angles[$i][0] < $minval ) {
                $minval = $angles[$i][0];
                $min = $i;
            }
        }
        $j = $min;
        $cnt = 0;
        while( $angles[$j][1] <= 90 ) {
            $j++;
            if( $j>=$n) {
                $j=0;
            }
            if( $cnt > $n ) {
                JpGraphError::RaiseL(14005);
                //("Pie3D Internal error (#1). Trying to wrap twice when looking for start index");
            }
            ++$cnt;
        }
        $start = $j;

        //
        // Step 3. Print slices in z-order
        //
        $cnt = 0;

        // First stroke all the slices between 90 and 270 (left half circle)
        // counterclockwise
         
        while( $angles[$j][0] < 270  && $aaoption !== 2 ) {

            list($x,$y) = $adjexplode[$j];

            $this->Pie3DSlice($img,$x,$y,$d,$h,$angles[$j][0],$angles[$j][1],
            $z,$adjcolors[$j],$shadow);

            $last = array($x,$y,$j);

            $j++;
            if( $j >= $n ) $j=0;
            if( $cnt > $n ) {
                JpGraphError::RaiseL(14006);
                //("Pie3D Internal Error: Z-Sorting algorithm for 3D Pies is not working properly (2). Trying to wrap twice while stroking.");
            }
            ++$cnt;
        }
         
        $slice_left = $n-$cnt;
        $j=$start-1;
        if($j<0) $j=$n-1;
        $cnt = 0;

        // The stroke all slices from 90 to -90 (right half circle)
        // clockwise
        while( $cnt < $slice_left  && $aaoption !== 2 ) {

            list($x,$y) = $adjexplode[$j];

            $this->Pie3DSlice($img,$x,$y,$d,$h,$angles[$j][0],$angles[$j][1],
            $z,$adjcolors[$j],$shadow);
            $j--;
            if( $cnt > $n ) {
                JpGraphError::RaiseL(14006);
                //("Pie3D Internal Error: Z-Sorting algorithm for 3D Pies is not working properly (2). Trying to wrap twice while stroking.");
            }
            if($j<0) $j=$n-1;
            $cnt++;
        }

        // Now do a special thing. Stroke the last slice on the left
        // halfcircle one more time.  This is needed in the case where
        // the slice close to 270 have been exploded. In that case the
        // part of the slice close to the center of the pie might be
        // slightly nagged.
        if( $aaoption !== 2 )
        $this->Pie3DSlice($img,$last[0],$last[1],$d,$h,$angles[$last[2]][0],
        $angles[$last[2]][1],$z,$adjcolors[$last[2]],$shadow);


        if( $aaoption !== 1 ) {
            // Now print possible labels and add csim
            $this->value->ApplyFont($img);
            $margin = $img->GetFontHeight()/2 + $this->value->margin ;
            for($i=0; $i < count($data); ++$i ) {
                $la = $labeldata[$i][0];
                $x = $labeldata[$i][1] + cos($la*M_PI/180)*($d+$margin)*$this->ilabelposadj;
                $y = $labeldata[$i][2] - sin($la*M_PI/180)*($h+$margin)*$this->ilabelposadj;
                if( $this->ilabelposadj >= 1.0 ) {
                    if( $la > 180 && $la < 360 ) $y += $z;
                }
                if( $this->labeltype == 0 ) {
                    if( $sum > 0 ) $l = 100*$data[$i]/$sum;
                    else $l = 0;
                }
                elseif( $this->labeltype == 1 ) {
                    $l = $data[$i];
                }
                else {
                    $l = $this->adjusted_data[$i];
                }
                if( isset($this->labels[$i]) && is_string($this->labels[$i]) ) {
                    $l=sprintf($this->labels[$i],$l);
                }

                $this->StrokeLabels($l,$img,$labeldata[$i][0]*M_PI/180,$x,$y,$z);
                 
                $this->Add3DSliceToCSIM($i,$labeldata[$i][1],$labeldata[$i][2],$h*2,$d*2,$z,
                $originalangles[$i][0],$originalangles[$i][1]);
            }
        }

        //
        // Finally add potential lines in pie
        //

        if( $edgecolor=="" || $aaoption !== 0 ) return;

        $accsum = 0;
        $a = $startangle;
        $a = $this->NormAngle($a);

        $a *= M_PI/180.0;

        $idx=0;
        $img->PushColor($edgecolor);
        $img->SetLineWeight($edgeweight);

        $fulledge = true;
        for($i=0; $i < count($data) && $fulledge; ++$i ) {
            if( empty($this->explode_radius[$i]) ) {
                $this->explode_radius[$i]=0;
            }
            if( $this->explode_radius[$i] > 0 ) {
                $fulledge = false;
            }
        }
         

        for($i=0; $i < count($data); ++$i, ++$idx ) {

            $da = $data[$i]/$sum * 2*M_PI;
            $this->StrokeFullSliceFrame($img,$xc,$yc,$a,$a+$da,$d,$h,$z,$edgecolor,
            $this->explode_radius[$i],$fulledge);
            $a += $da;
        }
        $img->PopColor();
    }

    function StrokeFullSliceFrame($img,$xc,$yc,$sa,$ea,$w,$h,$z,$edgecolor,$exploderadius,$fulledge) {
        $step = 0.02;

        if( $exploderadius > 0 ) {
            $la = ($sa+$ea)/2;
            $xc += $exploderadius*cos($la);
            $yc -= $exploderadius*sin($la) * ($h/$w) ;
             
        }

        $p = array($xc,$yc,$xc+$w*cos($sa),$yc-$h*sin($sa));

        for($a=$sa; $a < $ea; $a += $step ) {
            $p[] = $xc + $w*cos($a);
            $p[] = $yc - $h*sin($a);
        }

        $p[] = $xc+$w*cos($ea);
        $p[] = $yc-$h*sin($ea);
        $p[] = $xc;
        $p[] = $yc;

        $img->SetColor($edgecolor);
        $img->Polygon($p);

        // Unfortunately we can't really draw the full edge around the whole of
        // of the slice if any of the slices are exploded. The reason is that
        // this algorithm is to simply. There are cases where the edges will
        // "overwrite" other slices when they have been exploded.
        // Doing the full, proper 3D hidden lines stiff is actually quite
        // tricky. So for exploded pies we only draw the top edge. Not perfect
        // but the "real" solution is much more complicated.
        if( $fulledge && !( $sa > 0 && $sa < M_PI && $ea < M_PI) ) {

            if($sa < M_PI && $ea > M_PI) {
                $sa = M_PI;
            }

            if($sa < 2*M_PI && (($ea >= 2*M_PI) || ($ea > 0 && $ea < $sa ) ) ) {
                $ea = 2*M_PI;
            }

            if( $sa >= M_PI && $ea <= 2*M_PI ) {
                $p = array($xc + $w*cos($sa),$yc - $h*sin($sa),
                $xc + $w*cos($sa),$z + $yc - $h*sin($sa));

                for($a=$sa+$step; $a < $ea; $a += $step ) {
                    $p[] = $xc + $w*cos($a);
                    $p[] = $z + $yc - $h*sin($a);
                }
                $p[] = $xc + $w*cos($ea);
                $p[] = $z + $yc - $h*sin($ea);
                $p[] = $xc + $w*cos($ea);
                $p[] = $yc - $h*sin($ea);
                $img->SetColor($edgecolor);
                $img->Polygon($p);
            }
        }
    }

    function Stroke($img,$aaoption=0) {
        $n = count($this->data);

        // If user hasn't set the colors use the theme array
        if( $this->setslicecolors==null ) {
            $colors = array_keys($img->rgb->rgb_table);
            sort($colors);
            $idx_a=$this->themearr[$this->theme];
            $ca = array();
            $m = count($idx_a);
            for($i=0; $i < $m; ++$i) {
                $ca[$i] = $colors[$idx_a[$i]];
            }
            $ca = array_reverse(array_slice($ca,0,$n));
        }
        else {
            $ca = $this->setslicecolors;
        }


        if( $this->posx <= 1 && $this->posx > 0 ) {
            $xc = round($this->posx*$img->width);
        }
        else {
            $xc = $this->posx ;
        }

        if( $this->posy <= 1 && $this->posy > 0 ) {
            $yc = round($this->posy*$img->height);
        }
        else {
            $yc = $this->posy ;
        }

        if( $this->radius <= 1 ) {
            $width = floor($this->radius*min($img->width,$img->height));
            // Make sure that the pie doesn't overflow the image border
            // The 0.9 factor is simply an extra margin to leave some space
            // between the pie an the border of the image.
            $width = min($width,min($xc*0.9,($yc*90/$this->angle-$width/4)*0.9));
        }
        else {
            $width = $this->radius * ($aaoption === 1 ? 2 : 1 ) ;
        }

        // Add a sanity check for width
        if( $width < 1 ) {
            JpGraphError::RaiseL(14007);//("Width for 3D Pie is 0. Specify a size > 0");
        }

        // Establish a thickness. By default the thickness is a fifth of the
        // pie slice width (=pie radius) but since the perspective depends
        // on the inclination angle we use some heuristics to make the edge
        // slightly thicker the less the angle.

        // Has user specified an absolute thickness? In that case use
        // that instead

        if( $this->iThickness ) {
            $thick = $this->iThickness;
            $thick *= ($aaoption === 1 ? 2 : 1 );
        }
        else {
            $thick = $width/12;
        }
        $a = $this->angle;
        
        if( $a <= 30 ) $thick *= 1.6;
        elseif( $a <= 40 ) $thick *= 1.4;
        elseif( $a <= 50 ) $thick *= 1.2;
        elseif( $a <= 60 ) $thick *= 1.0;
        elseif( $a <= 70 ) $thick *= 0.8;
        elseif( $a <= 80 ) $thick *= 0.7;
        else $thick *= 0.6;

        $thick = floor($thick);

        if( $this->explode_all ) {
            for($i=0; $i < $n; ++$i)
                $this->explode_radius[$i]=$this->explode_r;
        }

        $this->Pie3D($aaoption,$img,$this->data, $ca, $xc, $yc, $width, $this->angle,
        $thick, 0.65, $this->startangle, $this->edgecolor, $this->edgeweight);

        // Adjust title position
        if( $aaoption != 1 ) {
            $this->title->SetPos($xc,$yc-$this->title->GetFontHeight($img)-$width/2-$this->title->margin,         "center","bottom");
            $this->title->Stroke($img);
        }
    }

    //---------------
    // PRIVATE METHODS

    // Position the labels of each slice
    function StrokeLabels($label,$img,$a,$xp,$yp,$z) {
        $this->value->halign="left";
        $this->value->valign="top";

        // Position the axis title.
        // dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
        // that intersects with the extension of the corresponding axis. The code looks a little
        // bit messy but this is really the only way of having a reasonable position of the
        // axis titles.
        $this->value->ApplyFont($img);
        $h=$img->GetTextHeight($label);
        // For numeric values the format of the display value
        // must be taken into account
        if( is_numeric($label) ) {
            if( $label >= 0 ) {
                $w=$img->GetTextWidth(sprintf($this->value->format,$label));
            }
            else {
                $w=$img->GetTextWidth(sprintf($this->value->negformat,$label));
            }
        }
        else {
            $w=$img->GetTextWidth($label);
        }
        
        while( $a > 2*M_PI ) {
            $a -= 2*M_PI;
        }
        
        if( $a>=7*M_PI/4 || $a <= M_PI/4 ) $dx=0;
        if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dx=($a-M_PI/4)*2/M_PI;
        if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dx=1;
        if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dx=(1-($a-M_PI*5/4)*2/M_PI);

        if( $a>=7*M_PI/4 ) $dy=(($a-M_PI)-3*M_PI/4)*2/M_PI;
        if( $a<=M_PI/4 ) $dy=(1-$a*2/M_PI);
        if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dy=1;
        if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dy=(1-($a-3*M_PI/4)*2/M_PI);
        if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dy=0;

        $x = round($xp-$dx*$w);
        $y = round($yp-$dy*$h);

        // Mark anchor point for debugging
        /*
        $img->SetColor('red');
        $img->Line($xp-10,$yp,$xp+10,$yp);
        $img->Line($xp,$yp-10,$xp,$yp+10);
        */

        $oldmargin = $this->value->margin;
        $this->value->margin=0;
        $this->value->Stroke($img,$label,$x,$y);
        $this->value->margin=$oldmargin;

    }
} // Class

/* EOF */
?>
