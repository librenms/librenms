<?php
/*=======================================================================
// File:	JPGRAPH_PIE.PHP
// Description:	Pie plot extension for JpGraph
// Created: 	2001-02-14
// Ver:		$Id: jpgraph_pie.php 1091 2009-01-18 22:57:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================
*/


// Defines for PiePlot::SetLabelType()
define("PIE_VALUE_ABS",1);
define("PIE_VALUE_PER",0);
define("PIE_VALUE_PERCENTAGE",0);
define("PIE_VALUE_ADJPERCENTAGE",2);
define("PIE_VALUE_ADJPER",2);

//===================================================
// CLASS PiePlot
// Description: Draws a pie plot
//===================================================
class PiePlot {
    public $posx=0.5,$posy=0.5;
    protected $radius=0.3;
    protected $explode_radius=array(),$explode_all=false,$explode_r=20;
    protected $labels=null, $legends=null;
    protected $csimtargets=null,$csimwintargets=null;  // Array of targets for CSIM
    protected $csimareas='';		// Generated CSIM text	
    protected $csimalts=null;		// ALT tags for corresponding target
    protected $data=null;
    public $title;
    protected $startangle=0;
    protected $weight=1, $color="black";
    protected $legend_margin=6,$show_labels=true;
    protected $themearr = array(
	"earth" 	=> array(136,34,40,45,46,62,63,134,74,10,120,136,141,168,180,77,209,218,346,395,89,430),
	"pastel" => array(27,415,128,59,66,79,105,110,42,147,152,230,236,240,331,337,405,38),
	"water"  => array(8,370,24,40,335,56,213,237,268,14,326,387,10,388),
	"sand"   => array(27,168,34,170,19,50,65,72,131,209,46,393));
    protected $theme="earth";
    protected $setslicecolors=array();
    protected $labeltype=0; // Default to percentage
    protected $pie_border=true,$pie_interior_border=true;
    public $value;
    protected $ishadowcolor='',$ishadowdrop=4;
    protected $ilabelposadj=1;
    protected $legendcsimtargets = array(),$legendcsimwintargets = array();
    protected $legendcsimalts = array();
    protected $adjusted_data = array();
    public $guideline = null;
    protected $guidelinemargin=10,$iShowGuideLineForSingle = false;
    protected $iGuideLineCurve = false,$iGuideVFactor=1.4,$iGuideLineRFactor=0.8;
    protected $la = array(); // Holds the exact angle for each label
	
//---------------
// CONSTRUCTOR
    function PiePlot($data) {
	$this->data = array_reverse($data);
	$this->title = new Text("");
	$this->title->SetFont(FF_FONT1,FS_BOLD);
	$this->value = new DisplayValue();
	$this->value->Show();
	$this->value->SetFormat('%.1f%%');
	$this->guideline = new LineProperty();
    }

//---------------
// PUBLIC METHODS	
    function SetCenter($x,$y=0.5) {
	$this->posx = $x;
	$this->posy = $y;
    }

    // Enable guideline and set drwaing policy
    function SetGuideLines($aFlg=true,$aCurved=true,$aAlways=false) {
	$this->guideline->Show($aFlg);
	$this->iShowGuideLineForSingle = $aAlways;
	$this->iGuideLineCurve = $aCurved;
    }

    // Adjuste the distance between labels and labels and pie
    function SetGuideLinesAdjust($aVFactor,$aRFactor=0.8) {
	$this->iGuideVFactor=$aVFactor;
	$this->iGuideLineRFactor=$aRFactor;
    }

    function SetColor($aColor) {
	$this->color = $aColor;
    }
	
    function SetSliceColors($aColors) {
	$this->setslicecolors = $aColors;
    }
	
    function SetShadow($aColor='darkgray',$aDropWidth=4) {
	$this->ishadowcolor = $aColor;
	$this->ishadowdrop = $aDropWidth;
    }

    function SetCSIMTargets($aTargets,$aAlts='',$aWinTargets='') {
	$this->csimtargets=array_reverse($aTargets);
	if( is_array($aWinTargets) )
	    $this->csimwintargets=array_reverse($aWinTargets);
	if( is_array($aAlts) )
	    $this->csimalts=array_reverse($aAlts);
    }
	
    function GetCSIMareas() {
	return $this->csimareas;
    }

    function AddSliceToCSIM($i,$xc,$yc,$radius,$sa,$ea) {  
        //Slice number, ellipse centre (x,y), height, width, start angle, end angle
	while( $sa > 2*M_PI ) $sa = $sa - 2*M_PI;
	while( $ea > 2*M_PI ) $ea = $ea - 2*M_PI;

	$sa = 2*M_PI - $sa;
	$ea = 2*M_PI - $ea;

	// Special case when we have only one slice since then both start and end
	// angle will be == 0
	if( abs($sa - $ea) < 0.0001 ) {
	    $sa=2*M_PI; $ea=0;
	}

	//add coordinates of the centre to the map
	$xc = floor($xc);$yc=floor($yc);
	$coords = "$xc, $yc";

	//add coordinates of the first point on the arc to the map
	$xp = floor(($radius*cos($ea))+$xc);
	$yp = floor($yc-$radius*sin($ea));
	$coords.= ", $xp, $yp";
	
	//add coordinates every 0.2 radians
	$a=$ea+0.2;

	// If we cross the 360-limit with a slice we need to handle
	// the fact that end angle is smaller than start
	if( $sa < $ea ) {
	    while ($a <= 2*M_PI) {
		$xp = floor($radius*cos($a)+$xc);
		$yp = floor($yc-$radius*sin($a));
		$coords.= ", $xp, $yp";
		$a += 0.2;
	    }
	    $a -= 2*M_PI;
	}


	while ($a < $sa) {
	    $xp = floor($radius*cos($a)+$xc);
	    $yp = floor($yc-$radius*sin($a));
	    $coords.= ", $xp, $yp";
	    $a += 0.2;
	}
		
	//Add the last point on the arc
	$xp = floor($radius*cos($sa)+$xc);
	$yp = floor($yc-$radius*sin($sa));
	$coords.= ", $xp, $yp";
	if( !empty($this->csimtargets[$i]) ) {
	    $this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->csimtargets[$i]."\"";
	    $tmp="";
	    if( !empty($this->csimwintargets[$i]) ) {
		$this->csimareas .= " target=\"".$this->csimwintargets[$i]."\" "; 
	    }
	    if( !empty($this->csimalts[$i]) ) {
		$tmp=sprintf($this->csimalts[$i],$this->data[$i]);
		$this->csimareas .= " title=\"$tmp\" alt=\"$tmp\" ";
	    }
	    $this->csimareas .= " />\n";
	}
    }

	
    function SetTheme($aTheme) {
	if( in_array($aTheme,array_keys($this->themearr)) )
	    $this->theme = $aTheme;
	else
	    JpGraphError::RaiseL(15001,$aTheme);//("PiePLot::SetTheme() Unknown theme: $aTheme");
    }
	
