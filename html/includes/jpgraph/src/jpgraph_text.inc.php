<?php
//=======================================================================
// File:	JPGRAPH_TEXT.INC.PHP
// Description:	Class to handle text as object in the graph.
//		The low level text layout engine is handled by the GD class
// Created: 	2001-01-08 (Refactored to separate file 2008-08-01)
// Ver:		$Id: jpgraph_text.inc.php 1048 2008-08-01 19:56:46Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================


//===================================================
// CLASS Text
// Description: Arbitrary text object that can be added to the graph
//===================================================
class Text {
    public $t,$margin=0;
    public $x=0,$y=0,$halign="left",$valign="top",$color=array(0,0,0);
    public $hide=false, $dir=0;
    public $iScalePosY=null,$iScalePosX=null;
    public $iWordwrap=0;
    public $font_family=FF_FONT1,$font_style=FS_NORMAL,$font_size=12;
    protected $boxed=false;	// Should the text be boxed
    protected $paragraph_align="left";
    protected $icornerradius=0,$ishadowwidth=3;
    protected $fcolor='white',$bcolor='black',$shadow=false;
    protected $iCSIMarea='',$iCSIMalt='',$iCSIMtarget='',$iCSIMWinTarget='';

//---------------
// CONSTRUCTOR

    // Create new text at absolute pixel coordinates
    function Text($aTxt="",$aXAbsPos=0,$aYAbsPos=0) {
	if( ! is_string($aTxt) ) {
	    JpGraphError::RaiseL(25050);//('First argument to Text::Text() must be s atring.');
	}
	$this->t = $aTxt;
	$this->x = round($aXAbsPos);
	$this->y = round($aYAbsPos);
	$this->margin = 0;
    }
//---------------
// PUBLIC METHODS	
    // Set the string in the text object
    function Set($aTxt) {
	$this->t = $aTxt;
    }
	
    // Alias for Pos()
    function SetPos($aXAbsPos=0,$aYAbsPos=0,$aHAlign="left",$aVAlign="top") {
	//$this->Pos($aXAbsPos,$aYAbsPos,$aHAlign,$aVAlign);
	$this->x = $aXAbsPos;
	$this->y = $aYAbsPos;
	$this->halign = $aHAlign;
	$this->valign = $aVAlign;
    }

    function SetScalePos($aX,$aY) {
	$this->iScalePosX = $aX;
	$this->iScalePosY = $aY;
    }
	
    // Specify alignment for the text
    function Align($aHAlign,$aVAlign="top",$aParagraphAlign="") {
	$this->halign = $aHAlign;
	$this->valign = $aVAlign;
	if( $aParagraphAlign != "" )
	    $this->paragraph_align = $aParagraphAlign;
    }		
    
    // Alias
    function SetAlign($aHAlign,$aVAlign="top",$aParagraphAlign="") {
	$this->Align($aHAlign,$aVAlign,$aParagraphAlign);
    }

    // Specifies the alignment for a multi line text
    function ParagraphAlign($aAlign) {
	$this->paragraph_align = $aAlign;
    }

    // Specifies the alignment for a multi line text
    function SetParagraphAlign($aAlign) {
	$this->paragraph_align = $aAlign;
    }

    function SetShadow($aShadowColor='gray',$aShadowWidth=3) {
	$this->ishadowwidth=$aShadowWidth;
	$this->shadow=$aShadowColor;
	$this->boxed=true;
    }

    function SetWordWrap($aCol) {
	$this->iWordwrap = $aCol ;
    }
	
    // Specify that the text should be boxed. fcolor=frame color, bcolor=border color,
    // $shadow=drop shadow should be added around the text.
    function SetBox($aFrameColor=array(255,255,255),$aBorderColor=array(0,0,0),$aShadowColor=false,$aCornerRadius=4,$aShadowWidth=3) {
	if( $aFrameColor==false )
	    $this->boxed=false;
	else
	    $this->boxed=true;
	$this->fcolor=$aFrameColor;
	$this->bcolor=$aBorderColor;
	// For backwards compatibility when shadow was just true or false
	if( $aShadowColor === true )
	    $aShadowColor = 'gray';
	$this->shadow=$aShadowColor;
	$this->icornerradius=$aCornerRadius;
	$this->ishadowwidth=$aShadowWidth;
    }
	
    // Hide the text
    function Hide($aHide=true) {
	$this->hide=$aHide;
    }
	
    // This looks ugly since it's not a very orthogonal design 
    // but I added this "inverse" of Hide() to harmonize
    // with some classes which I designed more recently (especially) 
    // jpgraph_gantt
    function Show($aShow=true) {
	$this->hide=!$aShow;
    }
	
