<?php
/**
 * ValidationResult.php
 *
 * Encapsulates the result of a validation test.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

class ValidationResult
{
    const FAILURE = 0;
    const WARNING = 1;
    const SUCCESS = 2;
    const INFO = 3;

    private $message;
    private $status;
    private $list_description = '';
    private $list;
    private $fix;

    /**
     * ValidationResult constructor.
     * @param string $message The message to describe this result
     * @param int $status The status of this result FAILURE, WARNING, or SUCCESS
     * @param string $fix a suggested fix to highlight for the user
     */
    public function __construct($message, $status, $fix = null)
    {
        $this->message = $message;
        $this->status = $status;
        $this->fix = $fix;
    }

    /**
     * Create a new ok Validation result
     * @param string $message The message to describe this result
     * @param string $fix a suggested fix to highlight for the user
     * @return ValidationResult
     */
    public static function ok($message, $fix = null)
    {
        return new self($message, self::SUCCESS, $fix);
    }

    /**
     * Create a new warning Validation result
     * @param string $message The message to describe this result
     * @param string $fix a suggested fix to highlight for the user
     * @return ValidationResult
     */
    public static function warn($message, $fix = null)
    {
        return new self($message, self::WARNING, $fix);
    }

    /**
     * Create a new informational Validation result
     * @param string $message The message to describe this result
     * @return ValidationResult
     */
    public static function info($message)
    {
        return new self($message, self::INFO);
    }

    /**
     * Create a new failure Validation result
     * @param string $message The message to describe this result
     * @param string $fix a suggested fix to highlight for the user
     * @return ValidationResult
     */
    public static function fail($message, $fix = null)
    {
        return new self($message, self::FAILURE, $fix);
    }

    /**
     * Returns the status an int representing
     * ValidationResult::FAILURE, ValidationResult::WARNING, or ValidationResult::SUCCESS
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function hasList()
    {
        return ! empty($this->list);
    }

    public function getList()
    {
        return $this->list;
    }

    public function setList($description, array $list)
    {
        if (is_array(current($list))) {
            $list = array_map(function ($item) {
                return implode(' ', $item);
            }, $list);
        }

        $this->list_description = $description;
        $this->list = $list;

        return $this;
    }

    public function hasFix()
    {
        return ! empty($this->fix);
    }

    public function getFix()
    {
        return $this->fix;
    }

    /**
     * The commands (generally) to fix the issue.
     * If there are multiple, use an array.
     *
     * @param string|array $fix
     * @return ValidationResult $this
     */
    public function setFix($fix)
    {
        $this->fix = $fix;

        return $this;
    }

    /**
     * Print out this result to the console.  Formatted nicely and with color.
     */
    public function consolePrint()
    {
        c_echo(str_pad('[' . $this->getStatusText($this->status) . ']', 12) . $this->message . PHP_EOL);

        if (isset($this->fix)) {
            c_echo("\t[%BFIX%n]: \n");
            foreach ((array) $this->fix as $fix) {
                c_echo("\t%B$fix%n\n");
            }
        }

        if (! empty($this->list)) {
            echo "\t" . $this->getListDescription() . ":\n";
            $this->printList();
        }
    }

    /**
     * Get the colorized string that represents the status of a ValidatonResult
     *
     * @return string
     */
    public static function getStatusText($status)
    {
        $table = [
            self::SUCCESS => '%gOK%n',
            self::WARNING => '%YWARN%n',
            self::FAILURE => '%RFAIL%n',
            self::INFO => '%CINFO%n',
        ];

        return $table[$status] ?? 'Unknown';
    }

    public function getListDescription()
    {
        return $this->list_description;
    }

    /**
     * Print a list of items up to a max amount
     * If over that number, a line will print the total items
     *
     * @param string $format format as consumed by printf()
     * @param int $max the max amount of items to print, default 15
     */
    private function printList($format = "\t %s\n", $max = 15)
    {
        foreach (array_slice($this->list, 0, $max) as $item) {
            printf($format, $item);
        }

        $extra = count($this->list) - $max;
        if ($extra > 0) {
            printf($format, " and $extra more...");
        }
    }
}