    function ExplodeSlice($e,$radius=20) {
	if( ! is_integer($e) ) 
	    JpGraphError::RaiseL(15002);//('Argument to PiePlot::ExplodeSlice() must be an integer');
	$this->explode_radius[$e]=$radius;
    }

    function ExplodeAll($radius=20) {
	$this->explode_all=true;
	$this->explode_r = $radius;
    }

    function Explode($aExplodeArr) {
	if( !is_array($aExplodeArr) ) {
	    JpGraphError::RaiseL(15003);
//("Argument to PiePlot::Explode() must be an array with integer distances.");
	}
	$this->explode_radius = $aExplodeArr;
    }

    function SetStartAngle($aStart) {
	if( $aStart < 0 || $aStart > 360 ) {
	    JpGraphError::RaiseL(15004);//('Slice start angle must be between 0 and 360 degrees.');
	}
	$this->startangle = 360-$aStart;
	$this->startangle *= M_PI/180;
    }
	
    function SetFont($family,$style=FS_NORMAL,$size=10) {
	JpGraphError::RaiseL(15005);//('PiePlot::SetFont() is deprecated. Use PiePlot->value->SetFont() instead.');
    }
	
    // Size in percentage
    function SetSize($aSize) {
	if( ($aSize>0 && $aSize<=0.5) || ($aSize>10 && $aSize<1000) )
	    $this->radius = $aSize;
	else
	    JpGraphError::RaiseL(15006);
//("PiePlot::SetSize() Radius for pie must either be specified as a fraction [0, 0.5] of the size of the image or as an absolute size in pixels  in the range [10, 1000]");
    }
	
    function SetFontColor($aColor) {
	JpGraphError::RaiseL(15007);
//('PiePlot::SetFontColor() is deprecated. Use PiePlot->value->SetColor() instead.');
    }
	
    // Set label arrays
    function SetLegends($aLegend) {
	$this->legends = $aLegend;
    }

    // Set text labels for slices 
    function SetLabels($aLabels,$aLblPosAdj="auto") {
	$this->labels = array_reverse($aLabels);
	$this->ilabelposadj=$aLblPosAdj;
    }

    function SetLabelPos($aLblPosAdj) {
	$this->ilabelposadj=$aLblPosAdj;
    }
	
    // Should we display actual value or percentage?
    function SetLabelType($t) {
	if( $t < 0 || $t > 2 ) 
	    JpGraphError::RaiseL(15008,$t);
//("PiePlot::SetLabelType() Type for pie plots must be 0 or 1 (not $t).");
	$this->labeltype=$t;
    }

    // Deprecated. 
    function SetValueType($aType) {
	$this->SetLabelType($aType);
    }

    // Should the circle around a pie plot be displayed
    function ShowBorder($exterior=true,$interior=true) {
	$this->pie_border = $exterior;
	$this->pie_interior_border = $interior;
    }
	
    // Setup the legends
    function Legend($graph) {
	$colors = array_keys($graph->img->rgb->rgb_table);
   	sort($colors);	
   	$ta=$this->themearr[$this->theme];	
   	$n = count($this->data);

   	if( $this->setslicecolors==null ) {
	    $numcolors=count($ta);
	    if( class_exists('PiePlot3D',false) && ($this instanceof PiePlot3D) ) {
		$ta = array_reverse(array_slice($ta,0,$n));
	    }
	}
   	else {
	    $this->setslicecolors = array_slice($this->setslicecolors,0,$n);
	    $numcolors=count($this->setslicecolors); 
	    if( $graph->pieaa && !($this instanceof PiePlot3D) ) { 
		$this->setslicecolors = array_reverse($this->setslicecolors);
	    }
	}
		
	$sum=0;
	for($i=0; $i < $n; ++$i)
	    $sum += $this->data[$i];

	// Bail out with error if the sum is 0
	if( $sum==0 )
	    JpGraphError::RaiseL(15009);//("Illegal pie plot. Sum of all data is zero for Pie!");

	// Make sure we don't plot more values than data points
	// (in case the user added more legends than data points)
	$n = min(count($this->legends),count($this->data));
	if( $this->legends != "" ) {
	    $this->legends = array_reverse(array_slice($this->legends,0,$n));
	}
	for( $i=$n-1; $i >= 0; --$i ) {
	    $l = $this->legends[$i];
	    // Replace possible format with actual values
	    if( count($this->csimalts) > $i ) {
		$fmt = $this->csimalts[$i];
	    }
	    else {
		$fmt = "%d"; // Deafult Alt if no other has been specified
	    }
	    if( $this->labeltype==0 ) {
		$l = sprintf($l,100*$this->data[$i]/$sum);
		$alt = sprintf($fmt,$this->data[$i]);
		
	    }
	    elseif( $this->labeltype == 1)  {
		$l = sprintf($l,$this->data[$i]);
		$alt = sprintf($fmt,$this->data[$i]);
		
	    }
	    else {
		$l = sprintf($l,$this->adjusted_data[$i]);
		$alt = sprintf($fmt,$this->adjusted_data[$i]);
	    }

	    if( empty($this->csimwintargets[$i]) ) {
		$wintarg = '';
	    }
	    else {
		$wintarg = $this->csimwintargets[$i];
	    }

	    if( $this->setslicecolors==null ) {
		$graph->legend->Add($l,$colors[$ta[$i%$numcolors]],"",0,$this->csimtargets[$i],$alt,$wintarg);
	    }
	    else {
		$graph->legend->Add($l,$this->setslicecolors[$i%$numcolors],"",0,$this->csimtargets[$i],$alt,$wintarg);
	    }
	}
    }
	
