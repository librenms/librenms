<?php
/**
 * Created by PhpStorm.
 * User: dmartinez
 * Date: 18-6-15
 * Time: 17:39
 */

namespace Leaseweb\InfluxDB\Test;


use Leaseweb\InfluxDB\Point;

class PointTest extends \PHPUnit_Framework_TestCase
{

    public function testPointStringRepresentation()
    {
        $expected = 'cpu_load_short,host=server01,region=us-west value=0.64 mytime';

        $point = new Point(
            'cpu_load_short',
            array('host'  =>'server01', 'region'=>'us-west'),
            array('value' => 0.64),
            'myTime'
        );


        $this->assertEquals($expected, (string) $point);
    }

}