<?php

//=======================================================================
// File:        jpgraph_ttf.inc.php
// Description: Handling of TTF fonts
// Created:     2006-11-19
// Ver:         $Id: jpgraph_ttf.inc.php 1858 2009-09-28 14:39:51Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================

// TTF Font families
define("FF_COURIER", 10);
define("FF_VERDANA", 11);
define("FF_TIMES", 12);
define("FF_COMIC", 14);
define("FF_ARIAL", 15);
define("FF_GEORGIA", 16);
define("FF_TREBUCHE", 17);

// Gnome Vera font
// Available from http://www.gnome.org/fonts/
define("FF_VERA", 18);
define("FF_VERAMONO", 19);
define("FF_VERASERIF", 20);

// Chinese font
define("FF_SIMSUN", 30);
define("FF_CHINESE", 31);
define("FF_BIG5", 32);

// Japanese font
define("FF_MINCHO", 40);
define("FF_PMINCHO", 41);
define("FF_GOTHIC", 42);
define("FF_PGOTHIC", 43);

// Hebrew fonts
define("FF_DAVID", 44);
define("FF_MIRIAM", 45);
define("FF_AHRON", 46);

// Dejavu-fonts http://sourceforge.net/projects/dejavu
define("FF_DV_SANSSERIF", 47);
define("FF_DV_SERIF", 48);
define("FF_DV_SANSSERIFMONO", 49);
define("FF_DV_SERIFCOND", 50);
define("FF_DV_SANSSERIFCOND", 51);

// Extra fonts
// Download fonts from
// http://www.webfontlist.com
// http://www.webpagepublicity.com/free-fonts.html
// http://www.fontonic.com/fonts.asp?width=d&offset=120
// http://www.fontspace.com/category/famous

// define("FF_SPEEDO",71);  // This font is also known as Bauer (Used for development gauge fascia)
define("FF_DIGITAL", 72); // Digital readout font
define("FF_COMPUTER", 73); // The classic computer font
define("FF_CALCULATOR", 74); // Triad font

define("FF_USERFONT", 90);
define("FF_USERFONT1", 90);
define("FF_USERFONT2", 91);
define("FF_USERFONT3", 92);

// Limits for fonts
define("_FIRST_FONT", 10);
define("_LAST_FONT", 99);

// TTF Font styles
define("FS_NORMAL", 9001);
define("FS_BOLD", 9002);
define("FS_ITALIC", 9003);
define("FS_BOLDIT", 9004);
define("FS_BOLDITALIC", 9004);

//Definitions for internal font
define("FF_FONT0", 1);
define("FF_FONT1", 2);
define("FF_FONT2", 4);

//------------------------------------------------------------------------
// Defines for font setup
//------------------------------------------------------------------------

// Actual name of the TTF file used together with FF_CHINESE aka FF_BIG5
// This is the TTF file being used when the font family is specified as
// either FF_CHINESE or FF_BIG5
define('CHINESE_TTF_FONT', 'bkai00mp.ttf');

// Special unicode greek language support
define("LANGUAGE_GREEK", false);

// If you are setting this config to true the conversion of greek characters
// will assume that the input text is windows 1251
define("GREEK_FROM_WINDOWS", false);

// Special unicode cyrillic language support
define("LANGUAGE_CYRILLIC", false);

// If you are setting this config to true the conversion
// will assume that the input text is windows 1251, if
// false it will assume koi8-r
define("CYRILLIC_FROM_WINDOWS", false);

// The following constant is used to auto-detect
// whether cyrillic conversion is really necessary
// if enabled. Just replace 'windows-1251' with a variable
// containing the input character encoding string
// of your application calling jpgraph.
// A typical such string would be 'UTF-8' or 'utf-8'.
// The comparison is case-insensitive.
// If this charset is not a 'koi8-r' or 'windows-1251'
// derivate then no conversion is done.
//
// This constant can be very important in multi-user
// multi-language environments where a cyrillic conversion
// could be needed for some cyrillic people
// and resulting in just erraneous conversions
// for not-cyrillic language based people.
//
// Example: In the free project management
// software dotproject.net $locale_char_set is dynamically
// set by the language environment the user has chosen.
//
// Usage: define('LANGUAGE_CHARSET', $locale_char_set);
//
// where $locale_char_set is a GLOBAL (string) variable
// from the application including JpGraph.
//
define('LANGUAGE_CHARSET', null);

// Japanese TrueType font used with FF_MINCHO, FF_PMINCHO, FF_GOTHIC, FF_PGOTHIC
// Standard fonts from Infomation-technology Promotion Agency (IPA)
// See http://mix-mplus-ipa.sourceforge.jp/
define('MINCHO_TTF_FONT', 'ipam.ttf');
define('PMINCHO_TTF_FONT', 'ipamp.ttf');
define('GOTHIC_TTF_FONT', 'ipag.ttf');
define('PGOTHIC_TTF_FONT', 'ipagp.ttf');

// Assume that Japanese text have been entered in EUC-JP encoding.
// If this define is true then conversion from EUC-JP to UTF8 is done
// automatically in the library using the mbstring module in PHP.
define('ASSUME_EUCJP_ENCODING', false);

// Default font family
define('FF_DEFAULT', FF_DV_SANSSERIF);
