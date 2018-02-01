<?php

namespace InfluxDB\Test;


use InfluxDB\Point;
use PHPUnit\Framework\TestCase;

class PointTest extends TestCase
{
    public function testPointStringRepresentation()
    {
        $expected = 'instance,host=server01,region=us-west cpucount=10i,free=1i,test="string",bool=false,value=1.11 1440494531376778481';

        $point = $this->getPoint('1440494531376778481');

        $this->assertEquals($expected, (string) $point);
    }

    /**
     * Check if the Point class throw an exception when invalid timestamp are given.
     *
     * @dataProvider wrongTimestampProvider
     * @expectedException \InfluxDB\Database\Exception
     */
    public function testPointWrongTimestamp($timestamp)
    {
        $this->getPoint($timestamp);
    }

    /**
     * Check if the Point class accept all valid timestamp given.
     *
     * @dataProvider validTimestampProvider
     */
    public function testPointValidTimestamp($timestamp)
    {
        $expected = 'instance,host=server01,region=us-west cpucount=10i,free=1i,test="string",bool=false,value=1.11' . (($timestamp) ? ' ' . $timestamp : '');

        $point = $this->getPoint($timestamp);

        $this->assertEquals($expected, (string) $point);
    }

    public function testGettersAndSetters()
    {
        $timestamp = time();
        $timestamp2 = time() + 3600;
        $point = $this->getPoint($timestamp);

        $this->assertEquals($timestamp, $point->getTimestamp());
        $point->setTimestamp($timestamp2);
        $this->assertEquals($timestamp2, $point->getTimestamp());

        $this->assertEquals('instance', $point->getMeasurement());
        $point->setMeasurement('test');
        $this->assertEquals('test', $point->getMeasurement());

        $fields = $point->getFields();
        $this->assertEquals(1.11, $fields['value']);
        $this->assertEquals([
            'cpucount' => '10i',
            'free' => '1i',
            'test' => "\"string\"",
            'bool' => 'false',
            'value' => '1.1100000000000001'
        ], $fields);

        $point->setFields(['cpucount' => 11]);
        $this->assertEquals(['cpucount' => '11i'], $point->getFields());

        $this->assertEquals(['host' => 'server01', 'region' => 'us-west'], $point->getTags());
        $point->setTags(['test' => 'value']);
        $this->assertEquals(['test' => 'value'], $point->getTags());

    }

    public function testFieldValueStringEscaping()
    {
        $expected = 'instance,host=server01,region=us-west spaces="string with spaces",doublequote="the \" is escaped"';
        $point = $this->getPoint(null);

        $point->setFields(['spaces' => 'string with spaces', 'doublequote' => 'the " is escaped']);

        $this->assertEquals($expected, (string) $point);
    }

    public function testTagBooleanValueEscaping() {
        $expected = 'instance,bool_tag=false,value_tag=value cpucount=10i,free=1i,test="string",bool=false,value=1.11';
        $point = $this->getPoint(null);

        $point->setTags(['bool_tag' => false, 'value_tag' => 'value']);

        $this->assertEquals($expected, (string) $point);
    }

    public function testTagNullValueEscaping() {
        $expected = 'instance,null_tag=null,value_tag=value cpucount=10i,free=1i,test="string",bool=false,value=1.11';
        $point = $this->getPoint(null);

        $point->setTags(['null_tag' => null, 'value_tag' => 'value']);

        $this->assertEquals($expected, (string) $point);
    }
    public function testTagEmptyValueEscaping() {
        $expected = 'instance,empty_tag="",whitespace=\ ,value_tag=value cpucount=10i,free=1i,test="string",bool=false,value=1.11';
        $point = $this->getPoint(null);

        $point->setTags(['empty_tag' => '', 'whitespace' => ' ', 'value_tag' => 'value']);

        $this->assertEquals($expected, (string) $point);
    }

    /**
     * Provide wrong timestamp value for testing.
     */
    public function wrongTimestampProvider()
    {
        return [
            ['2015-10-27 14:17:40'],
            ['INVALID'],
            ['aa778481'],
            ['1477aee'],
            ['15.258'],
            ['15,258'],
            [15.258],
            [true]
        ];
    }

    /**
     * Provide valid timestamp value for testing.
     */
    public function validTimestampProvider()
    {
        return [
            [time()],               // Current time returned by the PHP time function.
            [0],                    // Day 0
            [~PHP_INT_MAX],         // Minimum value integer
            [PHP_INT_MAX],          // Maximum value integer
            ['1440494531376778481'] // Text timestamp
        ];
    }

    /**
     * Returns an instance of \InfluxDB\Point
     *
     * @param int $timestamp
     *
     * @return Point
     */
    private function getPoint($timestamp)
    {
        return new Point(
            'instance', // the name of the measurement
            1.11, // measurement value
            ['host' => 'server01', 'region' => 'us-west'],
            ['cpucount' => 10, 'free' => 1, 'test' => "string", 'bool' => false],
            $timestamp
        );
    }

}
