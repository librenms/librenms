<?php

namespace Ulrichsg\Getopt;

class GetoptTest extends \PHPUnit_Framework_TestCase
{
    public function testAddOptions()
    {
        $getopt = new Getopt();
        $getopt->addOptions('a:');
        $getopt->addOptions(
            array(
                array('s', null, Getopt::OPTIONAL_ARGUMENT),
                array(null, 'long', Getopt::OPTIONAL_ARGUMENT),
                array('n', 'name', Getopt::OPTIONAL_ARGUMENT)
            )
        );

        $getopt->parse('-a aparam -s sparam --long longparam');
        $this->assertEquals('aparam', $getopt->getOption('a'));
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('sparam', $getopt->getOption('s'));
    }

    public function testAddOptionsChooseShortOrLongAutomatically()
    {
        $getopt = new Getopt();
        $getopt->addOptions(
            array(
                array('s'),
                array('long', Getopt::OPTIONAL_ARGUMENT)
            )
        );

        $getopt->parse('-s --long longparam');
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('1', $getopt->getOption('s'));
    }

    public function testAddOptionsUseDefaultArgumentType()
    {
        $getopt = new Getopt(null, Getopt::REQUIRED_ARGUMENT);
        $getopt->addOptions(
            array(
                array('l', 'long')
            )
        );

        $getopt->parse('-l something');
        $this->assertEquals('something', $getopt->getOption('l'));

        $getopt->parse('--long someOtherThing');
        $this->assertEquals('someOtherThing', $getopt->getOption('long'));
    }

    public function testAddOptionsFailsOnInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new Getopt(null);
        $getopt->addOptions(new Option('a', 'alpha'));
    }

    public function testAddOptionsOverwritesExistingOptions()
    {
        $getopt = new Getopt(array(
            array('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->addOptions(array(
            array('a', null, Getopt::NO_ARGUMENT)
        ));
        $getopt->parse('-a foo');

        $this->assertEquals(1, $getopt->getOption('a'));
        $this->assertEquals('foo', $getopt->getOperand(0));
    }

    public function testAddOptionsFailsOnConflict()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new Getopt(array(
            array('v', 'version')
        ));
        $getopt->addOptions(array(
            array('v', 'verbose')
        ));
    }

    public function testParseUsesGlobalArgvWhenNoneGiven()
    {
        global $argv;
        $argv = array('foo.php', '-a');

        $getopt = new Getopt('a');
        $getopt->parse();
        $this->assertEquals(1, $getopt->getOption('a'));
    }

    public function testAccessMethods()
    {
        $getopt = new Getopt('a');
        $getopt->parse('-a foo');

        $options = $getopt->getOptions();
        $this->assertCount(1, $options);
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $getopt->getOption('a'));

        $operands = $getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('foo', $operands[0]);
        $this->assertEquals('foo', $getopt->getOperand(0));
    }

    public function testCountable()
    {
        $getopt = new Getopt('abc');
        $getopt->parse('-abc');
        $this->assertEquals(3, count($getopt));
    }

    public function testArrayAccess()
    {
        $getopt = new Getopt('q');
        $getopt->parse('-q');
        $this->assertEquals(1, $getopt['q']);
    }

    public function testIterable()
    {
        $getopt = new Getopt(array(
            array(null, 'alpha', Getopt::NO_ARGUMENT),
            array('b', 'beta', Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('--alpha -b foo');
        $expected = array('alpha' => 1, 'b' => 'foo'); // 'beta' should not occur
        foreach ($getopt as $option => $value) {
            $this->assertEquals($expected[$option], $value);
        }
    }

    public function testHelpText()
    {
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT, 'Short and long options with no argument'),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument'),
            array('c', null, Getopt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument')
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha             Short and long options with no argument\n";
        $expected .= "  --beta [<arg>]          Long option only with an optional argument\n";
        $expected .= "  -c <arg>                Short option only with a mandatory argument\n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithoutDescriptions()
    {
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT),
            array('c', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha             \n";
        $expected .= "  --beta [<arg>]          \n";
        $expected .= "  -c <arg>                \n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }

    public function testHelpTextNoParse()
    {
        $getopt = new Getopt();
        $expected = "Usage:  [options] [operands]\nOptions:\n";
        $this->assertSame($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithCustomBanner()
    {
        $script = $_SERVER['PHP_SELF'];
        
        $getopt = new Getopt();
        $getopt->setBanner("My custom Banner %s\n");
        $this->assertSame("My custom Banner \nOptions:\n", $getopt->getHelpText());

        $getopt->parse('');
        $this->assertSame("My custom Banner $script\nOptions:\n", $getopt->getHelpText());
    }
}
