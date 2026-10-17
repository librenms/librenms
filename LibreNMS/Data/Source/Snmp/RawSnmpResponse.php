<?php

/**
 * RawSnmpResponse.php
 *
 * SnmpResponse subclass built from raw net-snmp command line output.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Snmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\StringHelpers;

class RawSnmpResponse extends SnmpResponse
{
    /**
     * Grammar of the left side of a varbind as printed by net-snmp.
     *
     * net-snmp always prints the whole instance, so a varbind left side is an object
     * name followed by an index: dotted numeric (or quoted string) sub-identifiers in
     * the default output format, or -OX bracket groups whose contents are printed
     * raw (spaces, quotes and even ']' are not escaped).  A bare name never carries
     * a value (snmptranslate output has no " = " delimiter), which keeps index-less
     * lines such as "VERSION = 42.3" out of the values.
     *
     * The optional trailing unclosed bracket tolerates the broken renders net-snmp
     * emits for tables whose MIB index definition cannot be resolved.
     *
     * Verified against net-snmp 5.9.4 output for every -O combination LibreNMS can
     * request: -On, -Os, -Ob, -Ou and the default -OQUteX.
     */
    private const VARBIND_OID_REGEX = '/
        ^
        (?:
            \.?\d+(?:\.\d+)+          # -On numeric: .1.3.6.1.2.1.1.1.0
            |
            (?:[A-Za-z0-9][\w-]*::)?   # optional MIB module, i.e. IF-MIB::
            [A-Za-z_][\w-]*           # object name
            (?:
                (?:\.\d+|\."[^"]*")+   # dotted index: .1 | ."string"
                |
                (?:\[.*?\])+           # -OX bracket index groups (raw contents)
                (?:\.\d+)*
                (?:\[[^\]]*)?          # tolerate a trailing unclosed bracket
            )
            |
            \.?iso\.org\.dod\.internet  # -Ou: the full symbolic path from the root
            (?:\.[A-Za-z_][\w-]*)+
            (?:\.\d+)+
        )
        $
        /x';

    protected readonly string $raw;
    private ?bool $inferValueEncoding = null;

    /**
     * Create a new raw response object filled with output from the net-snmp command.
     *
     * @param  array<int, string>  $command
     */
    public function __construct(
        string $output = '',
        string $stderr = '',
        int $exitCode = 0,
        array $command = [],
    ) {
        parent::__construct(
            rawValues: [],
            stderr: $stderr,
            exitCode: $exitCode,
            command: $command,
        );

        $this->raw = (string) preg_replace('/Wrong Type \(should be .*\): /', '', $output);
    }

    protected function findBadString(): ?string
    {
        if (preg_match(self::BAD_STRING_REGEX, $this->raw, $errors)) {
            return $errors[0];
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return empty($this->raw);
    }

    /**
     * Parse the raw net-snmp output into varbinds.
     *
     * net-snmp prints values that contain newlines over several lines, so lines have
     * to be assigned to the varbind they belong to:
     *
     * - Values of objects without a known DISPLAY-HINT (MIB not loaded, or a plain
     *   OCTET STRING) are printed quoted with embedded quotes and backslashes
     *   escaped.  While such a value is open, nothing can start a new varbind.
     * - Values of DisplayString style objects are printed raw without any quoting,
     *   so a continuation line is only recognized by not matching the varbind
     *   grammar above.
     * - A single response normally only contains varbinds of the requested object,
     *   so when the command is available, a line repeating a different object name
     *   is a continuation even if it is shaped like a varbind.
     *
     * @return array<string, mixed>
     */
    public function values(): array
    {
        if (! empty($this->values)) {
            return $this->values;
        }

        $this->inferValueEncoding ??= ! StringHelpers::isValidUtf8($this->raw);
        $this->values = [];

        $lines = explode(PHP_EOL, $this->raw);
        while ($lines !== [] && end($lines) === '') {
            array_pop($lines); // net-snmp output ends with a newline
        }

        $state = $this->readLines($lines);

        if ($state['open'] !== null) {
            // The quote never closed, so it was part of a raw value, not a quoted one.
            // Re-read the swallowed text without quote tracking to keep the varbinds after it.
            $this->readLines($lines, from: $state['open'], trackQuotes: false, strict: $state['strict'], base: $state['base']);
        }

        return $this->values;
    }

    /**
     * Read varbinds from $lines into the values, starting at $from.
     *
     * @param  string[]  $lines
     * @param  int  $from  index of the first line to read
     * @param  bool  $trackQuotes  consume a quoted value up to its closing quote
     * @param  bool|null  $strict  request context decided by a previous read
     * @param  string|null  $base  object name of the varbind read before $from
     * @return array{open: ?int, strict: ?bool, base: ?string} line index of a quoted value that never closed
     */
    private function readLines(array $lines, int $from = 0, bool $trackQuotes = true, ?bool $strict = null, ?string $base = null): array
    {
        $requestedBases = $this->requestedBases();
        $oid = null;        // left side of the varbind currently being read
        $value = '';
        $quoteOpen = false; // inside a quoted value that has not closed yet
        $scanned = 0;       // length of $value already checked for the closing quote
        $escaped = false;   // backslash escape state carried between lines
        $openLine = null;
        $openState = ['strict' => $strict, 'base' => $base];

        foreach ($lines as $i => $line) {
            if ($i < $from) {
                continue;
            }

            if ($quoteOpen) {
                $value .= PHP_EOL . $line;
                $this->scanClosingQuote($value, $scanned, $escaped, $quoteOpen);
                continue;
            }

            $parts = explode(self::KEY_VALUE_DELIMITER, $line, 2);

            if (count($parts) === 2) {
                if (preg_match(self::BAD_VALUE_REGEX, $line)) {
                    continue; // agent error text, not data
                }

                if ($oid === null || $this->isVarbindStart($parts[0], $strict, $base, $requestedBases)) {
                    $this->storeValue($oid, $value);

                    [$oid, $value] = $parts;
                    $candidateBase = $this->oidBase($oid);
                    $candidateStrict = $strict ?? $this->isRequested($oid);

                    if ($trackQuotes && str_starts_with($value, '"')) {
                        $quoteOpen = true;
                        $openLine = $i;
                        $openState = ['strict' => $strict, 'base' => $base];
                        $scanned = 1; // the opening quote itself does not close the value
                        $escaped = false;
                        $this->scanClosingQuote($value, $scanned, $escaped, $quoteOpen);
                    }

                    $base = $candidateBase;
                    $strict = $candidateStrict;
                    continue;
                }
            }

            if ($oid !== null) {
                // net-snmp prints values containing newlines over several lines
                $value .= PHP_EOL . $line;
            } else {
                // no delimiter at all (snmptranslate style output), keep it unindexed
                [$oid, $value] = ['', $line];
            }
        }

        if ($quoteOpen) {
            // the quote never closed: its varbind is not stored, re-reading from
            // $openLine with the state it was accepted in
            return ['open' => $openLine, 'strict' => $openState['strict'], 'base' => $openState['base']];
        }

        $this->storeValue($oid, $value);

        return ['open' => null, 'strict' => $strict, 'base' => $base];
    }

    /**
     * Advance the scan for the closing quote of a quoted value over newly read text.
     */
    private function scanClosingQuote(string $value, int &$scanned, bool &$escaped, bool &$open): void
    {
        for ($i = $scanned; $i < strlen($value); $i++) {
            if ($escaped) {
                $escaped = false;
            } elseif ($value[$i] === '\\') {
                $escaped = true;
            } elseif ($value[$i] === '"') {
                $open = false;

                return;
            }
        }

        $scanned = strlen($value);
    }

    /**
     * Decide if a line starts a new varbind or continues the value being read.
     *
     * A varbind is recognized by the grammar of its left side.  Responses of a single
     * object additionally have to repeat it, which keeps varbind-shaped lines inside
     * a multi-line value from creating phantom rows.
     */
    private function isVarbindStart(string $candidate, ?bool $strict, ?string $previousBase, array $requestedBases): bool
    {
        // fast exit: a varbind left side always has an index, never just a name
        if (! str_contains($candidate, '.') && ! str_contains($candidate, '[')) {
            return false;
        }

        if (! preg_match(self::VARBIND_OID_REGEX, $candidate)) {
            return false;
        }

        if ($strict !== true) {
            return true; // no request context, or a response covering several objects
        }

        $candidateBase = $this->oidBase($candidate);

        if ($candidateBase === '') {
            return $this->isUnderRequestedNumeric($candidate);
        }

        return $candidateBase === $previousBase || in_array($candidateBase, $requestedBases, true);
    }

    /**
     * Does this varbind belong to the requested OIDs?  Numeric requests match by
     * prefix, symbolic ones by object name.
     */
    private function isRequested(string $oid): bool
    {
        $base = $this->oidBase($oid);

        return $base !== '' ? in_array($base, $this->requestedBases(), true) : $this->isUnderRequestedNumeric($oid);
    }

    /**
     * Is this numeric left side under one of the numerically requested OIDs?
     */
    private function isUnderRequestedNumeric(string $oid): bool
    {
        if (! preg_match('/^\.?\d+(?:\.\d+)*$/', $oid)) {
            return false;
        }

        $candidate = ltrim($oid, '.');

        foreach ($this->requestedOids() as $requestedOid) {
            $prefix = ltrim($requestedOid, '.');
            if (preg_match('/^\.?\d+(?:\.\d+)*$/', $prefix) && ($candidate === $prefix || str_starts_with($candidate, $prefix . '.'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The OIDs requested by the command that produced this output, empty when unavailable.
     *
     * @return string[]
     */
    private function requestedOids(): array
    {
        $oids = [];
        for ($i = count($this->command) - 1; $i >= 0; $i--) {
            $arg = $this->command[$i];

            // the agent target (transport:host:port) ends the arguments,
            // and no OID starts with a flag dash
            if (preg_match('/:\d+$/', $arg) || str_starts_with($arg, '-')) {
                break;
            }

            array_unshift($oids, $arg);
        }

        return $oids;
    }

    /**
     * The object names of the requested OIDs, numeric OIDs have none.
     *
     * @return string[]
     */
    private function requestedBases(): array
    {
        $bases = [];
        foreach ($this->requestedOids() as $oid) {
            if ($base = $this->oidBase($oid)) {
                $bases[] = $base;
            }
        }

        return $bases;
    }

    /**
     * The object name of a varbind left side or requested OID: without MIB module
     * and without index.  Numeric OIDs have no name and yield an empty string.
     */
    private function oidBase(string $oid): string
    {
        $oid = ltrim((string) preg_replace('/^[A-Za-z0-9][\w-]*::/', '', $oid), '.');

        return preg_match('/^[A-Za-z_][\w-]*/', $oid, $matches) ? $matches[0] : '';
    }

    /**
     * Store a finished varbind, applying the same value clean up to every one of them.
     */
    private function storeValue(?string $oid, string $value): void
    {
        if ($oid === null) {
            return;
        }

        if (LibrenmsConfig::get('snmp.unescape')) {
            $value = stripslashes($value);
        }

        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = trim(stripslashes($value), "\" \n\r");
        } else {
            $value = trim($value);
        }

        $this->values[$oid] = $this->inferValueEncoding
            ? StringHelpers::inferEncoding($value)
            : $value;
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function append(SnmpResponse $response): SnmpResponse
    {
        if ($response instanceof RawSnmpResponse) {
            $newResponse = new RawSnmpResponse(
                $this->raw . $response->raw,
                $this->stderr . $response->stderr,
                $this->exitCode ?: $response->exitCode,
                $response->command ?: $this->command,
            );
        } else {
            $newResponse = new SnmpResponse(
                array_merge($this->values(), $response->values()),
                $this->stderr . $response->stderr,
                $this->exitCode ?: $response->exitCode,
                $response->command ?: $this->command,
            );
        }

        $newResponse->errorMessage = $this->errorMessage ?: $response->errorMessage;

        return $newResponse;
    }

    public function __sleep()
    {
        return ['raw', 'exitCode', 'stderr', 'command', 'values', 'errorMessage'];
    }
}
