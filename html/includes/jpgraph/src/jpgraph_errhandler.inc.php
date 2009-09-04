<?php
//=======================================================================
// File:	JPGRAPH_ERRHANDLER.PHP
// Description:	Error handler class together with handling of localized
//		error messages. All localized error messages are stored
//		in a separate file under the "lang/" subdirectory.
// Created: 	2006-09-24
// Ver:		$Id: jpgraph_errhandler.inc.php 973 2008-03-09 15:29:44Z ljp $
//
// Copyright 2006 (c) Aditus Consulting. All rights reserved.
//========================================================================


GLOBAL $__jpg_err_locale ;
$__jpg_err_locale = DEFAULT_ERR_LOCALE;

class ErrMsgText {
    private $lt=NULL;
    function ErrMsgText() {
	GLOBAL $__jpg_err_locale;
	$file = 'lang/'.$__jpg_err_locale.'.inc.php';

	// If the chosen locale doesn't exist try english
	if( !file_exists(dirname(__FILE__).'/'.$file) ) {
	    $__jpg_err_locale = 'en';
	}

	$file = 'lang/'.$__jpg_err_locale.'.inc.php';
	if( !file_exists(dirname(__FILE__).'/'.$file) ) {
	    die('Chosen locale file ("'.$file.'") for error messages does not exist or is not readable for the PHP process. Please make sure that the file exists and that the file permissions are such that the PHP process is allowed to read this file.');
	}
	require($file);
	$this->lt = $_jpg_messages;
    }

    function Get($errnbr,$a1=null,$a2=null,$a3=null,$a4=null,$a5=null) {
	GLOBAL $__jpg_err_locale;
	if( !isset($this->lt[$errnbr]) ) {
	    return 'Internal error: The specified error message ('.$errnbr.') does not exist in the chosen locale ('.$__jpg_err_locale.')';
	}
	$ea = $this->lt[$errnbr];
	$j=0;
	if( $a1 !== null ) {
	    $argv[$j++] = $a1;
	    if( $a2 !== null ) {
		$argv[$j++] = $a2;
		if( $a3 !== null ) {
		    $argv[$j++] = $a3;
		    if( $a4 !== null ) {
			$argv[$j++] = $a4;
			if( $a5 !== null ) {
			    $argv[$j++] = $a5;
			}
		    }
		}
	    }
	}
	$numargs = $j; 
	if( $ea[1] != $numargs ) {
	    // Error message argument count do not match.
	    // Just return the error message without arguments.
	    return $ea[0];
	}
	switch( $numargs ) {
	    case 1:
		$msg = sprintf($ea[0],$argv[0]);
		break;
	    case 2:
		$msg = sprintf($ea[0],$argv[0],$argv[1]);
		break;
	    case 3:
		$msg = sprintf($ea[0],$argv[0],$argv[1],$argv[2]);
		break;
	    case 4:
		$msg = sprintf($ea[0],$argv[0],$argv[1],$argv[2],$argv[3]);
		break;
	    case 5:
		$msg = sprintf($ea[0],$argv[0],$argv[1],$argv[2],$argv[3],$argv[4]);
		break;
	    case 0:
	    default:
		$msg = sprintf($ea[0]);
		break;
	}
	return $msg;
    }
}

//
// A wrapper class that is used to access the specified error object
// (to hide the global error parameter and avoid having a GLOBAL directive
// in all methods.
//
class JpGraphError {
    private static $__jpg_err;
    public static function Install($aErrObject) {
	self::$__jpg_err = new $aErrObject;
    }
    public static function Raise($aMsg,$aHalt=true){
	self::$__jpg_err->Raise($aMsg,$aHalt);
    }
    public static function SetErrLocale($aLoc) {
	GLOBAL $__jpg_err_locale ;
	$__jpg_err_locale = $aLoc;
    }
    public static function RaiseL($errnbr,$a1=null,$a2=null,$a3=null,$a4=null,$a5=null) {
	$t = new ErrMsgText();
	$msg = $t->Get($errnbr,$a1,$a2,$a3,$a4,$a5);
	self::$__jpg_err->Raise($msg);
    }
}

//
// First of all set up a default error handler
//

//=============================================================
// The default trivial text error handler.
//=============================================================
class JpGraphErrObject {

    protected $iTitle = "JpGraph Error";
    protected $iDest = false;


    function JpGraphErrObject() {
	// Empty. Reserved for future use
    }

    function SetTitle($aTitle) {
	$this->iTitle = $aTitle;
    }

    function SetStrokeDest($aDest) { 
	$this->iDest = $aDest; 
    }

    // If aHalt is true then execution can't continue. Typical used for fatal errors
    function Raise($aMsg,$aHalt=true) {
	$aMsg = $this->iTitle.' '.$aMsg;
	if ($this->iDest) {
	    $f = @fopen($this->iDest,'a');
	    if( $f ) {
		@fwrite($f,$aMsg);
		@fclose($f);
	    }
	}
	else {
	    echo $aMsg;
	}
	if( $aHalt )
	    die();
    }
}