    // Adjust the rounded percetage value so that the sum of
    // of the pie slices are always 100%
    // Using the Hare/Niemeyer method
    function AdjPercentage($aData,$aPrec=0) {
	$mul=100;
	if( $aPrec > 0 && $aPrec < 3 ) {
	    if( $aPrec == 1 ) 
		$mul=1000;
		else
		    $mul=10000;
	}
	
	$tmp = array();
	$result = array();
	$quote_sum=0;
	$n = count($aData) ;
	for( $i=0, $sum=0; $i < $n; ++$i )
	    $sum+=$aData[$i];
	foreach($aData as $index => $value) {
	    $tmp_percentage=$value/$sum*$mul;
	    $result[$index]=floor($tmp_percentage);
	    $tmp[$index]=$tmp_percentage-$result[$index];
	    $quote_sum+=$result[$index];
	}
	if( $quote_sum == $mul) {
	    if( $mul > 100 ) {
		$tmp = $mul / 100;
		for( $i=0; $i < $n; ++$i ) {
		    $result[$i] /= $tmp ;
		}
	    }
	    return $result;
	}
	arsort($tmp,SORT_NUMERIC);
	reset($tmp);
	for($i=0; $i < $mul-$quote_sum; $i++)
	{
	    $result[key($tmp)]++;
	    next($tmp);
	}
	if( $mul > 100 ) {
	    $tmp = $mul / 100;
	    for( $i=0; $i < $n; ++$i ) {
		$result[$i] /= $tmp ;
	    }
	}
	return $result;
    }


    function Stroke($img,$aaoption=0) {
	// aaoption is used to handle antialias
	// aaoption == 0 a normal pie
	// aaoption == 1 just the body
	// aaoption == 2 just the values

	// Explode scaling. If anti anti alias we scale the image
	// twice and we also need to scale the exploding distance
	$expscale = $aaoption === 1 ? 2 : 1;

	if( $this->labeltype == 2 ) {
	    // Adjust the data so that it will add up to 100%
	    $this->adjusted_data = $this->AdjPercentage($this->data);
	}

	$colors = array_keys($img->rgb->rgb_table);
   	sort($colors);	
   	$ta=$this->themearr[$this->theme];	
	$n = count($this->data);
   	
   	if( $this->setslicecolors==null ) {
	    $numcolors=count($ta);
	}
   	else {
	    // We need to create an array of colors as long as the data
	    // since we need to reverse it to get the colors in the right order
	    $numcolors=count($this->setslicecolors); 
	    $i = 2*$numcolors;
	    while( $n > $i ) {
		$this->setslicecolors = array_merge($this->setslicecolors,$this->setslicecolors);
		$i += $n;
	    }
	    $tt = array_slice($this->setslicecolors,0,$n % $numcolors);
	    $this->setslicecolors = array_merge($this->setslicecolors,$tt);
	    $this->setslicecolors = array_reverse($this->setslicecolors);
	}

	// Draw the slices
	$sum=0;
	for($i=0; $i < $n; ++$i)
	    $sum += $this->data[$i];
	
	// Bail out with error if the sum is 0
	if( $sum==0 )
	    JpGraphError::RaiseL(15009);//("Sum of all data is 0 for Pie.");
	
	// Set up the pie-circle
	if( $this->radius <= 1 )
	    $radius = floor($this->radius*min($img->width,$img->height));
	else {
	    $radius = $aaoption === 1 ? $this->radius*2 : $this->radius;
	}

	if( $this->posx <= 1 && $this->posx > 0 )
	    $xc = round($this->posx*$img->width);
	else
	    $xc = $this->posx ;
	
	if( $this->posy <= 1 && $this->posy > 0 )
	    $yc = round($this->posy*$img->height);
	else
	    $yc = $this->posy ;
		
	$n = count($this->data);

	if( $this->explode_all )
	    for($i=0; $i < $n; ++$i)
		$this->explode_radius[$i]=$this->explode_r;

	// If we have a shadow and not just drawing the labels
	if( $this->ishadowcolor != "" && $aaoption !== 2) {
	    $accsum=0;
	    $angle2 = $this->startangle;
	    $img->SetColor($this->ishadowcolor);
	    for($i=0; $sum > 0 && $i < $n; ++$i) {
		$j = $n-$i-1;
		$d = $this->data[$i];
		$angle1 = $angle2;
		$accsum += $d;
		$angle2 = $this->startangle+2*M_PI*$accsum/$sum;
		if( empty($this->explode_radius[$j]) )
		    $this->explode_radius[$j]=0;

		if( $d < 0.00001 ) continue;

		$la = 2*M_PI - (abs($angle2-$angle1)/2.0+$angle1);

		$xcm = $xc + $this->explode_radius[$j]*cos($la)*$expscale;
		$ycm = $yc - $this->explode_radius[$j]*sin($la)*$expscale;
		
		$xcm += $this->ishadowdrop*$expscale;
		$ycm += $this->ishadowdrop*$expscale;

		$_sa = round($angle1*180/M_PI);
		$_ea = round($angle2*180/M_PI);

		// The CakeSlice method draws a full circle in case of start angle = end angle
		// for pie slices we don't want this behaviour unless we only have one
		// slice in the pie in case it is the wanted behaviour
		if( $_ea-$_sa > 0.1 || $n==1 ) {
		    $img->CakeSlice($xcm,$ycm,$radius-1,$radius-1,
				    $angle1*180/M_PI,$angle2*180/M_PI,$this->ishadowcolor);
		}
	    }
	}

	//--------------------------------------------------------------------------------
	// This is the main loop to draw each cake slice
	//--------------------------------------------------------------------------------

	// Set up the accumulated sum, start angle for first slice and border color
	$accsum=0;
	$angle2 = $this->startangle;
	$img->SetColor($this->color);

	// Loop though all the slices if there is a pie to draw (sum>0)
	// There are n slices in total
	for($i=0; $sum>0 && $i < $n; ++$i) {

	    // $j is the actual index used for the slice
	    $j = $n-$i-1;

	    // Make sure we havea  valid distance to explode the slice
	    if( empty($this->explode_radius[$j]) )
		$this->explode_radius[$j]=0;

	    // The actual numeric value for the slice
	    $d = $this->data[$i];

	    $angle1 = $angle2;

	    // Accumlate the sum
	    $accsum += $d;

	    // The new angle when we add the "size" of this slice
	    // angle1 is then the start and angle2 the end of this slice
	    $angle2 = $this->NormAngle($this->startangle+2*M_PI*$accsum/$sum);

	    // We avoid some trouble by not allowing end angle to be 0, in that case
	    // we translate to 360


	    // la is used to hold the label angle, which is centered on the slice
	    if( $angle2 < 0.0001 && $angle1 > 0.0001 ) {
		$this->la[$i] = 2*M_PI - (abs(2*M_PI-$angle1)/2.0+$angle1);
	    }
	    elseif( $angle1 > $angle2 ) {
		// The case where the slice crosses the 3 a'clock line
		// Remember that the slices are counted clockwise and
		// labels are counted counter clockwise so we need to revert with 2 PI
		$this->la[$i] = 2*M_PI-$this->NormAngle($angle1 + ((2*M_PI - $angle1)+$angle2)/2);
	    }
	    else {
		$this->la[$i] = 2*M_PI - (abs($angle2-$angle1)/2.0+$angle1);
	    }

	    // Too avoid rounding problems we skip the slice if it is too small
	    if( $d < 0.00001 ) continue;

	    // If the user has specified an array of colors for each slice then use
	    // that a color otherwise use the theme array (ta) of colors
	    if( $this->setslicecolors==null )
		$slicecolor=$colors[$ta[$i%$numcolors]];
	    else
		$slicecolor=$this->setslicecolors[$i%$numcolors];
	    

	    
//$_sa = round($angle1*180/M_PI);
//$_ea = round($angle2*180/M_PI);
//$_la = round($this->la[$i]*180/M_PI);
//echo "ang1=$_sa , ang2=$_ea, la=$_la, color=$slicecolor<br>";


	    // If we have enabled antialias then we don't draw any border so
	    // make the bordedr color the same as the slice color
	    if( $this->pie_interior_border && $aaoption===0 )
		$img->SetColor($this->color);
	    else
		$img->SetColor($slicecolor);	    
	    $arccolor = $this->pie_border && $aaoption===0 ? $this->color : "";

	    // Calculate the x,y coordinates for the base of this slice taking
	    // the exploded distance into account. Here we use the mid angle as the
	    // ray of extension and we have the mid angle handy as it is also the
	    // label angle
	    $xcm = $xc + $this->explode_radius[$j]*cos($this->la[$i])*$expscale;
	    $ycm = $yc - $this->explode_radius[$j]*sin($this->la[$i])*$expscale;

	    // If we are not just drawing the labels then draw this cake slice
	    if( $aaoption !== 2 ) {

		
		$_sa = round($angle1*180/M_PI);
		$_ea = round($angle2*180/M_PI);
		$_la = round($this->la[$i]*180/M_PI);
		//echo "[$i] sa=$_sa, ea=$_ea, la[$i]=$_la, (color=$slicecolor)<br>";
		

		// The CakeSlice method draws a full circle in case of start angle = end angle
		// for pie slices we don't want this behaviour unless we only have one
		// slice in the pie in case it is the wanted behaviour
		if( abs($_ea-$_sa) > 0.1 || $n==1 ) {
		    $img->CakeSlice($xcm,$ycm,$radius-1,$radius-1,$_sa,$_ea,$slicecolor,$arccolor);
		}
	    }

	    // If the CSIM is used then make sure we register a CSIM area for this slice as well
	    if( $this->csimtargets && $aaoption !== 1 ) {
		$this->AddSliceToCSIM($i,$xcm,$ycm,$radius,$angle1,$angle2);
	    }
	}

	// Format the titles for each slice
	if( $aaoption !== 2 ) {
	    for( $i=0; $i < $n; ++$i) {
		if( $this->labeltype==0 ) {
		    if( $sum != 0 )
			$l = 100.0*$this->data[$i]/$sum;
		    else
			$l = 0.0;
		}
		elseif( $this->labeltype==1 ) {
		    $l = $this->data[$i]*1.0;
		}
		else {
		    $l = $this->adjusted_data[$i];
		}
		if( isset($this->labels[$i]) && is_string($this->labels[$i]) )
		    $this->labels[$i]=sprintf($this->labels[$i],$l);
		else
		    $this->labels[$i]=$l;
	    }
	}

	if( $this->value->show && $aaoption !== 1 ) {
	    $this->StrokeAllLabels($img,$xc,$yc,$radius);
	}

	// Adjust title position
	if( $aaoption !== 1 ) {
	    $this->title->SetPos($xc,
			  $yc-$this->title->GetFontHeight($img)-$radius-$this->title->margin,
			  "center","bottom");
	    $this->title->Stroke($img);
	}

    }

//---------------
// PRIVATE METHODS	

