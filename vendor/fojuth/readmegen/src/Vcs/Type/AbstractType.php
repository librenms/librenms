<?php namespace ReadmeGen\Vcs\Type;

use ReadmeGen\Shell;

abstract class AbstractType implements TypeInterface
{
    const MSG_SEPARATOR = '{{MSG_SEPARATOR}}';
    
    /**
     * Shell script runner.
     *
     * @var Shell
     */
    protected $shell;

    /**
     * Input arguments.
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * Input arguments.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Shell command executing class setter.
     * 
     * @param Shell $shell
     */
    public function setShellRunner(Shell $shell)
    {
        $this->shell = $shell;
    }

    /**
     * Runs the shell command and returns the result.
     *
     * @param $command
     * @return string
     */
    protected function runCommand($command)
    {
        return $this->shell->run($command);
    }

    /**
     * Input option setter.
     *
     * @param array $options
     * @return mixed
     */
    public function setOptions(array $options = null)
    {
        $this->options = $options;
    }

    /**
     * Input argument setter.
     *
     * @param array $arguments
     * @return mixed
     */
    public function setArguments(array $arguments = null)
    {
        $this->arguments = $arguments;
    }

    /**
     * Returns true if an option exists.
     *
     * @param $option
     * @return mixed
     */
    public function hasOption($option)
    {
        return in_array($option, $this->options);
    }

    /**
     * Returns all options.
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns true if an argument exists.
     *
     * @param $argument
     * @return mixed
     */
    public function hasArgument($argument)
    {
        return isset($this->arguments[$argument]);
    }

    /**
     * Returns the argument's value.
     *
     * @param $argument
     * @return mixed
     */
    public function getArgument($argument)
    {
        return $this->arguments[$argument];
    }

    /**
     * Return all arguments.
     *
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }
    
}
