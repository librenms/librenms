<?php

namespace Ulrichsg\Getopt;

/**
 * Converts user-given option specifications into Option objects.
 */
class OptionParser
{
    private $defaultMode;

    /**
     * Creates a new instance.
     *
     * @param int $defaultMode will be assigned to options when no mode is given for them.
     */
    public function __construct($defaultMode) {
        $this->defaultMode = $defaultMode;
    }

    /**
     * Parse a GNU-style option string.
     *
     * @param string $string the option string
     * @return Option[]
     * @throws \InvalidArgumentException
     */
    public function parseString($string)
    {
        if (!mb_strlen($string)) {
            throw new \InvalidArgumentException('Option string must not be empty');
        }
        $options = array();
        $eol = mb_strlen($string) - 1;
        $nextCanBeColon = false;
        for ($i = 0; $i <= $eol; ++$i) {
            $ch = $string[$i];
            if (!preg_match('/^[A-Za-z0-9]$/', $ch)) {
                $colon = $nextCanBeColon ? " or ':'" : '';
                throw new \InvalidArgumentException("Option string is not well formed: "
                    ."expected a letter$colon, found '$ch' at position ".($i + 1));
            }
            if ($i == $eol || $string[$i + 1] != ':') {
                $options[] = new Option($ch, null, Getopt::NO_ARGUMENT);
                $nextCanBeColon = true;
            } elseif ($i < $eol - 1 && $string[$i + 2] == ':') {
                $options[] = new Option($ch, null, Getopt::OPTIONAL_ARGUMENT);
                $i += 2;
                $nextCanBeColon = false;
            } else {
                $options[] = new Option($ch, null, Getopt::REQUIRED_ARGUMENT);
                ++$i;
                $nextCanBeColon = true;
            }
        }
        return $options;
    }

    /**
     * Processes an option array. The array elements can either be Option objects or arrays conforming to the format
     * (short, long, mode [, description [, default]]). See documentation for details.
	 *
	 * Developer note: Please don't add any further elements to the array. Future features should be configured only
	 * through the Option class's methods.
     *
     * @param array $array
     * @return Option[]
     * @throws \InvalidArgumentException
     */
    public function parseArray(array $array)
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('No options given');
        }
        $options = array();
        foreach ($array as $row) {
            if ($row instanceof Option) {
                $options[] = $row;
            } elseif (is_array($row)) {
                $options[] = $this->createOption($row);
            } else {
                throw new \InvalidArgumentException("Invalid option type, must be Option or array");
            }

        }
        return $options;
    }

    /**
     * @param array $row
     * @return Option
     */
    private function createOption(array $row)
    {
        $rowSize = count($row);
        if ($rowSize < 3) {
            $row = $this->completeOptionArray($row);
        }
        $option = new Option($row[0], $row[1], $row[2]);
        if ($rowSize >= 4) {
            $option->setDescription($row[3]);
        }
        if ($rowSize >= 5 && $row[2] != Getopt::NO_ARGUMENT) {
            $option->setArgument(new Argument($row[4]));
        }
        return $option;
    }

    /**
     * When using arrays, instead of a full option spec ([short, long, type]) users can leave out one or more of
     * these parts and have Getopt fill them in intelligently:
     * - If either the short or the long option string is left out, the first element of the given array is interpreted
     *   as either short (if it has length 1) or long, and the other one is set to null.
     * - If the type is left out, it is set to NO_ARGUMENT.
     *
     * @param array $row
     * @return array
     */
    private function completeOptionArray(array $row)
    {
        $short = (strlen($row[0]) == 1) ? $row[0] : null;

        $long = null;
        if (is_null($short)) {
            $long = $row[0];
        } elseif (count($row) > 1 && !is_int($row[1])) {
            $long = $row[1];
        }

        $mode = $this->defaultMode;
        if (count($row) == 2 && is_int($row[1])) {
            $mode = $row[1];
        }

        return array($short, $long, $mode);
    }
}
