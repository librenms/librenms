<?php
namespace InfluxDB\Adapter;

class HelpersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getElements
     */
    public function testListToInlineValues($message, $result, $escape)
    {
        $this->assertEquals($result, list_to_string($message, $escape));
    }

    public function getElements()
    {
        return [
            [["one" => "two"], "one=two", false],
            [["one" => "two"], "one=\"two\"", true],
            [["one" => "two", "three" => "four"], "one=two,three=four", false],
            [["one" => "two", "three" => "four"], "one=\"two\",three=\"four\"", true],
        ];
    }
}
