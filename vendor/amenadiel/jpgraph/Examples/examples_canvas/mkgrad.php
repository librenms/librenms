<?php // content="text/plain; charset=utf-8"
//=======================================================================
// File:	    MKGRAD.PHP
// Description:	Simple tool to create a gradient background
// Ver: 	    $Id$
//=======================================================================

// Basic library classes
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_canvas.php');
  

// Must have a global comparison method for usort()
function _cmp($a,$b) {
    return strcmp($a,$b);
}

// Generate the input form
class Form {
    var $iColors;
    var $iGradstyles;
    function Form() {

	$rgb = new RGB();
	$this->iColors = array_keys($rgb->rgb_table);
	usort($this->iColors,'_cmp');

	$this->iGradstyles = array(
	    "Vertical",2,
	    "Horizontal",1,
	    "Vertical from middle",3,
	    "Horizontal from middle",4,
	    "Horizontal wider middle",6,
	    "Vertical wider middle",7,
	    "Rectangle",5 );
    }

    function Run() {

	echo '<h3>Generate gradient background</h3>';
	echo '<form METHOD=POST action=""><table style="border:blue solid 1;">';
	echo '<tr><td>Width:<br>'.$this->GenHTMLInput('w',8,4,300).'</td>';
	echo "\n";
	echo '<td>Height:<br>'.$this->GenHTMLInput('h',8,4,300).'</td></tr>';
	echo "\n";
	echo '<tr><td>From Color:<br>';
	echo $this->GenHTMLSelect('fc',$this->iColors);
	echo '</td><td>To Color:<br>';
	echo $this->GenHTMLSelect('tc',$this->iColors);
	echo '</td></tr>';
	echo '<tr><td colspan=2>Gradient style:<br>';
	echo $this->GenHTMLSelectCode('s',$this->iGradstyles);
	echo '</td></tr>';
	echo '<tr><td colspan=2>Filename: (empty to stream)<br>';
	echo $this->GenHTMLInput('fn',55,100);
	echo '</td></tr>';
	echo '<tr><td colspan=2 align=right>'.$this->GenHTMLSubmit('submit').'</td></tr>';
	echo '</table>';
	echo '</form>';

    }

    function GenHTMLSubmit($name) {
	return '<INPUT TYPE=submit name="ok"  value=" Ok " >';
    }


    function GenHTMLInput($name,$len,$maxlen=100,$val='') {
	return '<INPUT TYPE=TEXT NAME='.$name.' VALUE="'.$val.'" SIZE='.$len.' MAXLENGTH='.$maxlen.'>';
    }

    function GenHTMLSelect($name,$option,$selected="",$size=0) {
	$txt="<select name=$name";
	if( $size > 0 )
	    $txt .= " size=$size >";
	else 
	    $txt .= ">";
	for($i=0; $i<count($option); $i++) {
	    if( $selected==$option[$i] )
		$txt=$txt."<option selected value=\"$option[$i]\">$option[$i]</option>\n";		
	    else
		$txt=$txt."<option value=\"".$option[$i]."\">$option[$i]</option>\n";
	}
	return $txt."</select>\n";
    }
    
    function GenHTMLSelectCode($name,$option,$selected="",$size=0) {
	$txt="<select name=$name";
	if( $size > 0 )
	    $txt .= " size=$size >";
	else 
	    $txt .= ">";
	for($i=0; $i<count($option); $i += 2) {
	    if( $selected==$option[($i+1)] )
		$txt=$txt."<option selected value=".$option[($i+1)].">$option[$i]</option>\n";		
	    else
		$txt=$txt."<option value=\"".$option[($i+1)]."\">$option[$i]</option>\n";
	}
	return $txt."</select>\n";
    }

}

// Basic application driver

class Driver {
    var $iGraph, $iGrad;
    var $iWidth,$iHeight;
    var $iFromColor, $iToColor;
    var $iStyle;
    var $iForm;

    function Driver() {
	$this->iForm = new Form();
    }

    function GenGradImage() {
	
	$aWidth	 = (int)@$_POST['w'];
	$aHeight = (int)@$_POST['h'];
	$aFrom   = @$_POST['fc'];
	$aTo     = @$_POST['tc'];
	$aStyle  = @$_POST['s'];
	$aFileName  = @$_POST['fn'];

	$this->iWidth     = $aWidth;
	$this->iHeight    = $aHeight;
	$this->iFromColor = $aFrom;
	$this->iToColor   = $aTo;
	$this->iStyle     = $aStyle;

	$this->graph = new CanvasGraph($aWidth,$aHeight);
	$this->grad  = new Gradient($this->graph->img);
	$this->grad->FilledRectangle(0,0,
				     $this->iWidth,$this->iHeight,
				     $this->iFromColor,
				     $this->iToColor,
				     $this->iStyle);

	if( $aFileName != "" ) {
	    $this->graph->Stroke($aFileName);
	    echo "Image file '$aFileName' created.";
	}
	else
	    $this->graph->Stroke();
    }


    function Run() {
	
	global $HTTP_POST_VARS;

	// Two modes:
	// 1) If the script is called with no posted arguments
	// we show the input form.
	// 2) If we have posted arguments we naivly assume that
	// we are called to do the image.

	if( @$_POST['ok']===' Ok ' ) { 
	    $this->GenGradImage();
	}
	else
	    $this->iForm->Run();
    }
}

$driver = new Driver();				
$driver->Run();

?>
