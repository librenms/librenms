<?php namespace ReadmeGen\Input;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

/**
 * Input parser.
 *
 * Class Parser
 * @package ReadmeGen\Input
 */
class Parser
{
    /**
     * CLI input parser.
     *
     * @var Getopt
     */
    protected $handler;

    /**
     * Entered command.
     *
     * @var string
     */
    protected $input;
    
    public function __construct()
    {
        // Register possible input arguments.
        $this->handler = new Getopt(array(
            new Option('r', 'release', Getopt::REQUIRED_ARGUMENT),
            new Option('f', 'from', Getopt::REQUIRED_ARGUMENT),
            new Option('t', 'to', Getopt::OPTIONAL_ARGUMENT),
            new Option('b', 'break', Getopt::OPTIONAL_ARGUMENT),
        ));
    }

    /**
     * Set the input.
     *
     * @param $input string
     */
    public function setInput($input)
    {
        $inputArray = explode(' ', $input);
        
        array_shift($inputArray);
        
        $this->input = join(' ', $inputArray);
    }

    /**
     * Parses the input and returns the Getopt handler.
     *
     * @return Getopt
     */
    public function parse()
    {
        $this->handler->parse($this->input);

        $output = $this->handler->getOptions();

        if (false === isset($output['from'])) {
            throw new \BadMethodCallException('The --from argument is required.');
        }

        if (false === isset($output['release'])) {
            throw new \BadMethodCallException('The --release argument is required.');
        }
        
        return $this->handler;
    }
}
