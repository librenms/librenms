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

class SimpleTemplate
{
    private string $regex = '/{{ \$?([a-zA-Z0-9\-_.:]+)(\|[^}]+)? }}/';
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
     * Parse and apply filters to a variable value
     */
    private function applyFilters(string $value, string $filterChain): string
    {
        $filterPattern = '/([a-zA-Z_][a-zA-Z0-9_]*)(?:\(([^)]*)\))?/';

        if (preg_match_all($filterPattern, trim($filterChain, '|'), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $filterName = $match[1];
                $argsString = $match[2] ?? '';
                $args = !empty($argsString) ? $this->parseArguments($argsString) : [];
                $value = $this->executeFilter($value, $filterName, $args);
            }
        }

        return $value;
    }

    /**
     * Parse function arguments from string
     */
    private function parseArguments(string $argsString): array
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
                $args[] = $this->parseArgumentValue($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $args[] = $this->parseArgumentValue($current);
        }

        return $args;
    }

    /**
     * Parse individual argument value (string, number, boolean)
     */
    private function parseArgumentValue(string $value): mixed
    {
        $decoded = json_decode($value);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Handle unquoted strings (not valid JSON but common in templates)
        return $value;
    }

    /**
     * Execute a specific filter on a value
     */
    private function executeFilter(string $value, string $filterName, array $args): string
    {
        return match ($filterName) {
            'trim' => trim($value, ...($args ?: [" \t\n\r\0\x0B"])),
            'upper' => strtoupper($value),
            'lower' => strtolower($value),
            'ucfirst' => ucfirst($value),
            'ucwords' => ucwords($value),
            'length' => (string) strlen($value),
            'replace' => count($args) >= 2 ? str_replace($args[0], $args[1], $value) : $value,
            'substr' => substr($value, ...$args) ?: $value,
            'reverse' => strrev($value),
            'md5' => md5($value),
            'sha1' => sha1($value),
            'base64_encode' => base64_encode($value),
            'base64_decode' => base64_decode($value, true) ?: $value,
            'urlencode' => urlencode($value),
            'urldecode' => urldecode($value),
            'htmlentities' => htmlentities($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'html_entity_decode' => html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'strip_tags' => strip_tags($value, ...$args),
            'nl2br' => nl2br($value),
            'addslashes' => addslashes($value),
            'stripslashes' => stripslashes($value),
            'number_format' => number_format((float) $value, ...$args),
            'date' =>  date($args[0] ?? 'Y-m-d H:i:s', is_numeric($value) ? (int) $value : (strtotime($value) ?: time())),
            'json_encode' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $value,
            'truncate' => (isset($args[0]) && strlen($value) > $args[0]) ? substr($value, 0, $args[0]) . ($args[1] ?? '...') : $value,
            'slug' => strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-')),
            'default' => empty($value) && isset($args[0]) ? (string) $args[0] : $value,
            default => $value
        };
    }

    public function __toString(): string
    {
        return preg_replace_callback($this->regex, $this->callback ?? function ($matches) {
            $variableName = $matches[1];
            $value = $this->variables[$variableName] ?? ($this->keepEmpty ? $matches[0] : '');

            if (!StringHelpers::isStringable($value)) {
                return '';
            }

            $stringValue = (string) $value;

            // Apply filters if present
            if (!empty($matches[2])) {
                $stringValue = $this->applyFilters($stringValue, $matches[2]);
            }

            return $stringValue;
        }, $this->template);
    }
}