    function NormAngle($a) {
	while( $a < 0 ) $a += 2*M_PI;
	while( $a > 2*M_PI ) $a -= 2*M_PI;
	return $a;
    }

    function Quadrant($a) {
	$a=$this->NormAngle($a);
	if( $a > 0 && $a <= M_PI/2 )
	    return 0;
	if( $a > M_PI/2 && $a <= M_PI )
	    return 1;
	if( $a > M_PI && $a <= 1.5*M_PI )
	    return 2;
	if( $a > 1.5*M_PI )
	    return 3;
    }

    function StrokeGuideLabels($img,$xc,$yc,$radius) {
	$n = count($this->labels);

	//-----------------------------------------------------------------------
	// Step 1 of the algorithm is to construct a number of clusters
	// a cluster is defined as all slices within the same quadrant (almost)
	// that has an angular distance less than the treshold
	//-----------------------------------------------------------------------
	$tresh_hold=25 * M_PI/180; // 25 degrees difference to be in a cluster
	$incluster=false;	// flag if we are currently in a cluster or not
	$clusters = array();	// array of clusters
	$cidx=-1;		// running cluster index

	// Go through all the labels and construct a number of clusters
	for($i=0; $i < $n-1; ++$i) {
	    // Calc the angle distance between two consecutive slices
	    $a1=$this->la[$i];
	    $a2=$this->la[$i+1];
	    $q1 = $this->Quadrant($a1);
	    $q2 = $this->Quadrant($a2);
	    $diff = abs($a1-$a2);
	    if( $diff < $tresh_hold ) {
		if( $incluster ) {
		    $clusters[$cidx][1]++;
		    // Each cluster can only cover one quadrant
		    // Do we cross a quadrant ( and must break the cluster)
		    if( $q1 !=  $q2 ) {
			// If we cross a quadrant boundary we normally start a 
			// new cluster. However we need to take the 12'a clock
			// and 6'a clock positions into a special consideration.
			// Case 1: WE go from q=1 to q=2 if the last slice on
			// the cluster for q=1 is close to 12'a clock and the 
			// first slice in q=0 is small we extend the previous
			// cluster
			if( $q1 == 1 && $q2 == 0 && $a2 > (90-15)*M_PI/180 ) {
			    if( $i < $n-2 ) {
				$a3 = $this->la[$i+2];
				// If there isn't a cluster coming up with the next-next slice
				// we extend the previous cluster to cover this slice as well
				if( abs($a3-$a2) >= $tresh_hold ) {
				    $clusters[$cidx][1]++;
				    $i++;
				}
			    }
			}
			elseif( $q1 == 3 && $q2 == 2 && $a2 > (270-15)*M_PI/180 ) {
			    if( $i < $n-2 ) {
				$a3 = $this->la[$i+2];
				// If there isn't a cluster coming up with the next-next slice
				// we extend the previous cluster to cover this slice as well
				if( abs($a3-$a2) >= $tresh_hold ) {
				    $clusters[$cidx][1]++;
				    $i++;
				}
			    }
			}

			if( $q1==2 && $q2==1 && $a2 > (180-15)*M_PI/180 ) {
			    $clusters[$cidx][1]++;
			    $i++;			    
			}
			
			$incluster = false;
		    }
		}
		elseif( $q1 == $q2)  {
		    $incluster = true;
		    // Now we have a special case for quadrant 0. If we previously
		    // have a cluster of one in quadrant 0 we just extend that
		    // cluster. If we don't do this then we risk that the label
		    // for the cluster of one will cross the guide-line
		    if( $q1 == 0 && $cidx > -1 && 
			$clusters[$cidx][1] == 1 && 
			$this->Quadrant($this->la[$clusters[$cidx][0]]) == 0 ) {
			$clusters[$cidx][1]++;
		    }
		    else {
			$cidx++;
			$clusters[$cidx][0] = $i;
			$clusters[$cidx][1] = 1;
		    }
		}
		else {  
		    // Create a "cluster" of one since we are just crossing
		    // a quadrant
		    $cidx++;
		    $clusters[$cidx][0] = $i;
		    $clusters[$cidx][1] = 1;	    
		}
	    }
	    else {
		if( $incluster ) {
		    // Add the last slice
		    $clusters[$cidx][1]++;
		    $incluster = false;
		}
		else { // Create a "cluster" of one
		    $cidx++;
		    $clusters[$cidx][0] = $i;
		    $clusters[$cidx][1] = 1;	    
		}
	    }
	}
	// Handle the very last slice
	if( $incluster ) {
	    $clusters[$cidx][1]++;
	}
	else { // Create a "cluster" of one
	    $cidx++;
	    $clusters[$cidx][0] = $i;
	    $clusters[$cidx][1] = 1;	    
	}

	/*
	if( true ) { 
	    // Debug printout in labels
	    for( $i=0; $i <= $cidx; ++$i ) {
		for( $j=0; $j < $clusters[$i][1]; ++$j ) {
		    $a = $this->la[$clusters[$i][0]+$j];
		    $aa = round($a*180/M_PI);
		    $q = $this->Quadrant($a);
		    $this->labels[$clusters[$i][0]+$j]="[$q:$aa] $i:$j";
		}
	    }
	}
	*/

	//-----------------------------------------------------------------------
	// Step 2 of the algorithm is use the clusters and draw the labels
	// and guidelines
	//-----------------------------------------------------------------------

	// We use the font height as the base factor for how far we need to
	// spread the labels in the Y-direction.
	$this->value->ApplyFont($img);
	$fh = $img->GetFontHeight();
	$origvstep=$fh*$this->iGuideVFactor;
	$this->value->SetMargin(0);

	// Number of clusters found
	$nc = count($clusters);

	// Walk through all the clusters
	for($i=0; $i < $nc; ++$i) {

	    // Start angle and number of slices in this cluster
	    $csize = $clusters[$i][1];
	    $a = $this->la[$clusters[$i][0]];
	    $q = $this->Quadrant($a);

	    // Now set up the start and end conditions to make sure that
	    // in each cluster we walk through the all the slices starting with the slice
	    // closest to the equator. Since all slices are numbered clockwise from "3'a clock"
	    // we have different conditions depending on in which quadrant the slice lies within.
	    if( $q == 0 ) {
		$start = $csize-1; $idx = $start; $step = -1; $vstep = -$origvstep;
	    }
	    elseif( $q == 1 ) {
		$start = 0; $idx = $start; $step = 1; $vstep = -$origvstep;
	    }
	    elseif( $q == 2 ) {
		$start = $csize-1; $idx = $start; $step = -1; $vstep = $origvstep;
	    }
	    elseif( $q == 3 ) {
		$start = 0; $idx = $start; $step = 1; $vstep = $origvstep;
	    }

	    // Walk through all slices within this cluster
	    for($j=0; $j < $csize; ++$j) {   
		// Now adjust the position of the labels in each cluster starting
		// with the slice that is closest to the equator of the pie
		$a = $this->la[$clusters[$i][0]+$idx];
		    
		// Guide line start in the center of the arc of the slice
		$r = $radius+$this->explode_radius[$n-1-($clusters[$i][0]+$idx)];
		$x = round($r*cos($a)+$xc);
		$y = round($yc-$r*sin($a));
		
		// The distance from the arc depends on chosen font and the "R-Factor"
		$r += $fh*$this->iGuideLineRFactor;

		// Should the labels be placed curved along the pie or in straight columns
		// outside the pie?
		if( $this->iGuideLineCurve )
		    $xt=round($r*cos($a)+$xc);

		// If this is the first slice in the cluster we need some first time
		// proessing
		if( $idx == $start ) {
		    if( ! $this->iGuideLineCurve )
			$xt=round($r*cos($a)+$xc);
		    $yt=round($yc-$r*sin($a));

		    // Some special consideration in case this cluster starts
		    // in quadrant 1 or 3 very close to the "equator" (< 20 degrees) 
		    // and the previous clusters last slice is within the tolerance. 
		    // In that case we add a font height to this labels Y-position 
		    // so it doesn't collide with
		    // the slice in the previous cluster
		    $prevcluster = ($i + ($nc-1) ) % $nc;
		    $previdx=$clusters[$prevcluster][0]+$clusters[$prevcluster][1]-1;
		    if( $q == 1 && $a > 160*M_PI/180 ) {
			// Get the angle for the previous clusters last slice
			$diff = abs($a-$this->la[$previdx]);
			 if( $diff < $tresh_hold ) {
			     $yt -= $fh;
			 }
		    }
		    elseif( $q == 3 && $a > 340*M_PI/180 ) {
			// We need to subtract 360 to compare angle distance between
			// q=0 and q=3
			$diff = abs($a-$this->la[$previdx]-360*M_PI/180);
			if( $diff < $tresh_hold ) {
			     $yt += $fh;
			}
		    }

		}
		else {
		    // The step is at minimum $vstep but if the slices are relatively large
		    // we make sure that we add at least a step that corresponds to the vertical
		    // distance between the centers at the arc on the slice
		    $prev_a = $this->la[$clusters[$i][0]+($idx-$step)];
		    $dy = abs($radius*(sin($a)-sin($prev_a))*1.2);
		    if( $vstep > 0 )
			$yt += max($vstep,$dy);
		    else
			$yt += min($vstep,-$dy);
		}

		$label = $this->labels[$clusters[$i][0]+$idx];

		if( $csize == 1 ) {
		    // A "meta" cluster with only one slice
		    $r = $radius+$this->explode_radius[$n-1-($clusters[$i][0]+$idx)];
		    $rr = $r+$img->GetFontHeight()/2;
		    $xt=round($rr*cos($a)+$xc);
		    $yt=round($yc-$rr*sin($a));
		    $this->StrokeLabel($label,$img,$xc,$yc,$a,$r); 
		    if( $this->iShowGuideLineForSingle ) 
			$this->guideline->Stroke($img,$x,$y,$xt,$yt);
		}
		else {
		    $this->guideline->Stroke($img,$x,$y,$xt,$yt);
		    if( $q==1 || $q==2 ) {
			// Left side of Pie
			$this->guideline->Stroke($img,$xt,$yt,$xt-$this->guidelinemargin,$yt);
			$lbladj = -$this->guidelinemargin-5;
			$this->value->halign = "right";
			$this->value->valign = "center";
		    }
		    else {
			// Right side of pie
			$this->guideline->Stroke($img,$xt,$yt,$xt+$this->guidelinemargin,$yt);
			$lbladj = $this->guidelinemargin+5;
			$this->value->halign = "left";
			$this->value->valign = "center";
		    }
		    $this->value->Stroke($img,$label,$xt+$lbladj,$yt);
		}

		// Udate idx to point to next slice in the cluster to process
		$idx += $step;
	    }
	}
    }

