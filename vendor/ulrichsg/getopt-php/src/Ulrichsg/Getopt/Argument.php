<?php

namespace Ulrichsg\Getopt;

class Argument
{
    /** @var string */
    private $default;
    /** @var callable */
    private $validation;
    /** @var string */
    private $name;

    /**
     * Creates a new argument.
     * 
     * @param scalar|null $default Default value or NULL
     * @param callable|null $validation a validation function (optional)
     * @throws \InvalidArgumentException
     */
    public function __construct($default = null, $validation = null, $name = "arg")
    {
        if (!is_null($default)) {
            $this->setDefaultValue($default);
        }
        if (!is_null($validation)) {
            $this->setValidation($validation);
        }
        $this->name = $name;
    }

    /**
     * Set the default value
     * 
     * @param scalar $value
     * @return Argument this object (for chaining calls)
     * @throws \InvalidArgumentException
     */
    public function setDefaultValue($value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException("Default value must be scalar");
        }
        $this->default = $value;
        return $this;
    }

    /**
     * Set a validation function.
     * The function must take a string and return true if it is valid, false otherwise.
     * 
     * @param callable $callable
     * @return Argument this object (for chaining calls)
     * @throws \InvalidArgumentException
     */
    public function setValidation($callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Validation must be a callable");
        }
        $this->validation = $callable;
        return $this;
    }

    /**
     * Check if an argument validates according to the specification.
     * 
     * @param string $arg
     * @return bool
     */
    public function validates($arg)
    {
        return (bool)call_user_func($this->validation, $arg);
    }

    /**
     * Check if the argument has a validation function
     * 
     * @return bool
     */
    public function hasValidation()
    {
        return isset($this->validation);
    }

    /**
     * Check whether the argument has a default value
     * 
     * @return boolean
     */
    public function hasDefaultValue()
    {
        return !empty($this->default);
    }

    /**
     * Retrieve the default value
     * 
     * @return scalar|null
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    /**
     * Retrieve the argument name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
