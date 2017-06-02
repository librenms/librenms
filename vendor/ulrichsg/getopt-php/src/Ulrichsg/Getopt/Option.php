<?php

namespace Ulrichsg\Getopt;

/**
 * Represents an option that Getopt accepts.
 */
class Option
{
    private $short;
    private $long;
    private $mode;
    private $description = '';
    private $argument;

    /**
     * Creates a new option.
     *
     * @param string $short the option's short name (a single letter or digit) or null for long-only options
     * @param string $long the option's long name (a string of 2+ letter/digit/_/- characters, starting with a letter
     *                     or digit) or null for short-only options
     * @param int $mode whether the option can/must have an argument (one of the constants defined in the Getopt class)
     *                  (optional, defaults to no argument)
     * @throws \InvalidArgumentException if both short and long name are null
     */
    public function __construct($short, $long, $mode = Getopt::NO_ARGUMENT)
    {
        if (!$short && !$long) {
            throw new \InvalidArgumentException("The short and long name may not both be empty");
        }
        $this->setShort($short);
        $this->setLong($long);
        $this->setMode($mode);
        $this->argument = new Argument();
    }

    /**
     * Defines a description for the option. This is only used for generating usage information.
     *
     * @param string $description
     * @return Option this object (for chaining calls)
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

	/**
	 * Defines a default value for the option.
	 *
	 * @param mixed $value
     * @return Option this object (for chaining calls)
	 */
	public function setDefaultValue($value)
	{
		$this->argument->setDefaultValue($value);
		return $this;
	}

	/**
	 * Defines a validation function for the option.
	 *
	 * @param callable $function
	 * @return Option this object (for chaining calls)
	 */
	public function setValidation($function)
	{
		$this->argument->setValidation($function);
		return $this;
	}

	/**
     * Sets the argument object directly.
     *
     * @param Argument $arg
     * @return Option this object (for chaining calls)
     */
    public function setArgument(Argument $arg)
    {
        if ($this->mode == Getopt::NO_ARGUMENT) {
            throw new \InvalidArgumentException("Option should not have any argument");
        }
        $this->argument = $arg;
        return $this;
    }

    /**
     * Returns true if the given string is equal to either the short or the long name.
     *
     * @param string $string
     * @return bool
     */
    public function matches($string)
    {
        return ($string === $this->short) || ($string === $this->long);
    }

    public function short()
    {
        return $this->short;
    }

    public function long()
    {
        return $this->long;
    }

    public function mode()
    {
        return $this->mode;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Retrieve the argument object
     * 
     * @return Argument
     */
    public function getArgument()
    {
        return $this->argument;
    }
    
    /**
     * Fluent interface for constructor so options can be added during construction
     * @see Options::__construct()
     */
    public static function create($short, $long, $mode = Getopt::NO_ARGUMENT)
    {
    	return new self($short, $long, $mode);
    }

    private function setShort($short)
    {
        if (!(is_null($short) || preg_match("/^[a-zA-Z0-9]$/", $short))) {
            throw new \InvalidArgumentException("Short option must be null or a letter/digit, found '$short'");
        }
        $this->short = $short;
    }

    private function setLong($long)
    {
        if (!(is_null($long) || preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]{1,}$/", $long))) {
            throw new \InvalidArgumentException("Long option must be null or an alphanumeric string, found '$long'");
        }
        $this->long = $long;
    }

    private function setMode($mode)
    {
        if (!in_array($mode, array(Getopt::NO_ARGUMENT, Getopt::OPTIONAL_ARGUMENT, Getopt::REQUIRED_ARGUMENT), true)) {
            throw new \InvalidArgumentException("Option mode must be one of "
                ."Getopt::NO_ARGUMENT, Getopt::OPTIONAL_ARGUMENT and Getopt::REQUIRED_ARGUMENT");
        }
        $this->mode = $mode;
    }
}
