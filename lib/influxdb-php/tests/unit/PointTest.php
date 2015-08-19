<?php
/**
 * Created by PhpStorm.
 * User: dmartinez
 * Date: 18-6-15
 * Time: 17:39
 */

namespace InfluxDB\Test;


use InfluxDB\Point;

class PointTest extends \PHPUnit_Framework_TestCase
{
    public function testPointStringRepresentation()
    {
        $expected = 'cpu_load_short,host=server01,region=us-west cpucount=10,value=0.64 1435222310';

        $point =  new Point(
            'cpu_load_short',
            0.64,
            array('host' => 'server01', 'region' => 'us-west'),
            array('cpucount' => 10),
            1435222310
        );

        $this->assertEquals($expected, (string) $point);
    }
}