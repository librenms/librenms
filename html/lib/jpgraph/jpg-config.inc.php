<?php
//=======================================================================
// File:        JPG-CONFIG.INC
// Description: Configuration file for JpGraph library
// Created:     2004-03-27
// Ver:         $Id: jpg-config.inc.php 1871 2009-09-29 05:56:39Z ljp $
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
//   MBTTF_DIR /usr/share/fonts/truetype/
//
// WINDOWS:
//   CACHE_DIR $SERVER_TEMP/jpgraph_cache/
//   TTF_DIR   $SERVER_SYSTEMROOT/fonts/
//   MBTTF_DIR $SERVER_SYSTEMROOT/fonts/
//
//------------------------------------------------------------------------
// define('CACHE_DIR','/tmp/jpgraph_cache/');
// define('TTF_DIR','/usr/share/fonts/truetype/');
// define('MBTTF_DIR','/usr/share/fonts/truetype/');

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
define('CSIMCACHE_DIR','csimcache/');
define('CSIMCACHE_HTTP_DIR','csimcache/');

//------------------------------------------------------------------------
// Various JpGraph Settings. Adjust accordingly to your
// preferences. Note that cache functionality is turned off by
// default (Enable by setting USE_CACHE to true)
//------------------------------------------------------------------------

// Deafult locale for error messages.
// This defaults to English = 'en'
define('DEFAULT_ERR_LOCALE','en');

// Deafult graphic format set to 'auto' which will automatically
// choose the best available format in the order png,gif,jpeg
// (The supported format depends on what your PHP installation supports)
define('DEFAULT_GFORMAT','auto');

// Should the cache be used at all? By setting this to false no
// files will be generated in the cache directory.
// The difference from READ_CACHE being that setting READ_CACHE to
// false will still create the image in the cache directory
// just not use it. By setting USE_CACHE=false no files will even
// be generated in the cache directory.
define('USE_CACHE',false);

// Should we try to find an image in the cache before generating it?
// Set this define to false to bypass the reading of the cache and always
// regenerate the image. Note that even if reading the cache is
// disabled the cached will still be updated with the newly generated
// image. Set also 'USE_CACHE' below.
define('READ_CACHE',true);

// Determine if the error handler should be image based or purely
// text based. Image based makes it easier since the script will
// always return an image even in case of errors.
define('USE_IMAGE_ERROR_HANDLER',true);

// Should the library examine the global php_errmsg string and convert
// any error in it to a graphical representation. This is handy for the
// occasions when, for example, header files cannot be found and this results
// in the graph not being created and just a 'red-cross' image would be seen.
// This should be turned off for a production site.
define('CATCH_PHPERRMSG',true);

// Determine if the library should also setup the default PHP
// error handler to generate a graphic error mesage. This is useful
// during development to be able to see the error message as an image
// instead as a 'red-cross' in a page where an image is expected.
define('INSTALL_PHP_ERR_HANDLER',false);

// Should usage of deprecated functions and parameters give a fatal error?
// (Useful to check if code is future proof.)
define('ERR_DEPRECATED',true);

// The builtin GD function imagettfbbox() fuction which calculates the bounding box for
// text using TTF fonts is buggy. By setting this define to true the library
// uses its own compensation for this bug. However this will give a
// slightly different visual apparance than not using this compensation.
// Enabling this compensation will in general give text a bit more space to more
// truly reflect the actual bounding box which is a bit larger than what the
// GD function thinks.
define('USE_LIBRARY_IMAGETTFBBOX',true);

//------------------------------------------------------------------------
// The following constants should rarely have to be changed !
//------------------------------------------------------------------------

// What group should the cached file belong to
// (Set to '' will give the default group for the 'PHP-user')
// Please note that the Apache user must be a member of the
// specified group since otherwise it is impossible for Apache
// to set the specified group.
define('CACHE_FILE_GROUP','www');

// What permissions should the cached file have
// (Set to '' will give the default persmissions for the 'PHP-user')
define('CACHE_FILE_MOD',0664);

?>