    function StrokeAllLabels($img,$xc,$yc,$radius) {
	// First normalize all angles for labels
	$n = count($this->la);
	for($i=0; $i < $n; ++$i) {
	    $this->la[$i] = $this->NormAngle($this->la[$i]);
	}
	if( $this->guideline->iShow ) {
	    $this->StrokeGuideLabels($img,$xc,$yc,$radius);
	}
	else {
	    $n = count($this->labels);
	    for($i=0; $i < $n; ++$i) {
		$this->StrokeLabel($this->labels[$i],$img,$xc,$yc,
				   $this->la[$i],
				   $radius + $this->explode_radius[$n-1-$i]); 
	    }
	}
    }

    // Position the labels of each slice
    function StrokeLabel($label,$img,$xc,$yc,$a,$r) {

	// Default value
	if( $this->ilabelposadj === 'auto' )
	    $this->ilabelposadj = 0.65;

	// We position the values diferently depending on if they are inside
	// or outside the pie
	if( $this->ilabelposadj < 1.0 ) {

	    $this->value->SetAlign('center','center');
	    $this->value->margin = 0;
	    
	    $xt=round($this->ilabelposadj*$r*cos($a)+$xc);
	    $yt=round($yc-$this->ilabelposadj*$r*sin($a));
	    
	    $this->value->Stroke($img,$label,$xt,$yt);
	}
	else {

	    $this->value->halign = "left";
	    $this->value->valign = "top";
	    $this->value->margin = 0;
	    	    
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
		if( $label > 0 )
		    $w=$img->GetTextWidth(sprintf($this->value->format,$label));
		else
		    $w=$img->GetTextWidth(sprintf($this->value->negformat,$label));
	    }
	    else
		$w=$img->GetTextWidth($label);

	    if( $this->ilabelposadj > 1.0 && $this->ilabelposadj < 5.0) {
		$r *= $this->ilabelposadj;
	    }
	    
	    $r += $img->GetFontHeight()/1.5;

	    $xt=round($r*cos($a)+$xc);
	    $yt=round($yc-$r*sin($a));

	    // Normalize angle
	    while( $a < 0 ) $a += 2*M_PI;
	    while( $a > 2*M_PI ) $a -= 2*M_PI;

	    if( $a>=7*M_PI/4 || $a <= M_PI/4 ) $dx=0;
	    if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dx=($a-M_PI/4)*2/M_PI; 
	    if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dx=1;
	    if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dx=(1-($a-M_PI*5/4)*2/M_PI);
	    
	    if( $a>=7*M_PI/4 ) $dy=(($a-M_PI)-3*M_PI/4)*2/M_PI;
	    if( $a<=M_PI/4 ) $dy=(1-$a*2/M_PI);
	    if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dy=1;
	    if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dy=(1-($a-3*M_PI/4)*2/M_PI);
	    if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dy=0;
	    
	    $this->value->Stroke($img,$label,$xt-$dx*$w,$yt-$dy*$h);
	}
    }	
} // Class


