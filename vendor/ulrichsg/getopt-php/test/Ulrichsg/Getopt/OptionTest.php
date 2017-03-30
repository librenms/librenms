<?php

namespace Ulrichsg\Getopt;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $option = new Option('a', 'az-AZ09_', Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
    }

    public function testConstructEmptyOption()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, null, Getopt::NO_ARGUMENT);
    }

    public function testConstructNoLetter()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option('?', null, Getopt::NO_ARGUMENT);
    }

    public function testConstructInvalidCharacter()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, 'öption', Getopt::NO_ARGUMENT);
    }

    public function testConstructInvalidArgumentType()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option('a', null, 'no_argument');
    }

    public function testConstructLongOptionTooShort()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, 'a', Getopt::REQUIRED_ARGUMENT);
    }

    public function testSetArgument()
    {
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setArgument(new Argument()));
        $this->assertInstanceof('Ulrichsg\Getopt\Argument', $option->getArgument());
    }

    public function testSetArgumentWrongMode()
    {
        $this->setExpectedException('InvalidArgumentException');
        $option = new Option('a', null, Getopt::NO_ARGUMENT);
        $option->setArgument(new Argument());
    }

	public function testSetDefaultValue()
	{
		$option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
		$this->assertEquals($option, $option->setDefaultValue(10));
		$this->assertEquals(10, $option->getArgument()->getDefaultValue());
	}

	public function testSetValidation()
	{
		$option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
		$this->assertEquals($option, $option->setValidation('is_numeric'));
		$this->assertTrue($option->getArgument()->hasValidation());
	}
}
