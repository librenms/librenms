<?php namespace ReadmeGen\Vcs\Type;

use ReadmeGen\Shell;

interface TypeInterface
{
    
    /**
     * Parses the log.
     * 
     * @return array
     */
    public function parse();
    
    /**
     * Shell command executing class setter.
     * 
     * @param Shell $shell
     */
    public function setShellRunner(Shell $shell);

    /**
     * Input option setter.
     *
     * @param array $options
     * @return mixed
     */
    public function setOptions(array $options = null);

    /**
     * Input argument setter.
     *
     * @param array $arguments
     * @return mixed
     */
    public function setArguments(array $arguments = null);

    /**
     * Returns true if an option exists.
     *
     * @param $option
     * @return mixed
     */
    public function hasOption($option);

    /**
     * Returns all options.
     *
     * @return mixed
     */
    public function getOptions();

    /**
     * Returns true if an argument exists.
     *
     * @param $argument
     * @return mixed
     */
    public function hasArgument($argument);

    /**
     * Returns the argument's value.
     *
     * @param $argument
     * @return mixed
     */
    public function getArgument($argument);

    /**
     * Return all arguments.
     *
     * @return mixed
     */
    public function getArguments();

    /**
     * Returns the date of the latter (--to) commit, in the format YYYY-MM-DD.
     *
     * @return string
     */
    public function getToDate();
    
}