//===================================================
// CLASS PiePlotC
// Description: Same as a normal pie plot but with a 
// filled circle in the center
//===================================================
class PiePlotC extends PiePlot {
    private $imidsize=0.5;		// Fraction of total width
    private $imidcolor='white';
    public $midtitle='';
    private $middlecsimtarget='',$middlecsimwintarget='',$middlecsimalt='';

    function PiePlotC($data,$aCenterTitle='') {
	parent::PiePlot($data);
	$this->midtitle = new Text();
	$this->midtitle->ParagraphAlign('center');
    }

    function SetMid($aTitle,$aColor='white',$aSize=0.5) {
	$this->midtitle->Set($aTitle);

	$this->imidsize = $aSize ; 
	$this->imidcolor = $aColor ; 
    }

    function SetMidTitle($aTitle) {
	$this->midtitle->Set($aTitle);
    }

    function SetMidSize($aSize) {
	$this->imidsize = $aSize ; 
    }

    function SetMidColor($aColor) {
	$this->imidcolor = $aColor ; 
    }

    function SetMidCSIM($aTarget,$aAlt='',$aWinTarget='') {
	$this->middlecsimtarget = $aTarget;
	$this->middlecsimwintarget = $aWinTarget;
	$this->middlecsimalt = $aAlt;
    }

