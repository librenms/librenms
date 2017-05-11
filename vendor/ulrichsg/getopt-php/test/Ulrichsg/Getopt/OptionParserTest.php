<?php

namespace Ulrichsg\Getopt;

class OptionParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var OptionParser */
    private $parser;

    public function setUp()
    {
        $this->parser = new OptionParser(Getopt::REQUIRED_ARGUMENT);
    }

    public function testParseString()
    {
        $options = $this->parser->parseString('ab:c::3');
        $this->assertInternalType('array', $options);
        $this->assertCount(4, $options);
        foreach ($options as $option) {
            $this->assertInstanceOf('Ulrichsg\Getopt\Option', $option);
            $this->assertNull($option->long());
            switch ($option->short()) {
                case 'a':
                case '3':
                    $this->assertEquals(Getopt::NO_ARGUMENT, $option->mode());
                    break;
                case 'b':
                    $this->assertEquals(Getopt::REQUIRED_ARGUMENT, $option->mode());
                    break;
                case 'c':
                    $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
                    break;
                default:
                    $this->fail('Unexpected option: '.$option->short());
            }
        }
    }

    public function testParseStringEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString('');
    }

    public function testParseStringInvalidCharacter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString('ab:c::dä');
    }

    public function testParseStringStartsWithColon()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString(':ab:c::d');
    }

    public function testParseStringTripleColon()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString('ab:c:::d');
    }

    public function testParseArray()
    {
        $options = $this->parser->parseArray(
            array(
                array('a', 'alpha', Getopt::OPTIONAL_ARGUMENT, 'Description', 42),
                new Option('b', 'beta'),
                array('c')
            )
        );

        $this->assertCount(3, $options);
        foreach ($options as $option) {
            $this->assertInstanceOf('Ulrichsg\Getopt\Option', $option);
            switch ($option->short()) {
                case 'a':
                    $this->assertEquals('alpha', $option->long());
                    $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
                    $this->assertEquals('Description', $option->getDescription());
                    $this->assertEquals(42, $option->getArgument()->getDefaultValue());
                    break;
                case 'b':
                    $this->assertEquals('beta', $option->long());
                    $this->assertEquals(Getopt::NO_ARGUMENT, $option->mode());
                    $this->assertEquals('', $option->getDescription());
                    break;
                case 'c':
                    $this->assertNull($option->long());
                    $this->assertEquals(Getopt::REQUIRED_ARGUMENT, $option->mode());
                    $this->assertEquals('', $option->getDescription());
                    $this->assertFalse($option->getArgument()->hasDefaultValue());
                    break;
                default:
                    $this->fail('Unexpected option: '.$option->short());
            }
        }
    }

    public function testParseArrayEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseArray(array());
    }

    public function testParseArrayInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseArray(array('a', 'b'));
    }
}
