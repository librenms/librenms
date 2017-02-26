<?php
namespace Amenadiel\JpGraph\Util;

//
// A wrapper class that is used to access the specified error object
// (to hide the global error parameter and avoid having a GLOBAL directive
// in all methods.
//
class JpGraphError
{
    private static $__iImgFlg = true;
    private static $__iLogFile = '';
    private static $__iTitle = 'JpGraph Error: ';
    public static function Raise($aMsg, $aHalt = true)
    {
        throw new JpGraphException($aMsg);
    }

    public static function SetErrLocale($aLoc)
    {
        global $__jpg_err_locale;
        $__jpg_err_locale = $aLoc;
    }

    public static function RaiseL($errnbr, $a1 = null, $a2 = null, $a3 = null, $a4 = null, $a5 = null)
    {
        throw new JpGraphExceptionL($errnbr, $a1, $a2, $a3, $a4, $a5);
    }

    public static function SetImageFlag($aFlg = true)
    {
        self::$__iImgFlg = $aFlg;
    }

    public static function GetImageFlag()
    {
        return self::$__iImgFlg;
    }

    public static function SetLogFile($aFile)
    {
        self::$__iLogFile = $aFile;
    }

    public static function GetLogFile()
    {
        return self::$__iLogFile;
    }

    public static function SetTitle($aTitle)
    {
        self::$__iTitle = $aTitle;
    }

    public static function GetTitle()
    {
        return self::$__iTitle;
    }
}
