<?php
//=======================================================================
// File:	JPG-CONFIG.INC
// Description:	Configuration file for JpGraph library
// Created: 	2004-03-27
// Ver:		$Id: jpg-config.inc.php 1091 2009-01-18 22:57:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================



//------------------------------------------------------------------------
// Directories for cache and font directory. 
//
// CACHE_DIR:
// The full absolute name of the directory to be used to store the
// cached image files. This directory will not be used if the USE_CACHE
// define (further down) is false. If you enable the cache please note that
// this directory MUST be readable and writable for the process running PHP.
// Must end with '/'
//
// TTF_DIR:
// Directory where TTF fonts can be found. Must end with '/'
//
// The default values used if these defines are left commented out are:
//
// UNIX:
//   CACHE_DIR /tmp/jpgraph_cache/
//   TTF_DIR   /usr/share/fonts/truetype/
//   MBTTF_DIR /usr/share/fonts/ja/TrueType/
//
// WINDOWS:
//   CACHE_DIR $SERVER_TEMP/jpgraph_cache/
//   TTF_DIR   $SERVER_SYSTEMROOT/fonts/
//   MBTTF_DIR $SERVER_SYSTEMROOT/fonts/
//
//------------------------------------------------------------------------
// define("CACHE_DIR","/tmp/jpgraph_cache/");
// define("TTF_DIR","/usr/share/fonts/truetype/");
// define("MBTTF_DIR","/usr/share/fonts/ja/TrueType/");

//-------------------------------------------------------------------------
// Cache directory specification for use with CSIM graphs that are
// using the cache.
// The directory must be the filesysystem name as seen by PHP
// and the 'http' version must be the same directory but as 
// seen by the HTTP server relative to the 'htdocs' ddirectory. 
// If a relative path is specified it is taken to be relative from where
// the image script is executed.
// Note: The default setting is to create a subdirectory in the 
// directory from where the image script is executed and store all files
// there. As ususal this directory must be writeable by the PHP process.
define("CSIMCACHE_DIR","csimcache/"); 
define("CSIMCACHE_HTTP_DIR","csimcache/");

//------------------------------------------------------------------------
// Defines for font setup
//------------------------------------------------------------------------

// Actual name of the TTF file used together with FF_CHINESE aka FF_BIG5
// This is the TTF file being used when the font family is specified as
// either FF_CHINESE or FF_BIG5
define('CHINESE_TTF_FONT','bkai00mp.ttf');

// Special unicode greek language support
define("LANGUAGE_GREEK",false);

// If you are setting this config to true the conversion of greek characters
// will assume that the input text is windows 1251 
define("GREEK_FROM_WINDOWS",false);

// Special unicode cyrillic language support
define("LANGUAGE_CYRILLIC",false);

// If you are setting this config to true the conversion
// will assume that the input text is windows 1251, if
// false it will assume koi8-r
define("CYRILLIC_FROM_WINDOWS",false);

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
define('MINCHO_TTF_FONT','ipam.ttf');
define('PMINCHO_TTF_FONT','ipamp.ttf');
define('GOTHIC_TTF_FONT','ipag.ttf');
define('PGOTHIC_TTF_FONT','ipagp.ttf');

// Assume that Japanese text have been entered in EUC-JP encoding.
// If this define is true then conversion from EUC-JP to UTF8 is done 
// automatically in the library using the mbstring module in PHP.
define('ASSUME_EUCJP_ENCODING',false);

//------------------------------------------------------------------------
// Various JpGraph Settings. Adjust accordingly to your
// preferences. Note that cache functionality is turned off by
// default (Enable by setting USE_CACHE to true)
//------------------------------------------------------------------------

// Deafult locale for error messages.
// This defaults to English = 'en'
define('DEFAULT_ERR_LOCALE','en');

// Deafult graphic format set to "auto" which will automatically
// choose the best available format in the order png,gif,jpeg
// (The supported format depends on what your PHP installation supports)
define("DEFAULT_GFORMAT","auto");

// Should the cache be used at all? By setting this to false no
// files will be generated in the cache directory.  
// The difference from READ_CACHE being that setting READ_CACHE to
// false will still create the image in the cache directory
// just not use it. By setting USE_CACHE=false no files will even
// be generated in the cache directory.
define("USE_CACHE",false);

// Should we try to find an image in the cache before generating it? 
// Set this define to false to bypass the reading of the cache and always
// regenerate the image. Note that even if reading the cache is 
// disabled the cached will still be updated with the newly generated
// image. Set also "USE_CACHE" below.
define("READ_CACHE",true);

// Determine if the error handler should be image based or purely
// text based. Image based makes it easier since the script will
// always return an image even in case of errors.
define("USE_IMAGE_ERROR_HANDLER",true);

// Should the library examin the global php_errmsg string and convert
// any error in it to a graphical representation. This is handy for the
// occasions when, for example, header files cannot be found and this results
// in the graph not being created and just a "red-cross" image would be seen.
// This should be turned off for a production site.
define("CATCH_PHPERRMSG",true);

// Determine if the library should also setup the default PHP
// error handler to generate a graphic error mesage. This is useful
// during development to be able to see the error message as an image
// instead as a "red-cross" in a page where an image is expected.
define("INSTALL_PHP_ERR_HANDLER",false);

// If the color palette is full should JpGraph try to allocate
// the closest match? If you plan on using background images or
// gradient fills it might be a good idea to enable this.
// If not you will otherwise get an error saying that the color palette is 
// exhausted. The drawback of using approximations is that the colors 
// might not be exactly what you specified. 
// Note1: This does only apply to paletted images, not truecolor 
// images since they don't have the limitations of maximum number
// of colors.
define("USE_APPROX_COLORS",true);

// Should usage of deprecated functions and parameters give a fatal error?
// (Useful to check if code is future proof.)
define("ERR_DEPRECATED",true);

// Should the time taken to generate each picture be branded to the lower
// left in corner in each generated image? Useful for performace measurements
// generating graphs
define("BRAND_TIMING",false);

// What format should be used for the timing string?
define("BRAND_TIME_FORMAT","(%01.3fs)");

//------------------------------------------------------------------------
// The following constants should rarely have to be changed !
//------------------------------------------------------------------------

// What group should the cached file belong to
// (Set to "" will give the default group for the "PHP-user")
// Please note that the Apache user must be a member of the
// specified group since otherwise it is impossible for Apache
// to set the specified group.
define("CACHE_FILE_GROUP","wwwadmin");

// What permissions should the cached file have
// (Set to "" will give the default persmissions for the "PHP-user")
define("CACHE_FILE_MOD",0664);

// Decide if we should use the bresenham circle algorithm or the
// built in Arc(). Bresenham gives better visual apperance of circles 
// but is more CPU intensive and slower then the built in Arc() function
// in GD. Turned off by default for speed
define("USE_BRESENHAM",false);

// Special file name to indicate that we only want to calc
// the image map in the call to Graph::Stroke() used
// internally from the GetHTMLCSIM() method.
define("_CSIM_SPECIALFILE","_csim_special_");

// HTTP GET argument that is used with image map
// to indicate to the script to just generate the image
// and not the full CSIM HTML page.
define("_CSIM_DISPLAY","_jpg_csimd");

// Special filename for Graph::Stroke(). If this filename is given
// then the image will NOT be streamed to browser of file. Instead the
// Stroke call will return the handler for the created GD image.
define("_IMG_HANDLER","__handle");


?>
