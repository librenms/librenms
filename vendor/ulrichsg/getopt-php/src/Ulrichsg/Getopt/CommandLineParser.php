<?php

namespace Ulrichsg\Getopt;

/**
 * Parses command line arguments according to a list of allowed options.
 */
class CommandLineParser
{
    /** @var Option[] */
    private $optionList;

    private $options = array();
    private $operands = array();

    /**
     * Creates a new instance.
     *
     * @param Option[] $optionList the list of allowed options
     */
    public function __construct(array $optionList)
    {
        $this->optionList = $optionList;
    }

    /**
     * Parses the given arguments and converts them into options and operands.
     *
     * @param mixed $arguments a string or an array with one argument per element
     */
    public function parse($arguments)
    {
        if (!is_array($arguments)) {
            $arguments = explode(' ', $arguments);
        }
        $operands = array();
        $numArgs = count($arguments);
        for ($i = 0; $i < $numArgs; ++$i) {
            $arg = trim($arguments[$i]);
            if (empty($arg)) {
                continue;
            }
            if (($arg === '--') || ($arg === '-') || (mb_substr($arg, 0, 1) !== '-')){
                // no more options, treat the remaining arguments as operands
                $firstOperandIndex = ($arg == '--') ? $i + 1 : $i;
                $operands = array_slice($arguments, $firstOperandIndex);
                break;
            }
            if (mb_substr($arg, 0, 2) == '--') {
                $this->addLongOption($arguments, $i);
            } else {
                $this->addShortOption($arguments, $i);
            }
        } // endfor

        $this->addDefaultValues();

        // remove '--' from operands array
        foreach ($operands as $operand) {
            if ($operand !== '--') {
                $this->operands[] = $operand;
            }
        }
    }

    /**
     * Returns the options created by a previous invocation of parse().
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * Returns the operands created by a previous invocation of parse(),
     *
     * @return array
     */
    public function getOperands()
    {
        return $this->operands;
    }

    private function addShortOption($arguments, &$i)
    {
        $numArgs = count($arguments);
        $option = mb_substr($arguments[$i], 1);
        if (mb_strlen($option) > 1) {
            // multiple options strung together
            $options = $this->splitString($option, 1);
            foreach ($options as $j => $ch) {
                if ($j < count($options) - 1
                        || !(
                                $i < $numArgs - 1
                                && ((mb_substr($arguments[$i + 1], 0, 1) !== '-') || ($arguments[$i + 1] === '-'))
                                && $this->optionHasArgument($ch)
                        )
                ) {
                    $this->addOption($ch, null);
                } else { // e.g. `ls -sw 100`
                    $value = $arguments[$i + 1];
                    ++$i;
                    $this->addOption($ch, $value);
                }
            }
        } else {
            if ($i < $numArgs - 1
                    && ((mb_substr($arguments[$i + 1], 0, 1) !== '-') || ($arguments[$i + 1] === '-'))
                    && $this->optionHasArgument($option)
            ) {
                $value = $arguments[$i + 1];
                ++$i;
            } else {
                $value = null;
            }
            $this->addOption($option, $value);
        }
    }

    private function addLongOption($arguments, &$i)
    {
        $option = mb_substr($arguments[$i], 2);
        if (strpos($option, '=') === false) {
            if ($i < count($arguments) - 1
                    && ((mb_substr($arguments[$i + 1], 0, 1) !== '-') || ($arguments[$i + 1] === '-'))
                    && $this->optionHasArgument($option)
            ) {
                $value = $arguments[$i + 1];
                ++$i;
            } else {
                $value = null;
            }
        } else {
            list($option, $value) = explode('=', $option, 2);
        }
        $this->addOption($option, $value);
    }

    /**
     * Add an option to the list of known options.
     *
     * @param string $string the option's name
     * @param string $value the option's value (or null)
     * @throws \UnexpectedValueException
     * @return void
     */
    private function addOption($string, $value)
    {
        foreach ($this->optionList as $option) {
            if ($option->matches($string)) {
                if ($option->mode() == Getopt::REQUIRED_ARGUMENT && !mb_strlen($value)) {
                    throw new \UnexpectedValueException("Option '$string' must have a value");
                }
                if ($option->getArgument()->hasValidation()) {
                    if ((mb_strlen($value) > 0) && !$option->getArgument()->validates($value)) {
                        throw new \UnexpectedValueException("Option '$string' has an invalid value");
                    }
                }
                // for no-argument options, check if they are duplicate
                if ($option->mode() == Getopt::NO_ARGUMENT) {
                    $oldValue = isset($this->options[$string]) ? $this->options[$string] : null;
                    $value = is_null($oldValue) ? 1 : $oldValue + 1;
                }
                // for optional-argument options, set value to 1 if none was given
                $value = (mb_strlen($value) > 0) ? $value : 1;
                // add both long and short names (if they exist) to the option array to facilitate lookup
                if ($option->short()) {
                    $this->options[$option->short()] = $value;
                }
                if ($option->long()) {
                    $this->options[$option->long()] = $value;
                }
                return;
            }
        }
        throw new \UnexpectedValueException("Option '$string' is unknown");
    }

    /**
     * If there are options with default values that were not overridden by the parsed option string,
     * add them to the list of known options.
     */
    private function addDefaultValues()
    {
        foreach ($this->optionList as $option) {
            if ($option->getArgument()->hasDefaultValue()
                    && !isset($this->options[$option->short()])
                    && !isset($this->options[$option->long()])
            ) {
                if ($option->short()) {
                    $this->addOption($option->short(), $option->getArgument()->getDefaultValue());
                }
                if ($option->long()) {
                    $this->addOption($option->long(), $option->getArgument()->getDefaultValue());
                }
            }
        }
    }

    /**
     * Return true if the given option can take an argument, false if it can't or is unknown.
     *
     * @param string $name the option's name
     * @return boolean
     */
    private function optionHasArgument($name)
    {
        foreach ($this->optionList as $option) {
            if ($option->matches($name)) {
                return $option->mode() != Getopt::NO_ARGUMENT;
            }
        }
        return false;
    }

    /**
     * Split the string into individual characters,
     *
     * @param string $string string to split
     * @return array
     */
    private function splitString($string)
    {
        $result = array();
        for ($i = 0; $i < mb_strlen($string, "UTF-8"); ++$i) {
            $result[] = mb_substr($string, $i, 1, "UTF-8");
        }
        return $result;
    }
}