    // Specify font
    function SetFont($aFamily,$aStyle=FS_NORMAL,$aSize=10) {
	$this->font_family=$aFamily;
	$this->font_style=$aStyle;
	$this->font_size=$aSize;
    }
			
    // Center the text between $left and $right coordinates
    function Center($aLeft,$aRight,$aYAbsPos=false) {
	$this->x = $aLeft + ($aRight-$aLeft	)/2;
	$this->halign = "center";
	if( is_numeric($aYAbsPos) )
	    $this->y = $aYAbsPos;		
    }
	
    // Set text color
    function SetColor($aColor) {
	$this->color = $aColor;
    }
	
    function SetAngle($aAngle) {
	$this->SetOrientation($aAngle);
    }
	
    // Orientation of text. Note only TTF fonts can have an arbitrary angle
    function SetOrientation($aDirection=0) {
	if( is_numeric($aDirection) )
	    $this->dir=$aDirection;	
	elseif( $aDirection=="h" )
	    $this->dir = 0;
	elseif( $aDirection=="v" )
	    $this->dir = 90;
	else JpGraphError::RaiseL(25051);//(" Invalid direction specified for text.");
    }
	
    // Total width of text
    function GetWidth($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$w = $aImg->GetTextWidth($this->t,$this->dir);
	return $w;	
    }
	
    // Hight of font
    function GetFontHeight($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$h = $aImg->GetFontHeight();
	return $h;

    }

    function GetTextHeight($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);	
	$h = $aImg->GetTextHeight($this->t,$this->dir);
	return $h;
    }

    function GetHeight($aImg) {
	// Synonym for GetTextHeight()
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);	
	$h = $aImg->GetTextHeight($this->t,$this->dir);
	return $h;
    }

    // Set the margin which will be interpretated differently depending
    // on the context.
    function SetMargin($aMarg) {
	$this->margin = $aMarg;
    }

    function StrokeWithScale($aImg,$axscale,$ayscale) {
	if( $this->iScalePosX === null ||
	    $this->iScalePosY === null ) {
	    $this->Stroke($aImg);
	}
	else {
	    $this->Stroke($aImg,
			  round($axscale->Translate($this->iScalePosX)),
			  round($ayscale->Translate($this->iScalePosY)));
	}
    }

    function SetCSIMTarget($aURITarget,$aAlt='',$aWinTarget='') {
	$this->iCSIMtarget = $aURITarget;
	$this->iCSIMalt = $aAlt;
	$this->iCSIMWinTarget = $aWinTarget;
    }

    function GetCSIMareas() {
	if( $this->iCSIMtarget !== '' ) 
	    return $this->iCSIMarea;
	else
	    return '';
    }

    // Display text in image
    function Stroke($aImg,$x=null,$y=null) {

	if( !empty($x) ) $this->x = round($x);
	if( !empty($y) ) $this->y = round($y);

	// Insert newlines
	if( $this->iWordwrap > 0 ) {
	    $this->t = wordwrap($this->t,$this->iWordwrap,"\n");
	}

	// If position been given as a fraction of the image size
	// calculate the absolute position
	if( $this->x < 1 && $this->x > 0 ) $this->x *= $aImg->width;
	if( $this->y < 1 && $this->y > 0 ) $this->y *= $aImg->height;

	$aImg->PushColor($this->color);	
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$aImg->SetTextAlign($this->halign,$this->valign);
	if( $this->boxed ) {
	    if( $this->fcolor=="nofill" ) 
		$this->fcolor=false;		
	    $aImg->SetLineWeight(1);
	    $bbox = $aImg->StrokeBoxedText($this->x,$this->y,$this->t,
				   $this->dir,$this->fcolor,$this->bcolor,$this->shadow,
				   $this->paragraph_align,5,5,$this->icornerradius,
				   $this->ishadowwidth);
	}
	else {
	    $bbox = $aImg->StrokeText($this->x,$this->y,$this->t,$this->dir,$this->paragraph_align);
	}

	// Create CSIM targets
	$coords = $bbox[0].','.$bbox[1].','.$bbox[2].','.$bbox[3].','.$bbox[4].','.$bbox[5].','.$bbox[6].','.$bbox[7];
	$this->iCSIMarea = "<area shape=\"poly\" coords=\"$coords\" href=\"".htmlentities($this->iCSIMtarget)."\" ";
	if( trim($this->iCSIMalt) != '' ) {
	    $this->iCSIMarea .= " alt=\"".$this->iCSIMalt."\" "; 
	    $this->iCSIMarea .= " title=\"".$this->iCSIMalt."\" ";
	}
	if( trim($this->iCSIMWinTarget) != '' ) {
	    $this->iCSIMarea .= " target=\"".$this->iCSIMWinTarget."\" "; 
	}
	$this->iCSIMarea .= " />\n";

	$aImg->PopColor($this->color);	

    }
} // Class


?>
