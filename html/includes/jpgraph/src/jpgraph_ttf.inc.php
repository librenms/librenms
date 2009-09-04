<?php
//=======================================================================
// File:	jpgraph_ttf.inc.php
// Description:	Handling of TTF fonts
// Created: 	2006-11-19
// Ver:		$Id: jpgraph_ttf.inc.php 1091 2009-01-18 22:57:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

// TTF Font families
define("FF_COURIER",10);
define("FF_VERDANA",11);
define("FF_TIMES",12);
define("FF_COMIC",14);
define("FF_ARIAL",15);
define("FF_GEORGIA",16);
define("FF_TREBUCHE",17);

// Gnome Vera font
// Available from http://www.gnome.org/fonts/
define("FF_VERA",18);
define("FF_VERAMONO",19);
define("FF_VERASERIF",20);

// Chinese font
define("FF_SIMSUN",30);
define("FF_CHINESE",31);
define("FF_BIG5",31);

// Japanese font
define("FF_MINCHO",40);
define("FF_PMINCHO",41);
define("FF_GOTHIC",42);
define("FF_PGOTHIC",43);

// Hebrew fonts
define("FF_DAVID",44);
define("FF_MIRIAM",45);
define("FF_AHRON",46);

// Dejavu-fonts http://sourceforge.net/projects/dejavu
define("FF_DV_SANSSERIF",47);
define("FF_DV_SERIF",48);
define("FF_DV_SANSSERIFMONO",49);
define("FF_DV_SERIFCOND",50);
define("FF_DV_SANSSERIFCOND",51);

// Extra fonts
// Download fonts from 
// http://www.webfontlist.com
// http://www.webpagepublicity.com/free-fonts.html

define("FF_SPEEDO",71);		// This font is also known as Bauer (Used for gauge fascia)
define("FF_DIGITAL",72);	// Digital readout font
define("FF_COMPUTER",73);	// The classic computer font
define("FF_CALCULATOR",74);	// Triad font

define("FF_USERFONT",90);
define("FF_USERFONT1",90);
define("FF_USERFONT2",91);
define("FF_USERFONT3",92);

// Limits for fonts
define("_FIRST_FONT",10);
define("_LAST_FONT",99);

// TTF Font styles
define("FS_NORMAL",9001);
define("FS_BOLD",9002);
define("FS_ITALIC",9003);
define("FS_BOLDIT",9004);
define("FS_BOLDITALIC",9004);

//Definitions for internal font
define("FF_FONT0",1);
define("FF_FONT1",2);
define("FF_FONT2",4);

//=================================================================
// CLASS LanguageConv
// Description: 
// Converts various character encoding into proper
// UTF-8 depending on how the library have been configured and
// what font family is being used
//=================================================================
class LanguageConv {
    private $g2312 = null ;

    function Convert($aTxt,$aFF) {
	if( LANGUAGE_GREEK ) {
	    if( GREEK_FROM_WINDOWS ) {
		$unistring = LanguageConv::gr_win2uni($aTxt); 
	    } else  {
		$unistring = LanguageConv::gr_iso2uni($aTxt);
	    }
	    return $unistring;
	} elseif( LANGUAGE_CYRILLIC ) {
	    if( CYRILLIC_FROM_WINDOWS && (!defined('LANGUAGE_CHARSET') || stristr(LANGUAGE_CHARSET, 'windows-1251')) ) {
		$aTxt = convert_cyr_string($aTxt, "w", "k"); 
	    }
	    if( !defined('LANGUAGE_CHARSET') || stristr(LANGUAGE_CHARSET, 'koi8-r') || stristr(LANGUAGE_CHARSET, 'windows-1251')) {
		$isostring = convert_cyr_string($aTxt, "k", "i");
		$unistring = LanguageConv::iso2uni($isostring);
	    }
	    else {
		$unistring = $aTxt;
	    }
	    return $unistring;
	}
	elseif( $aFF === FF_SIMSUN ) {
	    // Do Chinese conversion
	    if( $this->g2312 == null ) {
		include_once 'jpgraph_gb2312.php' ;
		$this->g2312 = new GB2312toUTF8();
	    }
	    return $this->g2312->gb2utf8($aTxt);
	}
	elseif( $aFF === FF_CHINESE ) {
	    if( !function_exists('iconv') ) {
		JpGraphError::RaiseL(25006);
//('Usage of FF_CHINESE (FF_BIG5) font family requires that your PHP setup has the iconv() function. By default this is not compiled into PHP (needs the "--width-iconv" when configured).');
	    }
	    return iconv('BIG5','UTF-8',$aTxt);
	}
	elseif( ASSUME_EUCJP_ENCODING &&
		($aFF == FF_MINCHO || $aFF == FF_GOTHIC || $aFF == FF_PMINCHO || $aFF == FF_PGOTHIC) ) {
	    if( !function_exists('mb_convert_encoding') ) {
		JpGraphError::RaiseL(25127);
	    }
	    return mb_convert_encoding($aTxt, 'UTF-8','EUC-JP');
	}
	elseif( $aFF == FF_DAVID || $aFF == FF_MIRIAM || $aFF == FF_AHRON ) {
	    return LanguageConv::heb_iso2uni($aTxt);
	}
	else 
	    return $aTxt;
    }

