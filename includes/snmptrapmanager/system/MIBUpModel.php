<?php namespace snmptrapmanager;

class MIBUpModel
{

    public static function load($sName)
    {
        $sName = '\snmptrapmanager\MIBUpModel' . $sName;
        return new $sName;
    }
}
