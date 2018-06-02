<?php namespace snmptrapmanager;

class MIBUpAutoload
{

    private static $bRegistered = false;

    private static function snmptrapmanagerReqin($sSubPath, $class)
    {
        preg_match("/.*\\\([^\\\]+)$/", $class, $m);
        $sFile = join(
            DIRECTORY_SEPARATOR,
            array(
            dirname(__FILE__),
            '..',
            $sSubPath,
            $m[1] . '.php')
        );

        if (is_file($sFile)) {
            include_once $sFile;
            return true;
        }
        return false;
    }

    // Ugly autoloader.
    public static function snmptrapmanagerAutoload($class)
    {
        if (self::snmptrapmanagerReqin('system', $class)) {
            return;
        }
        if (self::snmptrapmanagerReqin('controllers', $class)) {
            return;
        }
        if (self::snmptrapmanagerReqin('models', $class)) {
            return;
        }
    }

    public static function register()
    {
        if (!self::$bRegistered) {
            spl_autoload_register(
                __NAMESPACE__.'\MIBUpAutoload::snmptrapmanagerAutoload'
            );
            self::$bRegistered = true;
        }
    }
}