    // Translate iso encoding to unicode
    public static function iso2uni ($isoline){
	$uniline='';
	for ($i=0; $i < strlen($isoline); $i++){
	    $thischar=substr($isoline,$i,1);
	    $charcode=ord($thischar);
	    $uniline.=($charcode>175) ? "&#" . (1040+($charcode-176)). ";" : $thischar;
	}
	return $uniline;
    }

    // Translate greek iso encoding to unicode
    public static function gr_iso2uni ($isoline) {
	$uniline='';
	for ($i=0; $i < strlen($isoline); $i++)	{
	    $thischar=substr($isoline,$i,1);
	    $charcode=ord($thischar);
	    $uniline.=($charcode>179 && $charcode!=183 && $charcode!=187 && $charcode!=189) ? "&#" . (900+($charcode-180)). ";" : $thischar;
	}
	return $uniline;
    }

    // Translate greek win encoding to unicode
    public static function gr_win2uni ($winline) {
	$uniline='';
	for ($i=0; $i < strlen($winline); $i++) {
	    $thischar=substr($winline,$i,1);
	    $charcode=ord($thischar);
	    if ($charcode==161 || $charcode==162) {
		$uniline.="&#" . (740+$charcode). ";";
	    }
	    else {
		$uniline.=(($charcode>183 && $charcode!=187 && $charcode!=189) || $charcode==180) ? "&#" . (900+($charcode-180)). ";" : $thischar;
	    }
	}
	return $uniline;
    }

    public static function heb_iso2uni($isoline) {
	$isoline = hebrev($isoline);
	$o = '';

	$n = strlen($isoline);
	for($i=0; $i < $n; $i++) {
	    $c=ord( substr($isoline,$i,1) );
	    $o .= ($c > 223) && ($c < 251) ? '&#'.(1264+$c).';' : chr($c);
	}
	return utf8_encode($o);
    }
}

