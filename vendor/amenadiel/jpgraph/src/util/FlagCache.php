<?php
namespace Amenadiel\JpGraph\Util;

// Keep a global flag cache to reduce memory usage
$_gFlagCache = array(
    1 => null,
    2 => null,
    3 => null,
    4 => null,
);
// Only supposed to b called as statics
class FlagCache
{
    public static function GetFlagImgByName($aSize, $aName)
    {
        global $_gFlagCache;
        require_once 'jpgraph_flags.php';
        if ($_gFlagCache[$aSize] === null) {
            $_gFlagCache[$aSize] = new FlagImages($aSize);
        }
        $f   = $_gFlagCache[$aSize];
        $idx = $f->GetIdxByName($aName, $aFullName);
        return $f->GetImgByIdx($idx);
    }
}
