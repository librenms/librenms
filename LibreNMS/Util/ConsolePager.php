<?php

/**
 * ConsolePager.php
 *
 * Paginates long console output without losing colours or folding long lines.
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
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Util;

/**
 * Three modes, chosen by open():
 *   less   the text is streamed into an external pager (less by default):
 *          colours kept, long lines chopped instead of folded, screen not
 *          cleared on exit, and quits at once when the text fits one screen
 *   enter  fallback when no pager can be spawned: pause every N lines
 *   plain  no paging, useful when the output is redirected
 */
class ConsolePager
{
    public const MODE_LESS = 'less';

    public const MODE_ENTER = 'enter';

    public const MODE_PLAIN = 'plain';

    private const DEFAULT_PAGER = 'less -R -S -X -F';

    private string $mode = self::MODE_PLAIN;

    private int $printed = 0;

    private bool $quit = false;

    /** @var resource|null */
    private $pipe = null;

    /** @var resource|null */
    private $process = null;

    /** @var callable|null */
    private $writer;

    /**
     * @param  string|null  $command  pager command, "cat" or empty for no pager, null to auto detect
     * @param  int  $perPage  lines to print before pausing in enter mode
     * @param  callable|null  $writer  where the text goes, defaults to stdout
     * @param  bool|null  $interactive  override terminal detection, for tests
     */
    public function __construct(
        private ?string $command = null,
        private readonly int $perPage = 30,
        ?callable $writer = null,
        private readonly ?bool $interactive = null
    ) {
        $this->writer = $writer ?? fn (string $text) => print $text;
    }

    public function mode(): string
    {
        return $this->mode;
    }

    public function quitRequested(): bool
    {
        return $this->quit;
    }

    /**
     * Start the pager. Must be called before the first write.
     *
     * @return string the mode actually in use, which may be a fallback
     */
    public function open(): string
    {
        $command = $this->command ?? $this->detectCommand();

        // "cat" and an empty command mean no paging at all, whichever place
        // the command came from (option or environment)
        if (trim($command) === '' || strtolower(trim($command)) === 'cat') {
            $this->mode = self::MODE_PLAIN;

            return $this->mode;
        }

        if (! $this->interactive()) {
            $this->mode = self::MODE_PLAIN;

            return $this->mode;
        }

        // check the binary first: proc_open would happily start a shell that
        // then dies on "command not found", silently swallowing the output
        if (! $this->commandExists($command)) {
            $this->mode = self::MODE_ENTER;

            return $this->mode;
        }

        // a pager that goes away (the user pressed q) must not kill us with a
        // broken pipe
        if (function_exists('pcntl_signal') && defined('SIGPIPE')) {
            pcntl_signal(SIGPIPE, SIG_IGN);
        }

        $pipes = [];
        $this->process = @proc_open($command, [
            0 => ['pipe', 'r'],
            1 => STDOUT,
            2 => STDERR,
        ], $pipes);

        if (! is_resource($this->process) || ! isset($pipes[0]) || ! is_resource($pipes[0])) {
            $this->process = null;
            $this->mode = self::MODE_ENTER;

            return $this->mode;
        }

        $this->pipe = $pipes[0];
        $this->mode = self::MODE_LESS;

        return $this->mode;
    }

    /** Write without page accounting, for headers and summaries. */
    public function write(string $text): void
    {
        $this->emit($text);
    }

    /**
     * Write a block, then pause if needed. A page break lands between blocks,
     * so a caller can pass a whole table row as one block and never have it
     * split across pages.
     */
    public function page(string $text): void
    {
        if ($this->quit || $text === '') {
            return;
        }

        $this->emit($text);

        if ($this->mode === self::MODE_ENTER && $this->printed >= $this->perPage) {
            $this->pause();
        }
    }

    /** Let the pager consume what is left, then release the terminal. */
    public function close(): void
    {
        if (is_resource($this->pipe)) {
            @fclose($this->pipe);
            $this->pipe = null;
        }

        if (is_resource($this->process)) {
            @proc_close($this->process);
            $this->process = null;
        }
    }

    private function emit(string $text): void
    {
        if ($text === '') {
            return;
        }

        $this->printed += substr_count($text, "\n");

        if ($this->pipe === null) {
            ($this->writer)($text);

            return;
        }

        // a failed write means the pager is gone, stop instead of spinning on
        // a closed pipe for the rest of the report
        if (@fwrite($this->pipe, $text) === false) {
            $this->quit = true;
            $this->close();
        }
    }

    private function pause(): void
    {
        $this->printed = 0;
        $this->write("\n--- [Enter] continue, [q] quit ---\n");

        $answer = fgets(STDIN);

        if ($answer === false) {
            $this->quit = true; // stdin closed, do not loop forever

            return;
        }

        if (in_array(strtolower(trim($answer)), ['q', 'quit'], true)) {
            $this->quit = true;
            $this->write("\n");
        }
    }

    private function interactive(): bool
    {
        if ($this->interactive !== null) {
            return $this->interactive;
        }

        if (getenv('LIBRENMS_NO_PAGER') !== false) {
            return false;
        }

        // both ends must be a terminal: a piped stdin would either block on
        // the prompt or make a pager swallow the rest of the output
        return function_exists('stream_isatty')
            && @stream_isatty(STDOUT)
            && @stream_isatty(STDIN);
    }

    /**
     * --pager style option, then a dedicated environment variable, then the
     * standard PAGER, then less.
     */
    private function detectCommand(): string
    {
        $candidates = [];
        foreach (['LIBRENMS_PAGER', 'PAGER'] as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                $candidates[] = $value;
            }
        }

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);

            if ($candidate !== '' && strtolower($candidate) !== 'cat') {
                return $candidate;
            }
        }

        return $candidates === [] ? self::DEFAULT_PAGER : '';
    }

    private function commandExists(string $command): bool
    {
        // strtok returns false for an empty or blank command
        $binary = strtok(trim($command), " \t");
        if ($binary === false) {
            return false;
        }

        if (str_contains($binary, '/')) {
            return is_file($binary) && is_executable($binary);
        }

        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $dir) {
            if ($dir !== '' && is_file("$dir/$binary") && is_executable("$dir/$binary")) {
                return true;
            }
        }

        return false;
    }
}