//=============================================================
// CLASS TTF
// Description: Handle TTF font names and mapping and loading of 
//              font files
//=============================================================
class TTF {
    private $font_files,$style_names;

//---------------
// CONSTRUCTOR
    function TTF() {

	// String names for font styles to be used in error messages
	$this->style_names=array(FS_NORMAL	=>'normal',
				 FS_BOLD	=>'bold',
				 FS_ITALIC	=>'italic',
				 FS_BOLDITALIC	=>'bolditalic');

	// File names for available fonts
	$this->font_files=array(
	    FF_COURIER => array(FS_NORMAL	=>'cour.ttf', 
				FS_BOLD		=>'courbd.ttf', 
				FS_ITALIC	=>'couri.ttf', 
				FS_BOLDITALIC	=>'courbi.ttf' ),
	    FF_GEORGIA => array(FS_NORMAL	=>'georgia.ttf', 
				FS_BOLD		=>'georgiab.ttf', 
				FS_ITALIC	=>'georgiai.ttf', 
				FS_BOLDITALIC	=>'' ),
	    FF_TREBUCHE	=>array(FS_NORMAL	=>'trebuc.ttf', 
				FS_BOLD		=>'trebucbd.ttf',   
				FS_ITALIC	=>'trebucit.ttf', 
				FS_BOLDITALIC	=>'trebucbi.ttf' ),
	    FF_VERDANA 	=> array(FS_NORMAL	=>'verdana.ttf', 
				FS_BOLD		=>'verdanab.ttf',  
				FS_ITALIC	=>'verdanai.ttf', 
				FS_BOLDITALIC	=>'' ),
	    FF_TIMES =>   array(FS_NORMAL	=>'times.ttf',   
				FS_BOLD		=>'timesbd.ttf',   
				FS_ITALIC	=>'timesi.ttf',   
				FS_BOLDITALIC	=>'timesbi.ttf' ),
	    FF_COMIC =>   array(FS_NORMAL	=>'comic.ttf',   
				FS_BOLD		=>'comicbd.ttf',   
				FS_ITALIC	=>'',         
				FS_BOLDITALIC	=>'' ),
	    FF_ARIAL =>   array(FS_NORMAL	=>'arial.ttf',   
				FS_BOLD		=>'arialbd.ttf',   
				FS_ITALIC	=>'ariali.ttf',   
				FS_BOLDITALIC	=>'arialbi.ttf' ) ,
	    FF_VERA =>    array(FS_NORMAL	=>'Vera.ttf',   
				FS_BOLD		=>'VeraBd.ttf',   
				FS_ITALIC	=>'VeraIt.ttf',   
				FS_BOLDITALIC	=>'VeraBI.ttf' ),
	    FF_VERAMONO	=> array(FS_NORMAL	=>'VeraMono.ttf', 
				 FS_BOLD	=>'VeraMoBd.ttf', 
				 FS_ITALIC	=>'VeraMoIt.ttf', 
				 FS_BOLDITALIC	=>'VeraMoBI.ttf' ),
	    FF_VERASERIF=> array(FS_NORMAL	=>'VeraSe.ttf', 
				  FS_BOLD	=>'VeraSeBd.ttf', 
				  FS_ITALIC	=>'', 
				  FS_BOLDITALIC	=>'' ) ,

	    /* Chinese fonts */
	    FF_SIMSUN 	=>  array(FS_NORMAL	=>'simsun.ttc',  
				  FS_BOLD	=>'simhei.ttf',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),
	    FF_CHINESE 	=>   array(FS_NORMAL	=>CHINESE_TTF_FONT, 
				  FS_BOLD	=>'', 
				  FS_ITALIC	=>'', 
				  FS_BOLDITALIC	=>'' ),

	    /* Japanese fonts */
 	    FF_MINCHO 	=>  array(FS_NORMAL	=>MINCHO_TTF_FONT,  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),
 	    FF_PMINCHO 	=>  array(FS_NORMAL	=>PMINCHO_TTF_FONT,  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',  
				  FS_BOLDITALIC	=>'' ),    
 	    FF_GOTHIC  	=>  array(FS_NORMAL	=>GOTHIC_TTF_FONT,  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),    
 	    FF_PGOTHIC 	=>  array(FS_NORMAL	=>PGOTHIC_TTF_FONT,  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',  
				  FS_BOLDITALIC	=>'' ),    
 	    FF_MINCHO 	=>  array(FS_NORMAL	=>PMINCHO_TTF_FONT,  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',  
				  FS_BOLDITALIC	=>'' ),   

	    /* Hebrew fonts */
	    FF_DAVID 	=>  array(FS_NORMAL	=>'DAVIDNEW.TTF',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',  
				  FS_BOLDITALIC	=>'' ),   

	    FF_MIRIAM 	=>  array(FS_NORMAL	=>'MRIAMY.TTF',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',  
				  FS_BOLDITALIC	=>'' ),   

	    FF_AHRON 	=>  array(FS_NORMAL	=>'ahronbd.ttf',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',  
				  FS_BOLDITALIC	=>'' ),   

	    /* Misc fonts */
 	    FF_DIGITAL =>   array(FS_NORMAL	=>'DIGIRU__.TTF',  
				  FS_BOLD	=>'Digirtu_.ttf', 
				  FS_ITALIC	=>'Digir___.ttf', 
				  FS_BOLDITALIC	=>'DIGIRT__.TTF' ),   
 	    FF_SPEEDO =>    array(FS_NORMAL	=>'Speedo.ttf',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),   
 	    FF_COMPUTER  =>  array(FS_NORMAL	=>'COMPUTER.TTF',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),   
 	    FF_CALCULATOR => array(FS_NORMAL	=>'Triad_xs.ttf',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),  

	    /* Dejavu fonts */
 	    FF_DV_SANSSERIF => array(FS_NORMAL	=>'DejaVuSans.ttf',  
				  FS_BOLD	=>'DejaVuSans-Bold.ttf',   
				  FS_ITALIC	=>'DejaVuSans-Oblique.ttf',   
				  FS_BOLDITALIC	=>'DejaVuSans-BoldOblique.ttf' ),  

 	    FF_DV_SANSSERIFMONO => array(FS_NORMAL	=>'DejaVuSansMono.ttf',  
				  FS_BOLD	=>'DejaVuSansMono-Bold.ttf',   
				  FS_ITALIC	=>'DejaVuSansMono-Oblique.ttf',   
				  FS_BOLDITALIC	=>'DejaVuSansMono-BoldOblique.ttf' ),  

 	    FF_DV_SANSSERIFCOND => array(FS_NORMAL	=>'DejaVuSansCondensed.ttf',  
				  FS_BOLD	=>'DejaVuSansCondensed-Bold.ttf',   
				  FS_ITALIC	=>'DejaVuSansCondensed-Oblique.ttf',   
				  FS_BOLDITALIC	=>'DejaVuSansCondensed-BoldOblique.ttf' ),  

 	    FF_DV_SERIF => array(FS_NORMAL	=>'DejaVuSerif.ttf',  
				  FS_BOLD	=>'DejaVuSerif-Bold.ttf',   
				  FS_ITALIC	=>'DejaVuSerif-Italic.ttf',   
				  FS_BOLDITALIC	=>'DejaVuSerif-BoldItalic.ttf' ),  

 	    FF_DV_SERIFCOND => array(FS_NORMAL	=>'DejaVuSerifCondensed.ttf',  
				  FS_BOLD	=>'DejaVuSerifCondensed-Bold.ttf',   
				  FS_ITALIC	=>'DejaVuSerifCondensed-Italic.ttf',   
				  FS_BOLDITALIC	=>'DejaVuSerifCondensed-BoldItalic.ttf' ),  


	    /* User defined font */
 	    FF_USERFONT1 => array(FS_NORMAL	=>'',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),  

 	    FF_USERFONT2 => array(FS_NORMAL	=>'',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),  

 	    FF_USERFONT3 => array(FS_NORMAL	=>'',  
				  FS_BOLD	=>'',   
				  FS_ITALIC	=>'',   
				  FS_BOLDITALIC	=>'' ),  

 	    );
    }

//---------------
// PUBLIC METHODS	
    // Create the TTF file from the font specification
    function File($family,$style=FS_NORMAL) {
	$fam = @$this->font_files[$family];
	if( !$fam ) {
	    JpGraphError::RaiseL(25046,$family);//("Specified TTF font family (id=$family) is unknown or does not exist. Please note that TTF fonts are not distributed with JpGraph for copyright reasons. You can find the MS TTF WEB-fonts (arial, courier etc) for download at http://corefonts.sourceforge.net/");
	}
	$f = @$fam[$style];

	if( $f==='' )
	    JpGraphError::RaiseL(25047,$this->style_names[$style],$this->font_files[$family][FS_NORMAL]);//('Style "'.$this->style_names[$style].'" is not available for font family '.$this->font_files[$family][FS_NORMAL].'.');
	if( !$f ) {
	    JpGraphError::RaiseL(25048,$fam);//("Unknown font style specification [$fam].");
	}

	if ($family >= FF_MINCHO && $family <= FF_PGOTHIC) {
	    $f = MBTTF_DIR.$f;
	} else {
	    $f = TTF_DIR.$f;
	}

	if( file_exists($f) === false || is_readable($f) === false ) {
	    JpGraphError::RaiseL(25049,$f);//("Font file \"$f\" is not readable or does not exist.");
	}
	return $f;
    }

