<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

//=======================================================================
// File:        JPGRAPH.PHP
// Description: PHP Graph Plotting library. Base module.
// Created:     2001-01-08
// Ver:         $Id: jpgraph.php 1924 2010-01-11 14:03:26Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================

require_once 'jpg-config.inc.php';
//require_once('jpgraph_gradient.php'); *
//require_once('jpgraph_errhandler.inc.php');*
require_once 'jpgraph_ttf.inc.php';
//require_once('jpgraph_rgb.inc.php');*
//require_once('jpgraph_text.inc.php');*
//require_once('jpgraph_legend.inc.php');*
//require_once('jpgraph_theme.inc.php');*
//require_once('gd_image.inc.php');*

// Line styles
define('LINESTYLE_SOLID', 1);
define('LINESTYLE_DOTTED', 2);
define('LINESTYLE_DASHED', 3);
define('LINESTYLE_LONGDASH', 4);

// The DEFAULT_GFORMAT sets the default graphic encoding format, i.e.
// PNG, JPG or GIF depending on what is installed on the target system
// in that order.
if (!DEFINED("DEFAULT_GFORMAT")) {
    define("DEFAULT_GFORMAT", "auto");
}

require_once 'imageSmoothArc.php';

// Styles for gradient color fill
define("GRAD_VER", 1);
define("GRAD_VERT", 1);
define("GRAD_HOR", 2);
define("GRAD_MIDHOR", 3);
define("GRAD_MIDVER", 4);
define("GRAD_CENTER", 5);
define("GRAD_WIDE_MIDVER", 6);
define("GRAD_WIDE_MIDHOR", 7);
define("GRAD_LEFT_REFLECTION", 8);
define("GRAD_RIGHT_REFLECTION", 9);
define("GRAD_RAISED_PANEL", 10);
define("GRAD_DIAGONAL", 11);
DEFINE('_DEFAULT_LPM_SIZE', 8); // Default Legend Plot Mark size

// Version info
define('JPG_VERSION', '3.5.0b1');

// Minimum required PHP version
define('MIN_PHPVERSION', '5.1.0');

// Special file name to indicate that we only want to calc
// the image map in the call to Graph::Stroke() used
// internally from the GetHTMLCSIM() method.
define('_CSIM_SPECIALFILE', '_csim_special_');

// HTTP GET argument that is used with image map
// to indicate to the script to just generate the image
// and not the full CSIM HTML page.
define('_CSIM_DISPLAY', '_jpg_csimd');

// Special filename for Graph::Stroke(). If this filename is given
// then the image will NOT be streamed to browser of file. Instead the
// Stroke call will return the handler for the created GD image.
define('_IMG_HANDLER', '__handle');

// Special filename for Graph::Stroke(). If this filename is given
// the image will be stroked to a file with a name based on the script name.
define('_IMG_AUTO', 'auto');

// Tick density
define("TICKD_DENSE", 1);
define("TICKD_NORMAL", 2);
define("TICKD_SPARSE", 3);
define("TICKD_VERYSPARSE", 4);

// Side for ticks and labels.
define("SIDE_LEFT", -1);
define("SIDE_RIGHT", 1);
define("SIDE_DOWN", -1);
define("SIDE_BOTTOM", -1);
define("SIDE_UP", 1);
define("SIDE_TOP", 1);

// Legend type stacked vertical or horizontal
define("LEGEND_VERT", 0);
define("LEGEND_HOR", 1);

// Mark types for plot marks
define("MARK_SQUARE", 1);
define("MARK_UTRIANGLE", 2);
define("MARK_DTRIANGLE", 3);
define("MARK_DIAMOND", 4);
define("MARK_CIRCLE", 5);
define("MARK_FILLEDCIRCLE", 6);
define("MARK_CROSS", 7);
define("MARK_STAR", 8);
define("MARK_X", 9);
define("MARK_LEFTTRIANGLE", 10);
define("MARK_RIGHTTRIANGLE", 11);
define("MARK_FLASH", 12);
define("MARK_IMG", 13);
define("MARK_FLAG1", 14);
define("MARK_FLAG2", 15);
define("MARK_FLAG3", 16);
define("MARK_FLAG4", 17);