    function AddSliceToCSIM($i,$xc,$yc,$radius,$sa,$ea) {  
        //Slice number, ellipse centre (x,y), radius, start angle, end angle
	while( $sa > 2*M_PI ) $sa = $sa - 2*M_PI;
	while( $ea > 2*M_PI ) $ea = $ea - 2*M_PI;

	$sa = 2*M_PI - $sa;
	$ea = 2*M_PI - $ea;

	// Special case when we have only one slice since then both start and end
	// angle will be == 0
	if( abs($sa - $ea) < 0.0001 ) {
	    $sa=2*M_PI; $ea=0;
	}

	// Add inner circle first point
	$xp = floor(($this->imidsize*$radius*cos($ea))+$xc);
	$yp = floor($yc-($this->imidsize*$radius*sin($ea)));
	$coords = "$xp, $yp";
	
	//add coordinates every 0.25 radians
	$a=$ea+0.25;

	// If we cross the 360-limit with a slice we need to handle
	// the fact that end angle is smaller than start
	if( $sa < $ea ) {
	    while ($a <= 2*M_PI) {
		$xp = floor($radius*cos($a)+$xc);
		$yp = floor($yc-$radius*sin($a));
		$coords.= ", $xp, $yp";
		$a += 0.25;
	    }
	    $a -= 2*M_PI;
	}

	while ($a < $sa) {
	    $xp = floor(($this->imidsize*$radius*cos($a)+$xc));
	    $yp = floor($yc-($this->imidsize*$radius*sin($a)));
	    $coords.= ", $xp, $yp";
	    $a += 0.25;
	}

	// Make sure we end at the last point
	$xp = floor(($this->imidsize*$radius*cos($sa)+$xc));
	$yp = floor($yc-($this->imidsize*$radius*sin($sa)));
	$coords.= ", $xp, $yp";

	// Straight line to outer circle
	$xp = floor($radius*cos($sa)+$xc);
	$yp = floor($yc-$radius*sin($sa));
	$coords.= ", $xp, $yp";	

	//add coordinates every 0.25 radians
	$a=$sa - 0.25;
	while ($a > $ea) {
	    $xp = floor($radius*cos($a)+$xc);
	    $yp = floor($yc-$radius*sin($a));
	    $coords.= ", $xp, $yp";
	    $a -= 0.25;
	}
		
	//Add the last point on the arc
	$xp = floor($radius*cos($ea)+$xc);
	$yp = floor($yc-$radius*sin($ea));
	$coords.= ", $xp, $yp";

	// Close the arc
	$xp = floor(($this->imidsize*$radius*cos($ea))+$xc);
	$yp = floor($yc-($this->imidsize*$radius*sin($ea)));
	$coords .= ", $xp, $yp";

	if( !empty($this->csimtargets[$i]) ) {
	    $this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".
		$this->csimtargets[$i]."\"";
	    if( !empty($this->csimwintargets[$i]) ) {
		$this->csimareas .= " target=\"".$this->csimwintargets[$i]."\" ";
	    }
	    if( !empty($this->csimalts[$i]) ) {
		$tmp=sprintf($this->csimalts[$i],$this->data[$i]);
		$this->csimareas .= " title=\"$tmp\"  alt=\"$tmp\" ";
	    }
	    $this->csimareas .= " />\n";
	}
    }


    function Stroke($img,$aaoption=0) {

	// Stroke the pie but don't stroke values
	$tmp =  $this->value->show;
	$this->value->show = false;
	parent::Stroke($img,$aaoption);
	$this->value->show = $tmp;

 	$xc = round($this->posx*$img->width);
	$yc = round($this->posy*$img->height);

	$radius = floor($this->radius * min($img->width,$img->height)) ;


	if( $this->imidsize > 0 && $aaoption !== 2 ) {

	    if( $this->ishadowcolor != "" ) {
		$img->SetColor($this->ishadowcolor);
		$img->FilledCircle($xc+$this->ishadowdrop,$yc+$this->ishadowdrop,
				   round($radius*$this->imidsize));
	    }

	    $img->SetColor($this->imidcolor);
	    $img->FilledCircle($xc,$yc,round($radius*$this->imidsize));

	    if(  $this->pie_border && $aaoption === 0 ) {
		$img->SetColor($this->color);
		$img->Circle($xc,$yc,round($radius*$this->imidsize));
	    }

	    if( !empty($this->middlecsimtarget) )
		$this->AddMiddleCSIM($xc,$yc,round($radius*$this->imidsize));

	}

	if( $this->value->show && $aaoption !== 1) {
	    $this->StrokeAllLabels($img,$xc,$yc,$radius);
	    $this->midtitle->SetPos($xc,$yc,'center','center');
	    $this->midtitle->Stroke($img);
	}

    }

    function AddMiddleCSIM($xc,$yc,$r) {
	$xc=round($xc);$yc=round($yc);$r=round($r);
	$this->csimareas .= "<area shape=\"circle\" coords=\"$xc,$yc,$r\" href=\"".
	    $this->middlecsimtarget."\"";
	if( !empty($this->middlecsimwintarget) ) {
	    $this->csimareas .= " target=\"".$this->middlecsimwintarget."\"";
	}
	if( !empty($this->middlecsimalt) ) {
	    $tmp = $this->middlecsimalt;
	    $this->csimareas .= " title=\"$tmp\" alt=\"$tmp\" ";
	}
	$this->csimareas .= " />\n";
    }

    function StrokeLabel($label,$img,$xc,$yc,$a,$r) {

	if( $this->ilabelposadj === 'auto' )
	    $this->ilabelposadj = (1-$this->imidsize)/2+$this->imidsize;

	parent::StrokeLabel($label,$img,$xc,$yc,$a,$r);

    }

}


//===================================================
// CLASS PieGraph
// Description: 
//===================================================
class PieGraph extends Graph {
    private $posx, $posy, $radius;		
    private $legends=array();	
    public $plots=array();
    public $pieaa = false ;
//---------------
// CONSTRUCTOR
    function PieGraph($width=300,$height=200,$cachedName="",$timeout=0,$inline=1) {
	$this->Graph($width,$height,$cachedName,$timeout,$inline);
	$this->posx=$width/2;
	$this->posy=$height/2;
	$this->SetColor(array(255,255,255));		
    }

//---------------
// PUBLIC METHODS	
    function Add($aObj) {

	if( is_array($aObj) && count($aObj) > 0 )
	    $cl = $aObj[0];
	else
	    $cl = $aObj;

	if( $cl instanceof Text ) 
	    $this->AddText($aObj);
	elseif( class_exists('IconPlot',false) && ($cl instanceof IconPlot) ) 
	    $this->AddIcon($aObj);
	else {
	    if( is_array($aObj) ) {
		$n = count($aObj);
		for($i=0; $i < $n; ++$i ) {
		    $this->plots[] = $aObj[$i];
		}
	    }
	    else {
		$this->plots[] = $aObj;
	    }
	}
    }

    function SetAntiAliasing($aFlg=true) {
	$this->pieaa = $aFlg;
    }
	
    function SetColor($c) {
	$this->SetMarginColor($c);
    }