    function SetUserFont($aNormal,$aBold='',$aItalic='',$aBoldIt='') {
	$this->font_files[FF_USERFONT] = 
	    array(FS_NORMAL     => $aNormal,  
		  FS_BOLD	=> $aBold,   
		  FS_ITALIC	=> $aItalic,   
		  FS_BOLDITALIC	=> $aBoldIt ) ;
    }

    function SetUserFont1($aNormal,$aBold='',$aItalic='',$aBoldIt='') {
	$this->font_files[FF_USERFONT1] = 
	    array(FS_NORMAL     => $aNormal,  
		  FS_BOLD	=> $aBold,   
		  FS_ITALIC	=> $aItalic,   
		  FS_BOLDITALIC	=> $aBoldIt ) ;
    }

    function SetUserFont2($aNormal,$aBold='',$aItalic='',$aBoldIt='') {
	$this->font_files[FF_USERFONT2] = 
	    array(FS_NORMAL     => $aNormal,  
		  FS_BOLD	=> $aBold,   
		  FS_ITALIC	=> $aItalic,   
		  FS_BOLDITALIC	=> $aBoldIt ) ;
    }

    function SetUserFont3($aNormal,$aBold='',$aItalic='',$aBoldIt='') {
	$this->font_files[FF_USERFONT3] = 
	    array(FS_NORMAL     => $aNormal,  
		  FS_BOLD	=> $aBold,   
		  FS_ITALIC	=> $aItalic,   
		  FS_BOLDITALIC	=> $aBoldIt ) ;
    }

} // Class



?>
