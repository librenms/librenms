<?php

namespace Ulrichsg\Getopt;

class ArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $argument1 = new Argument();
        $argument2 = new Argument(10);
        $this->assertFalse($argument1->hasDefaultValue());
        $this->assertEquals(10, $argument2->getDefaultValue());
    }

    public function testSetDefaultValueNotScalar()
    {
        $this->setExpectedException('InvalidArgumentException');
        $argument = new Argument();
        $argument->setDefaultValue(array());
    }

    public function testValidates()
    {
        $test = $this;
        $argument = new Argument();
        $argument->setValidation(function($arg) use ($test, $argument) {
            $test->assertEquals('test', $arg);
            return true;
        });
        $this->assertTrue($argument->hasValidation());
        $this->assertTrue($argument->validates('test'));
    }

    public function testSetValidationUncallable()
    {
        $this->setExpectedException('InvalidArgumentException');
        $argument = new Argument();
        $argument->setValidation('');
    }
}