//==============================================================
// An image based error handler
//==============================================================
class JpGraphErrObjectImg extends JpGraphErrObject {

    function Raise($aMsg,$aHalt=true) {
	$img_iconerror = 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAAaV'.
	    'BMVEX//////2Xy8mLl5V/Z2VvMzFi/v1WyslKlpU+ZmUyMjEh/'.
	    'f0VyckJlZT9YWDxMTDjAwMDy8sLl5bnY2K/MzKW/v5yyspKlpY'.
	    'iYmH+MjHY/PzV/f2xycmJlZVlZWU9MTEXY2Ms/PzwyMjLFTjea'.
	    'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACx'.
	    'IAAAsSAdLdfvwAAAAHdElNRQfTBgISOCqusfs5AAABLUlEQVR4'.
	    '2tWV3XKCMBBGWfkranCIVClKLd/7P2Q3QsgCxjDTq+6FE2cPH+'.
	    'xJ0Ogn2lQbsT+Wrs+buAZAV4W5T6Bs0YXBBwpKgEuIu+JERAX6'.
	    'wM2rHjmDdEITmsQEEmWADgZm6rAjhXsoMGY9B/NZBwJzBvn+e3'.
	    'wHntCAJdGu9SviwIwoZVDxPB9+Rc0TSEbQr0j3SA1gwdSn6Db0'.
	    '6Tm1KfV6yzWGQO7zdpvyKLKBDmRFjzeB3LYgK7r6A/noDAfjtS'.
	    'IXaIzbJSv6WgUebTMV4EoRB8a2mQiQjgtF91HdKDKZ1gtFtQjk'.
	    'YcWaR5OKOhkYt+ZsTFdJRfPAApOpQYJTNHvCRSJR6SJngQadfc'.
	    'vd69OLMddVOPCGVnmrFD8bVYd3JXfxXPtLR/+mtv59/ALWiiMx'.
	    'qL72fwAAAABJRU5ErkJggg==' ;

	if( function_exists("imagetypes") )
	    $supported = imagetypes();
	else
	    $supported = 0;

	if( !function_exists('imagecreatefromstring') )
	    $supported = 0;

	if( ob_get_length() || headers_sent() || !($supported & IMG_PNG) ) {
	    // Special case for headers already sent or that the installation doesn't support
	    // the PNG format (which the error icon is encoded in). 
	    // Dont return an image since it can't be displayed
	    die($this->iTitle.' '.$aMsg);		
	}

	$aMsg = wordwrap($aMsg,55);
	$lines = substr_count($aMsg,"\n");

	// Create the error icon GD
	$erricon = Image::CreateFromString(base64_decode($img_iconerror));   

	// Create an image that contains the error text.
	$w=400; 	
	$h=100 + 15*max(0,$lines-3);

	$img = new Image($w,$h);


	// Drop shadow
	$img->SetColor("gray");
	$img->FilledRectangle(5,5,$w-1,$h-1,10);
	$img->SetColor("gray:0.7");
	$img->FilledRectangle(5,5,$w-3,$h-3,10);
	
	// Window background
	$img->SetColor("lightblue");
	$img->FilledRectangle(1,1,$w-5,$h-5);
	$img->CopyCanvasH($img->img,$erricon,5,30,0,0,40,40);

	// Window border
	$img->SetColor("black");
	$img->Rectangle(1,1,$w-5,$h-5);
	$img->Rectangle(0,0,$w-4,$h-4);
	
	// Window top row
	$img->SetColor("darkred");
	for($y=3; $y < 18; $y += 2 ) 
	    $img->Line(1,$y,$w-6,$y);

	// "White shadow"
	$img->SetColor("white");

	// Left window edge
	$img->Line(2,2,2,$h-5);
	$img->Line(2,2,$w-6,2);

	// "Gray button shadow"
	$img->SetColor("darkgray");

	// Gray window shadow
	$img->Line(2,$h-6,$w-5,$h-6);
	$img->Line(3,$h-7,$w-5,$h-7);

	// Window title
	$m = floor($w/2-5);
	$l = 100;
	$img->SetColor("lightgray:1.3");
	$img->FilledRectangle($m-$l,2,$m+$l,16);

	// Stroke text
	$img->SetColor("darkred");
	$img->SetFont(FF_FONT2,FS_BOLD);
	$img->StrokeText($m-50,15,$this->iTitle);
	$img->SetColor("black");
	$img->SetFont(FF_FONT1,FS_NORMAL);
	$txt = new Text($aMsg,52,25);
	$txt->Align("left","top");
	$txt->Stroke($img);
	if ($this->iDest) {
           $img->Stream($this->iDest);
	} else {
	    $img->Headers();
	    $img->Stream();
	}
	if( $aHalt )
	    die();
    }
}


// Install the default error handler
if( USE_IMAGE_ERROR_HANDLER ) {
    JpGraphError::Install("JpGraphErrObjectImg");
}
else {
    JpGraphError::Install("JpGraphErrObject");
}


?>