// Builtin images
define("MARK_IMG_PUSHPIN", 50);
define("MARK_IMG_SPUSHPIN", 50);
define("MARK_IMG_LPUSHPIN", 51);
define("MARK_IMG_DIAMOND", 52);
define("MARK_IMG_SQUARE", 53);
define("MARK_IMG_STAR", 54);
define("MARK_IMG_BALL", 55);
define("MARK_IMG_SBALL", 55);
define("MARK_IMG_MBALL", 56);
define("MARK_IMG_LBALL", 57);
define("MARK_IMG_BEVEL", 58);

// Inline defines
define("INLINE_YES", 1);
define("INLINE_NO", 0);

// Format for background images
define("BGIMG_FILLPLOT", 1);
define("BGIMG_FILLFRAME", 2);
define("BGIMG_COPY", 3);
define("BGIMG_CENTER", 4);
define("BGIMG_FREE", 5);

// Depth of objects
define("DEPTH_BACK", 0);
define("DEPTH_FRONT", 1);

// Direction
define("VERTICAL", 1);
define("HORIZONTAL", 0);

// Axis styles for scientific style axis
define('AXSTYLE_SIMPLE', 1);
define('AXSTYLE_BOXIN', 2);
define('AXSTYLE_BOXOUT', 3);
define('AXSTYLE_YBOXIN', 4);
define('AXSTYLE_YBOXOUT', 5);

// Style for title backgrounds
define('TITLEBKG_STYLE1', 1);
define('TITLEBKG_STYLE2', 2);
define('TITLEBKG_STYLE3', 3);
define('TITLEBKG_FRAME_NONE', 0);
define('TITLEBKG_FRAME_FULL', 1);
define('TITLEBKG_FRAME_BOTTOM', 2);
define('TITLEBKG_FRAME_BEVEL', 3);
define('TITLEBKG_FILLSTYLE_HSTRIPED', 1);
define('TITLEBKG_FILLSTYLE_VSTRIPED', 2);
define('TITLEBKG_FILLSTYLE_SOLID', 3);

// Styles for axis labels background
define('LABELBKG_NONE', 0);
define('LABELBKG_XAXIS', 1);
define('LABELBKG_YAXIS', 2);
define('LABELBKG_XAXISFULL', 3);
define('LABELBKG_YAXISFULL', 4);
define('LABELBKG_XYFULL', 5);
define('LABELBKG_XY', 6);

// Style for background gradient fills
define('BGRAD_FRAME', 1);
define('BGRAD_MARGIN', 2);
define('BGRAD_PLOT', 3);

// Width of tab titles
define('TABTITLE_WIDTHFIT', 0);
define('TABTITLE_WIDTHFULL', -1);

// Defines for 3D skew directions
define('SKEW3D_UP', 0);
define('SKEW3D_DOWN', 1);
define('SKEW3D_LEFT', 2);
define('SKEW3D_RIGHT', 3);

// For internal use only
define("_JPG_DEBUG", false);
define("_FORCE_IMGTOFILE", false);
define("_FORCE_IMGDIR", '/tmp/jpgimg/');

//
// Automatic settings of path for cache and font directory
// if they have not been previously specified
//
if (USE_CACHE) {
    if (!defined('CACHE_DIR')) {
        if (strstr(PHP_OS, 'WIN')) {
            if (empty($_SERVER['TEMP'])) {
                $t = new ErrMsgText();
                $msg = $t->Get(11, $file, $lineno);
                die($msg);
            } else {
                define('CACHE_DIR', $_SERVER['TEMP'] . '/');
            }
        } else {
            define('CACHE_DIR', '/tmp/jpgraph_cache/');
        }
    }
} elseif (!defined('CACHE_DIR')) {
    define('CACHE_DIR', '');
}

//
// Setup path for western/latin TTF fonts
//
if (!defined('TTF_DIR')) {
    if (strstr(PHP_OS, 'WIN')) {
        $sroot = getenv('SystemRoot');
        if (empty($sroot)) {
            $t = new ErrMsgText();
            $msg = $t->Get(12, $file, $lineno);
            die($msg);
        } else {
            define('TTF_DIR', $sroot . '/fonts/');
        }
    } else {
        define('TTF_DIR', '/usr/share/fonts/truetype/');
    }
}

