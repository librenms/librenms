<?php

use cli\Arguments;

/**
 * Class TestArguments
 * @todo add more tests to increase coverage
 *
 * @backupGlobals enabled
 */
class TestArguments extends PHPUnit_Framework_TestCase
{
    /**
     * Array of expected settings
     * @var array
     */
    protected $settings = null;

    /**
     * Array of flags
     * @var array
     */
    protected $flags = null;

    /**
     * Array of expected options
     * @var array
     */
    protected $options = null;

    /**
     * Clear the $_SERVER['argv'] array
     */
    public static function clearArgv()
    {
        $_SERVER['argv'] = array();
        $_SERVER['argc'] = 0;
    }

    /**
     * Add one or more element(s) at the end of the $_SERVER['argv'] array
     *
     * @param  array $args: value(s) to add to the argv array
     */
    public static function pushToArgv($args)
    {
        if (is_string($args)) {
            $args = explode(' ', $args);
        }

        foreach ($args as $arg) {
            array_push($_SERVER['argv'], $arg);
        }

        $_SERVER['argc'] += count($args);
    }

    /**
     * Set up valid flags and options
     */
    public function setUp()
    {
        self::clearArgv();
        self::pushToArgv('my_script.php');

        $this->flags = array(
            'flag1' => array(
                'aliases' => 'f',
                'description' => 'Test flag 1'
            ),
            'flag2' => array(
                'description' => 'Test flag 2'
            )
        );

        $this->options = array(
            'option1' => array(
                'aliases' => 'o',
                'description' => 'Test option 1'
            ),
            'option2' => array(
                'aliases' => array('x', 'y'),
                'description' => 'Test option 2 with default',
                'default' => 'some default value'
            )
        );

        $this->settings = array(
            'strict' => true,
            'flags' => $this->flags,
            'options' => $this->options
        );
    }

    /**
     * Tear down fixtures
     */
    public function tearDown()
    {
        $this->flags = null;
        $this->options = null;
        $this->settings = null;
        self::clearArgv();
    }

    /**
     * Test adding a flag, getting a flag and getting all flags
     */
    public function testAddFlags()
    {
        $args = new cli\Arguments($this->settings);

        $expectedFlags = $this->flags;
        $expectedFlags['flag1']['default'] = false;
        $expectedFlags['flag1']['stackable'] = false;
        $expectedFlags['flag2']['default'] = false;
        $expectedFlags['flag2']['stackable'] = false;
        $expectedFlags['flag2']['aliases'] = array();

        $this->assertSame($expectedFlags, $args->getFlags());

        $this->assertSame($expectedFlags['flag1'], $args->getFlag('flag1'));
        $this->assertSame($expectedFlags['flag1'], $args->getFlag('f'));

        $expectedFlag1Argument = new cli\arguments\Argument('-f');
        $this->assertSame($expectedFlags['flag1'], $args->getFlag($expectedFlag1Argument));
    }

    /**
     * Test adding a option, getting a option and getting all options
     */
    public function testAddOptions()
    {
        $args = new cli\Arguments($this->settings);

        $expectedOptions = $this->options;
        $expectedOptions['option1']['default'] = null;

        $this->assertSame($expectedOptions, $args->getOptions());

        $this->assertSame($expectedOptions['option1'], $args->getOption('option1'));
        $this->assertSame($expectedOptions['option1'], $args->getOption('o'));

        $expectedOption1Argument = new cli\arguments\Argument('-o');
        $this->assertSame($expectedOptions['option1'], $args->getOption($expectedOption1Argument));
    }

    /**
     * Data provider with valid args and options
     *
     * @return array set of args and expected parsed values
     */
    public function settingsWithValidOptions()
    {
        return array(
            array(
                array('-o', 'option_value', '-f'),
                array('option1' => 'option_value', 'flag1' => true)
            ),
            array(
                array('--option1', 'option_value', '--flag1'),
                array('option1' => 'option_value', 'flag1' => true)
            ),
            array(
                array('-f', '--option1', 'option_value'),
                array('flag1' => true, 'option1' => 'option_value')
            )
        );
    }

    /**
     * Data provider with missing options
     *
     * @return array set of args and expected parsed values
     */
    public function settingsWithMissingOptions()
    {
        return array(
            array(
                array('-f', '--option1'),
                array('flag1' => true, 'option1' => 'Error should be triggered')
            ),
            array(
                array('--option1', '-f'),
                array('option1' => 'Error should be triggered', 'flag1' => true)
            )
        );
    }

    /**
     * Data provider with missing options. The default value should be populated
     *
     * @return array set of args and expected parsed values
     */
    public function settingsWithMissingOptionsWithDefault()
    {
        return array(
            array(
                array('-f', '--option2'),
                array('flag1' => true, 'option2' => 'some default value')
            ),
            array(
                array('--option2', '-f'),
                array('option2' => 'some default value', 'flag1' => true)
            )
        );
    }

    public function settingsWithNoOptionsWithDefault()
    {
        return array(
            array(
                array(),
                array('flag1' => false, 'flag2' => false, 'option2' => 'some default value')
            )
        );
    }

    /**
     * Generic private testParse method.
     *
     * @param  array $args           arguments as they appear in the cli
     * @param  array $expectedValues expected values after parsing
     */
    private function _testParse($cliParams, $expectedValues)
    {
        self::pushToArgv($cliParams);

        $args = new cli\Arguments($this->settings);
        $args->parse();

        foreach ($expectedValues as $name => $value) {
            if ($args->isFlag($name)) {
                $this->assertEquals($value, $args[$name]);
            }

            if ($args->isOption($name)) {
                $this->assertEquals($value, $args[$name]);
            }
        }
    }

    /**
     * @param  array $args           arguments as they appear in the cli
     * @param  array $expectedValues expected values after parsing
     *
     * @dataProvider settingsWithValidOptions
     */
    public function testParseWithValidOptions($cliParams, $expectedValues)
    {
        $this->_testParse($cliParams, $expectedValues);
    }

    /**
     * @param  array $args           arguments as they appear in the cli
     * @param  array $expectedValues expected values after parsing
     * @dataProvider settingsWithMissingOptions
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage no value given for --option1
     */
    public function testParseWithMissingOptions($cliParams, $expectedValues)
    {
        $this->_testParse($cliParams, $expectedValues);
    }

    /**
     * @param  array $args           arguments as they appear in the cli
     * @param  array $expectedValues expected values after parsing
     * @dataProvider settingsWithMissingOptionsWithDefault
     */
    public function testParseWithMissingOptionsWithDefault($cliParams, $expectedValues)
    {
        $this->_testParse($cliParams, $expectedValues);
    }

    /**
     * @param  array $args           arguments as they appear in the cli
     * @param  array $expectedValues expected values after parsing
     * @dataProvider settingsWithNoOptionsWithDefault
     */
    public function testParseWithNoOptionsWithDefault($cliParams, $expectedValues) {
        $this->_testParse($cliParams, $expectedValues);
    }
}
