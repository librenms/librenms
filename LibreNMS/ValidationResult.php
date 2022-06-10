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
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Illuminate\Support\Arr;

class ValidationResult
{
    public const FAILURE = 0;
    public const WARNING = 1;
    public const SUCCESS = 2;
    public const INFO = 3;

    /** @var string */
    private $message;
    /** @var int */
    private $status;
    /** @var string */
    private $list_description = '';
    /** @var array */
    private $list;
    /** @var string|null */
    private $fix;
    /** @var string|null */
    private $fixer;

    /**
     * ValidationResult constructor.
     *
     * @param  string  $message  The message to describe this result
     * @param  int  $status  The status of this result FAILURE, WARNING, or SUCCESS
     * @param  string|null  $fix  a suggested fix to highlight for the user
     */
    public function __construct(string $message, int $status, string $fix = null)
    {
        $this->message = $message;
        $this->status = $status;
        $this->fix = $fix;
    }

    /**
     * Create a new ok Validation result
     *
     * @param  string  $message  The message to describe this result
     * @param  string|null  $fix  a suggested fix to highlight for the user
     * @return ValidationResult
     */
    public static function ok(string $message, string $fix = null): ValidationResult
    {
        return new self($message, self::SUCCESS, $fix);
    }

    /**
     * Create a new warning Validation result
     *
     * @param  string  $message  The message to describe this result
     * @param  string|null  $fix  a suggested fix to highlight for the user
     * @return ValidationResult
     */
    public static function warn(string $message, string $fix = null): ValidationResult
    {
        return new self($message, self::WARNING, $fix);
    }

    /**
     * Create a new informational Validation result
     *
     * @param  string  $message  The message to describe this result
     * @return ValidationResult
     */
    public static function info(string $message): ValidationResult
    {
        return new self($message, self::INFO);
    }

    /**
     * Create a new failure Validation result
     *
     * @param  string  $message  The message to describe this result
     * @param  string|null  $fix  a suggested fix to highlight for the user
     * @return ValidationResult
     */
    public static function fail(string $message, string $fix = null): ValidationResult
    {
        return new self($message, self::FAILURE, $fix);
    }

    /**
     * Returns the status an int representing
     * ValidationResult::FAILURE, ValidationResult::WARNING, or ValidationResult::SUCCESS
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function hasList(): bool
    {
        return ! empty($this->list);
    }

    public function getList(): ?array
    {
        return $this->list;
    }

    public function setList(string $description, array $list): ValidationResult
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

    public function hasFix(): bool
    {
        return ! empty($this->fix);
    }

    /**
     * @return string|array|null
     */
    public function getFix()
    {
        return $this->fix;
    }

    /**
     * The commands (generally) to fix the issue.
     * If there are multiple, use an array.
     *
     * @param  string|array  $fix
     * @return ValidationResult $this
     */
    public function setFix($fix): ValidationResult
    {
        $this->fix = $fix;

        return $this;
    }

    /**
     * Print out this result to the console.  Formatted nicely and with color.
     */
    public function consolePrint(): void
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
    public static function getStatusText(int $status): string
    {
        $table = [
            self::SUCCESS => '%gOK%n',
            self::WARNING => '%YWARN%n',
            self::FAILURE => '%RFAIL%n',
            self::INFO => '%CINFO%n',
        ];

        return $table[$status] ?? 'Unknown';
    }

    public function getListDescription(): string
    {
        return $this->list_description;
    }

    public function toArray(): array
    {
        $resultStatus = $this->getStatus();
        $resultFix = $this->getFix();
        $resultList = $this->getList();

        return [
            'status' => $resultStatus,
            'statusText' => substr($this->getStatusText($resultStatus), 2, -2), // remove console colors
            'message' => $this->getMessage(),
            'fix' => Arr::wrap($resultFix),
            'fixer' => $this->getFixer(),
            'listDescription' => $this->getListDescription(),
            'list' => is_array($resultList) ? array_values($resultList) : [],
        ];
    }

    /**
     * Print a list of items up to a max amount
     * If over that number, a line will print the total items
     *
     * @param  string  $format  format as consumed by printf()
     * @param  int  $max  the max amount of items to print, default 15
     */
    private function printList(string $format = "\t %s\n", int $max = 15): void
    {
        foreach (array_slice($this->list, 0, $max) as $item) {
            printf($format, $item);
        }

        $extra = count($this->list) - $max;
        if ($extra > 0) {
            printf($format, " and $extra more...");
        }
    }

    /**
     * Fixer exists
     */
    public function hasFixer(): bool
    {
        return $this->fixer !== null;
    }

    /**
     * @return string|null the class of the fixer
     */
    public function getFixer(): ?string
    {
        return $this->fixer;
    }

    /**
     * Set fixer, optionally denote if this is fixable
     */
    public function setFixer(string $fixer, bool $fixable = true): ValidationResult
    {
        $this->fixer = $fixable ? $fixer : null;

        return $this;
    }
}
