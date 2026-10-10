<?php

/**
 * PhpRrd.php
 *
 * RRD backend using the php-rrd extension, which calls librrd in-process
 * instead of piping commands to an rrdtool process.
 *
 * php-rrd capabilities (from the php-rrd and librrd source):
 *  - rrd_create(), rrd_update(), rrd_tune() and RRDGraph accept --daemon and go through rrdcached.
 *    rrd_tune() sends TUNE to rrdcached with newer librrd, older versions flush and edit the local file.
 *  - rrd_last(), rrd_first() and rrd_info() only take a file name and read the local file,
 *    and there is no list function.
 *
 * So writes and graphs run in-process here, while exists() and list() are inherited from the
 * rrdtool backend: local file checks without rrdcached, otherwise an rrdtool process
 * (started on first use) asks rrdcached, because the files may not be local.
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\RRD\Backend;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdUnknownException;
use LibreNMS\RRD\RrdPath;
use Log;

class PhpRrd extends Rrdtool
{
    /**
     * @param  string|null  $rrdcached  rrdcached address to send commands to
     *
     * @throws RrdUnknownException
     */
    public function __construct(?string $rrdcached)
    {
        if (! extension_loaded('rrd')) {
            throw new RrdUnknownException('The php-rrd extension is not loaded');
        }

        parent::__construct($rrdcached);
    }

    public function __destruct()
    {
        // librrd keeps its rrdcached connection in global state, release it with the backend
        if ($this->rrdcached) {
            rrdc_disconnect();
        }
    }

    /**
     * @param  string[]  $arguments
     *
     * @throws RrdException
     */
    protected function createFile(RrdPath $rrd, array $arguments): void
    {
        $arguments = [...$this->daemon(), ...$arguments, '-O'];
        Log::debug("PHPRRD[%gcreate $rrd " . implode(' ', $arguments) . '%n]', ['color' => true]);

        if (! $this->inRrdDir(fn () => rrd_create($rrd->relativePath(), $arguments))) {
            throw RrdException::parse(rrd_error());
        }
    }

    /**
     * @param  array<int|float|string|null>  $values
     *
     * @throws RrdException
     */
    public function update(RrdPath $rrd, array $values, ?int $timestamp = null): void
    {
        $arguments = [...$this->daemon(), ($timestamp ?? 'N') . ':' . $this->formatValues($values)];
        Log::debug("PHPRRD[%gupdate $rrd " . implode(' ', $arguments) . '%n]', ['color' => true]);

        // \RRDUpdater can't be given --daemon, so use the function
        if (! $this->inRrdDir(fn () => rrd_update($rrd->relativePath(), $arguments))) {
            throw RrdException::parse(rrd_error());
        }
    }

    /**
     * @param  array<string, array{min?: int|float|null, max?: int|float|null}>  $limits
     *
     * @throws RrdException
     */
    public function tune(RrdPath $rrd, array $limits): void
    {
        $arguments = [...$this->daemon(), ...$this->limitArguments($limits)];
        Log::debug("PHPRRD[%gtune $rrd " . implode(' ', $arguments) . '%n]', ['color' => true]);

        if ($this->remote() && version_compare(LibrenmsConfig::get('rrdtool_version', '0'), '1.8.0', '<')) {
            parent::tune($rrd, $limits);

            return;
        }

        if (! $this->inRrdDir(fn () => rrd_tune($rrd->relativePath(), $arguments))) {
            throw RrdException::parse(rrd_error());
        }
    }

    /**
     * @throws RrdException
     */
    public function last(RrdPath $rrd): int
    {
        return $this->remote() ? parent::last($rrd) : $this->inRrdDir(fn () => rrd_last($rrd->relativePath()));
    }

    /**
     * librrd only reads the timezone from the TZ environment variable, which is process wide,
     * so this is not safe to use from multiple threads in the same process.
     *
     * @param  string[]  $options
     */
    public function graph(array $options, ?string $timezone = null): string
    {
        // librrd calls tzset() during graph init, so TZ must be set in the environment
        $savedTz = getenv('TZ');
        if ($timezone) {
            putenv("TZ=$timezone");
        }

        $options = [...$this->daemon(), ...$options];
        Log::debug('PHPRRD[%ggraph ' . implode(' ', $options) . '%n]', ['color' => true]);
        $graph = new \RRDGraph('-');
        $graph->setOptions($options);
        try {
            $data = $this->inRrdDir($graph->saveVerbose(...));
        } catch (\Exception $e) {
            throw new RrdGraphException($e->getMessage());
        } finally {
            if ($timezone) {
                putenv($savedTz === false ? 'TZ' : "TZ=$savedTz");
            }
        }

        return $data['image'];
    }

    /**
     * librrd resolves file names from the working directory, which is process wide, so run in the
     * rrd directory like the rrdtool process does. A remote rrdcached may have no local directory.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function inRrdDir(callable $callback): mixed
    {
        $rrdDir = (string) LibrenmsConfig::get('rrd_dir');
        $savedCwd = is_dir($rrdDir) ? getcwd() : false;
        if ($savedCwd !== false) {
            chdir($rrdDir);
        }

        try {
            return $callback();
        } finally {
            if ($savedCwd !== false) {
                chdir($savedCwd);
            }
        }
    }

    /**
     * @return string[] librrd arguments to send the command through rrdcached when it is set
     */
    private function daemon(): array
    {
        return $this->rrdcached ? ['--daemon', $this->rrdcached] : [];
    }
}