    function DisplayCSIMAreas() {
	    $csim="";
	    foreach($this->plots as $p ) {
		$csim .= $p->GetCSIMareas();
	    }
	    //$csim.= $this->legend->GetCSIMareas();
	    if (preg_match_all("/area shape=\"(\w+)\" coords=\"([0-9\, ]+)\"/", $csim, $coords)) {
		$this->img->SetColor($this->csimcolor);
		$n = count($coords[0]);
		for ($i=0; $i < $n; $i++) {
		    if ($coords[1][$i]=="poly") {
			preg_match_all('/\s*([0-9]+)\s*,\s*([0-9]+)\s*,*/',$coords[2][$i],$pts);
			$this->img->SetStartPoint($pts[1][count($pts[0])-1],$pts[2][count($pts[0])-1]);
			$m = count($pts[0]);
			for ($j=0; $j < $m; $j++) {
			    $this->img->LineTo($pts[1][$j],$pts[2][$j]);
			}
		    } else if ($coords[1][$i]=="rect") {
			$pts = preg_split('/,/', $coords[2][$i]);
			$this->img->SetStartPoint($pts[0],$pts[1]);
			$this->img->LineTo($pts[2],$pts[1]);
			$this->img->LineTo($pts[2],$pts[3]);
			$this->img->LineTo($pts[0],$pts[3]);
			$this->img->LineTo($pts[0],$pts[1]);
						
		    }
		}
	    }
    }

    // Method description
    function Stroke($aStrokeFileName="") {
	// If the filename is the predefined value = '_csim_special_'
	// we assume that the call to stroke only needs to do enough
	// to correctly generate the CSIM maps.
	// We use this variable to skip things we don't strictly need
	// to do to generate the image map to improve performance
	// a best we can. Therefor you will see a lot of tests !$_csim in the
	// code below.
	$_csim = ($aStrokeFileName===_CSIM_SPECIALFILE);

	// We need to know if we have stroked the plot in the
	// GetCSIMareas. Otherwise the CSIM hasn't been generated
	// and in the case of GetCSIM called before stroke to generate
	// CSIM without storing an image to disk GetCSIM must call Stroke.
	$this->iHasStroked = true;

	$n = count($this->plots);

	if( $this->pieaa ) {

	    if( !$_csim ) {
		if( $this->background_image != "" ) {
		    $this->StrokeFrameBackground();		
		}
		else {
		    $this->StrokeFrame();		
		    $this->StrokeBackgroundGrad();
		}
	    }


	    $w = $this->img->width;
	    $h = $this->img->height;
	    $oldimg = $this->img->img;

	    $this->img->CreateImgCanvas(2*$w,2*$h);
	    
	    $this->img->SetColor( $this->margin_color );
	    $this->img->FilledRectangle(0,0,2*$w-1,2*$h-1);

	    // Make all icons *2 i size since we will be scaling down the
	    // imahe to do the anti aliasing
	    $ni = count($this->iIcons);
	    for($i=0; $i < $ni; ++$i) {
		$this->iIcons[$i]->iScale *= 2 ;
		if( $this->iIcons[$i]->iX > 1 ) 
		    $this->iIcons[$i]->iX *= 2 ;
		if( $this->iIcons[$i]->iY > 1 ) 
		    $this->iIcons[$i]->iY *= 2 ;
	    }

	    $this->StrokeIcons();

	    for($i=0; $i < $n; ++$i) {
		if( $this->plots[$i]->posx > 1 ) 
		    $this->plots[$i]->posx *= 2 ;
		if( $this->plots[$i]->posy > 1 ) 
		    $this->plots[$i]->posy *= 2 ;

		$this->plots[$i]->Stroke($this->img,1);

		if( $this->plots[$i]->posx > 1 ) 
		    $this->plots[$i]->posx /= 2 ;
		if( $this->plots[$i]->posy > 1 ) 
		    $this->plots[$i]->posy /= 2 ;
	    }

	    $indent = $this->doframe ? ($this->frame_weight + ($this->doshadow ? $this->shadow_width : 0 )) : 0 ;
	    $indent += $this->framebevel ? $this->framebeveldepth + 1 : 0 ;
	    $this->img->CopyCanvasH($oldimg,$this->img->img,$indent,$indent,$indent,$indent,
				    $w-2*$indent,$h-2*$indent,2*($w-$indent),2*($h-$indent));

	    $this->img->img = $oldimg ;
	    $this->img->width = $w ;
	    $this->img->height = $h ;

	    for($i=0; $i < $n; ++$i) {
		$this->plots[$i]->Stroke($this->img,2); // Stroke labels
		$this->plots[$i]->Legend($this);
	    }

	}
	else {

	    if( !$_csim ) {
		if( $this->background_image != "" ) {
		    $this->StrokeFrameBackground();		
		}
		else {
		    $this->StrokeFrame();		
		}
	    }

	    $this->StrokeIcons();

	    for($i=0; $i < $n; ++$i) {
		$this->plots[$i]->Stroke($this->img);
		$this->plots[$i]->Legend($this);
	    }
	}

	$this->legend->Stroke($this->img);
	$this->footer->Stroke($this->img);
	$this->StrokeTitles();

	if( !$_csim ) {	

	    // Stroke texts
	    if( $this->texts != null ) {
		$n = count($this->texts);
		for($i=0; $i < $n; ++$i ) {
		    $this->texts[$i]->Stroke($this->img);
		}
	    }

	    if( _JPG_DEBUG ) {
		$this->DisplayCSIMAreas();
	    }

	    // Should we do any final image transformation
	    if( $this->iImgTrans ) {
		if( !class_exists('ImgTrans',false) ) {
		    require_once('jpgraph_imgtrans.php');
		    //JpGraphError::Raise('In order to use image transformation you must include the file jpgraph_imgtrans.php in your script.');
		}
	       
		$tform = new ImgTrans($this->img->img);
		$this->img->img = $tform->Skew3D($this->iImgTransHorizon,$this->iImgTransSkewDist,
						 $this->iImgTransDirection,$this->iImgTransHighQ,
						 $this->iImgTransMinSize,$this->iImgTransFillColor,
						 $this->iImgTransBorder);
	    }


	    // If the filename is given as the special "__handle"
	    // then the image handler is returned and the image is NOT
	    // streamed back
	    if( $aStrokeFileName == _IMG_HANDLER ) {
		return $this->img->img;
	    }
	    else {
		// Finally stream the generated picture					
		$this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,
					   $aStrokeFileName);		
	    }
	}
    }
} // Class

/* EOF */
?>
