<?php
namespace Amenadiel\JpGraph\Util;

// Provide a deterministic list of new colors whenever the getColor() method
// is called. Used to automatically set colors of plots.
class ColorFactory
{
    private static $iIdx       = 0;
    private static $iColorList = array(
        'black',
        'blue',
        'orange',
        'darkgreen',
        'red',
        'AntiqueWhite3',
        'aquamarine3',
        'azure4',
        'brown',
        'cadetblue3',
        'chartreuse4',
        'chocolate',
        'darkblue',
        'darkgoldenrod3',
        'darkorchid3',
        'darksalmon',
        'darkseagreen4',
        'deepskyblue2',
        'dodgerblue4',
        'gold3',
        'hotpink',
        'lawngreen',
        'lightcoral',
        'lightpink3',
        'lightseagreen',
        'lightslateblue',
        'mediumpurple',
        'olivedrab',
        'orangered1',
        'peru',
        'slategray',
        'yellow4',
        'springgreen2');
    private static $iNum = 33;

    public static function getColor()
    {
        if (ColorFactory::$iIdx >= ColorFactory::$iNum) {
            ColorFactory::$iIdx = 0;
        }

        return ColorFactory::$iColorList[ColorFactory::$iIdx++];
    }
}