//
// Setup path for MultiByte TTF fonts (japanese, chinese etc.)
//
if (!defined('MBTTF_DIR')) {
    if (strstr(PHP_OS, 'WIN')) {
        $sroot = getenv('SystemRoot');
        if (empty($sroot)) {
            $t = new ErrMsgText();
            $msg = $t->Get(12, $file, $lineno);
            die($msg);
        } else {
            define('MBTTF_DIR', $sroot . '/fonts/');
        }
    } else {
        define('MBTTF_DIR', '/usr/share/fonts/truetype/');
    }
}

//
// Check minimum PHP version
//
function CheckPHPVersion($aMinVersion)
{
    return version_compare(PHP_VERSION, $aMinVersion) >= 0;
}

//
// Make sure PHP version is high enough
//
if (!CheckPHPVersion(MIN_PHPVERSION)) {
    Amenadiel\JpGraph\Util\JpGraphError::RaiseL(13, PHP_VERSION, MIN_PHPVERSION);
    die();
}

//
// Make GD sanity check
//
if (!function_exists("imagetypes") || !function_exists('imagecreatefromstring')) {
    Amenadiel\JpGraph\Util\JpGraphError::RaiseL(25001);
    //("This PHP installation is not configured with the GD library. Please recompile PHP with GD support to run JpGraph. (Neither function imagetypes() nor imagecreatefromstring() does exist)");
}

//
// Setup PHP error handler
//
function _phpErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    // Respect current error level
    if ($errno & error_reporting()) {
        Amenadiel\JpGraph\Util\JpGraphError::RaiseL(25003, basename($filename), $linenum, $errmsg);
    }
}

if (INSTALL_PHP_ERR_HANDLER) {
    set_error_handler("_phpErrorHandler");
}

//
// Check if there were any warnings, perhaps some wrong includes by the user. In this
// case we raise it immediately since otherwise the image will not show and makes
// debugging difficult. This is controlled by the user setting CATCH_PHPERRMSG
//
if (isset($GLOBALS['php_errormsg']) && CATCH_PHPERRMSG && !preg_match('/|Deprecated|/i', $GLOBALS['php_errormsg'])) {
    Amenadiel\JpGraph\Util\JpGraphError::RaiseL(25004, $GLOBALS['php_errormsg']);
}

// Useful mathematical function
function sign($a)
{return $a >= 0 ? 1 : -1;}

//
// Utility function to generate an image name based on the filename we
// are running from and assuming we use auto detection of graphic format
// (top level), i.e it is safe to call this function
// from a script that uses JpGraph
//
function GenImgName()
{
    // Determine what format we should use when we save the images
    $supported = imagetypes();
    if ($supported & IMG_PNG) {
        $img_format = "png";
    } elseif ($supported & IMG_GIF) {
        $img_format = "gif";
    } elseif ($supported & IMG_JPG) {
        $img_format = "jpeg";
    } elseif ($supported & IMG_WBMP) {
        $img_format = "wbmp";
    } elseif ($supported & IMG_XPM) {
        $img_format = "xpm";
    }

    if (!isset($_SERVER['PHP_SELF'])) {
        Amenadiel\JpGraph\Util\JpGraphError::RaiseL(25005);
        //(" Can't access PHP_SELF, PHP global variable. You can't run PHP from command line if you want to use the 'auto' naming of cache or image files.");
    }
    $fname = basename($_SERVER['PHP_SELF']);
    if (!empty($_SERVER['QUERY_STRING'])) {
        $q = @$_SERVER['QUERY_STRING'];
        $fname .= '_' . preg_replace("/\W/", "_", $q) . '.' . $img_format;
    } else {
        $fname = substr($fname, 0, strlen($fname) - 4) . '.' . $img_format;
    }
    return $fname;
}

global $gDateLocale;
// Global object handlers
$gDateLocale = new Amenadiel\JpGraph\Util\DateLocale();
$gJpgDateLocale = new Amenadiel\JpGraph\Util\DateLocale();

// <EOF>
