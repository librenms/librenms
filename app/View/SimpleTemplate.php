<?php

/*
 * SimpleTemplate.php
 *
 * Simple variable substitution template
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View;

use LibreNMS\Util\StringHelpers;

class SimpleTemplate implements \Stringable
{
    private const TEMPLATE_PATTERN = '/{{ \$?([a-zA-Z0-9\-_.:]+)(\|[^}]+)? }}/';
    private const FILTER_PATTERN = '/([a-zA-Z_][a-zA-Z0-9_]*)(?:\(([^)]*)\))?/';
    /** @var array<string, \Closure(string, array): string>|null */
    private static ?array $filters = null;
    private bool $keepEmpty = false;
    /** @var ?callable */
    private $callback = null;

    public function __construct(
        private readonly string $template,
        private array $variables = []
    ) {
    }

    /**
     * By default, unmatched templates will be removed from the output, set this to keep them
     */
    public function keepEmptyTemplates(): SimpleTemplate
    {
        $this->keepEmpty = true;

        return $this;
    }

    /**
     * Add a variable to the set of possible substitutions
     */
    public function setVariable(string $key, string $value): SimpleTemplate
    {
        $this->variables[$key] = $value;

        return $this;
    }

    /**
     * Instead of using the given variables to replace {{ var }}
     * send the matched variable to this callback, which will return a string to replace it
     */
    public function replaceWith(callable $callback): SimpleTemplate
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Create and parse a simple template
     */
    public static function parse(string $template, array $variables): string
    {
        return (string) new static($template, $variables);
    }

    /**
     * Names of the filters in a template that do not exist, for example ['foobar'] for '{{ x|upper|foobar }}'
     *
     * @return string[]
     */
    public static function unknownFilters(string $template): array
    {
        preg_match_all(self::TEMPLATE_PATTERN, $template, $templates);
        preg_match_all(self::FILTER_PATTERN, implode('|', $templates[2]), $filters);

        return array_values(array_diff($filters[1], array_keys(self::filters())));
    }

    /**
     * Apply a filter chain to a value, for example filter('3.14159', '|round(1)')
     * Unknown filters leave the value unchanged
     */
    public static function filter(string $value, string $filterChain): string
    {
        if (preg_match_all(self::FILTER_PATTERN, trim($filterChain, '|'), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if ($filter = self::filters()[$match[1]] ?? null) {
                    $value = $filter($value, self::parseArguments($match[2] ?? ''));
                }
            }
        }

        return $value;
    }

    /**
     * Parse function arguments from string
     */
    private static function parseArguments(string $argsString): array
    {
        $args = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;
        $depth = 0;

        for ($i = 0, $len = strlen($argsString); $i < $len; $i++) {
            $char = $argsString[$i];

            if ($inQuotes) {
                if ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                }
                $current .= $char;
                continue;
            }

            if ($char === '"' || $char === "'") {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
                continue;
            }

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }

            if ($char === ',' && $depth === 0) {
                $args[] = self::parseArgumentValue($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $args[] = self::parseArgumentValue($current);
        }

        return $args;
    }

    /**
     * Parse individual argument value (string, number, boolean)
     */
    private static function parseArgumentValue(string $value): mixed
    {
        $value = trim($value);

        // Handle single-quoted strings by removing quotes directly since json decode doesn't work there
        if (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            return substr($value, 1, -1);
        }

        $decoded = json_decode($value);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Handle unquoted strings (not valid JSON but common in templates)
        return $value;
    }

    /**
     * Twig-compatible filters, called with the value and the filter arguments
     *
     * @return array<string, \Closure(string, array): string>
     */
    private static function filters(): array
    {
        return self::$filters ??= [
            // Basic string filters
            'trim' => fn (string $value, array $args) => trim($value, ...($args ?: [" \t\n\r\0\x0B"])),
            'upper' => fn (string $value, array $args) => strtoupper($value),
            'lower' => fn (string $value, array $args) => strtolower($value),
            'title' => fn (string $value, array $args) => ucwords(strtolower($value)),
            'capitalize' => fn (string $value, array $args) => ucfirst(strtolower($value)),
            'length' => fn (string $value, array $args) => (string) strlen($value),

            // String manipulation
            'replace' => fn (string $value, array $args) => count($args) >= 2 ? str_replace($args[0], $args[1], $value) : $value,
            'slice' => self::sliceFilter(...),

            // Encoding/escaping
            'escape' => self::escapeFilter(...),
            'url_encode' => fn (string $value, array $args) => urlencode($value),

            // HTML
            'striptags' => fn (string $value, array $args) => strip_tags($value, ...($args ?: [])),
            'nl2br' => fn (string $value, array $args) => nl2br($value),

            // Formatting
            'number_format' => self::numberFormatFilter(...),
            'date' => self::dateFilter(...),
            'json_encode' => fn (string $value, array $args) => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $value,

            // Utility
            'default' => fn (string $value, array $args) => ($value === '' && isset($args[0])) ? (string) $args[0] : $value,
            'abs' => fn (string $value, array $args) => (string) abs((float) $value),
            'round' => fn (string $value, array $args) => (string) round((float) $value, $args[0] ?? 0),
        ];
    }

    private static function sliceFilter(string $value, array $args): string
    {
        $start = $args[0] ?? 0;
        $length = $args[1] ?? null;

        if ($length === null) {
            return substr($value, $start) ?: $value;
        }

        return substr($value, $start, $length) ?: $value;
    }

    private static function escapeFilter(string $value, array $args): string
    {
        $strategy = $args[0] ?? 'html';

        return match ($strategy) {
            'js' => json_encode($value, JSON_UNESCAPED_UNICODE),
            'css' => self::escapeCss($value),
            'url' => urlencode($value),
            default => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), // html
        };
    }

    private static function escapeCss(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '\\\\$0', $value);
    }

    private static function numberFormatFilter(string $value, array $args): string
    {
        $decimals = $args[0] ?? 0;
        $decimalPoint = $args[1] ?? '.';
        $thousandsSep = $args[2] ?? ',';

        return number_format((float) $value, $decimals, $decimalPoint, $thousandsSep);
    }

    private static function dateFilter(string $value, array $args): string
    {
        $format = $args[0] ?? 'F j, Y H:i'; // Twig's default format

        if (is_numeric($value)) {
            $timestamp = (int) $value;
        } else {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                $timestamp = time();
            }
        }

        return date($format, $timestamp);
    }

    public function __toString(): string
    {
        return (string) preg_replace_callback(self::TEMPLATE_PATTERN, $this->callback ?? function ($matches) {
            $variableName = $matches[1];
            if ($this->keepEmpty && ! isset($this->variables[$variableName])) {
                return $matches[0]; // keep unmatched templates intact, including their filters
            }

            $value = $this->variables[$variableName] ?? '';

            if (! StringHelpers::isStringable($value)) {
                return '';
            }

            $stringValue = (string) $value;

            // Apply filters if present
            if (! empty($matches[2])) {
                $stringValue = self::filter($stringValue, $matches[2]);
            }

            return $stringValue;
        }, $this->template);
    }
}
