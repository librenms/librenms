<?php
/**
 * HelpParser.php
 *
 * -Description-
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services;

use Illuminate\Support\Collection;
use LibreNMS\Config;
use Symfony\Component\Process\Process;

class HelpParser
{
    /** @var \Illuminate\Support\Collection<\LibreNMS\Services\CheckParameter> */
    protected $params;

    public function __construct()
    {
        $this->params = new Collection;
    }

    /**
     * @param  string  $check
     * @return \Illuminate\Support\Collection<\LibreNMS\Services\CheckParameter>
     */
    public function parse(string $check): Collection
    {
        $usage_found = false;

        foreach (explode("\n", $this->fetchHelp($check)) as $line) {
            // parse usage section, includes optional/required information
            if (! $usage_found) { // only find usage once
                if (preg_match("/^(Usage:)?\s*$check [-[{]/", $line)) {
                    if (isset($usage)) {
                        $this->parseUsage($usage);
                    }
                    $usage = trim($line);
                } elseif (isset($usage)) {
                    if (empty($line)) {
                        $this->parseUsage($usage);
                        $usage_found = true;
                        unset($usage);
                    } else {
                        $usage .= ' ' . trim($line);
                    }
                }
            }

            // parse option lines
            if (preg_match('/^\s*((?<short>-\w)[, ]*|\(?(?<param>--[\w-]+)\)?){1,2}(\s*=\s*(?<value>.+?))?(, --|$)/', $line, $param_matches)) {
                if (isset($pending)) {
                    $this->setParameter($pending);
                    unset($pending);
                }
                $pending = new CheckParameter($param_matches['param'] ?? '', $param_matches['short'] ?? '', $param_matches['value'] ?? '');
            } elseif (isset($pending)) {
                if (empty($line)) {
                    $this->setParameter($pending);
                    unset($pending);
                } else {
                    $pending->appendDescription($line);
                }
            }
        }

        return $this->params;
    }

    protected function fetchHelp(string $check): string
    {
        $command = [Config::get('nagios_plugins') . '/' . $check, '--help'];
        $process = new Process($command);
        $process->run();

        return $process->getOutput();
    }

    /**
     * @param  string  $usage
     * @return void
     */
    private function parseUsage(string $usage): void
    {
        $usage .= ' '; // make regex easier
        $optional_args = [];
        $required_args = [];
        $usage = str_replace(' | -', '|-', $usage); // remove spaces that complicate parsing
        $filtered = preg_replace_callback('/\[(\[?-\w.*?)] /', function ($match) use (&$optional_args) {
            $optional_args[] = $match[1];

            return '';
        }, $usage);

        preg_match_all('/(?<= )-\w \S+/', $filtered, $required_args);

        foreach ($required_args[0] as $entry) {
            $this->parseOptionGroup($entry, true);
        }
        foreach ($optional_args as $entry) {
            $this->parseOptionGroup($entry, false);
        }
    }

    private function parseOptionGroup(string $group, bool $required): void
    {
        $group_params = new Collection;
        $exclusive = true;

        // check for group with optional value
        if (preg_match('/\[(?<group>[^]]+)](?<value>\w*)/', $group, $multi_value_matches)) {
            $group = $multi_value_matches['group'];
            $value = $multi_value_matches['value'];
        }

        // check for a group
        $args = preg_split('/(\||]\[)/', $group);
        if (count($args) === 1) {
            $exclusive = false;
            preg_match_all('/((?<= )-\w \S+|^-\w \S+)/', $group, $inclusive_args);
            $args = $inclusive_args[0];
        }
        foreach ($args as $arg) {
            $parts = explode(' ', $arg, 2);
            $group_params->push((new CheckParameter('', $parts[0], $value ?? $parts[1] ?? ''))->setRequired($required));
        }

        // set group
        $group_keys = $group_params->pluck('short')->all();
        foreach ($group_params as $param) {
            if (count($group_params) > 1) {
                $param = $exclusive ? $param->setExclusiveGroup($group_keys) : $param->setInclusiveGroup($group_keys);
            }
            $this->setParameter($param);
        }
    }

    private function setParameter(CheckParameter $param): void
    {
        $key = $param->short ?: $param->param;

        // if existing, update fields.
        if ($current_param = $this->params->get($key)) {
            $current_param->short = $param->short ?? $current_param->short;
            $current_param->param = $param->param ?? $current_param->param;
            $current_param->value = $param->value ?? $current_param->value;
            $current_param->description = $param->description ?? $current_param->description;
            $current_param->inclusive_group = $param->inclusive_group ?? $current_param->inclusive_group;
            $current_param->exclusive_group = $param->exclusive_group ?? $current_param->exclusive_group;

            $current_param->required = $param->required || $current_param->required;
            $current_param->default = $param->default || $current_param->default;

            return;
        }

        $this->params->put($key, $param);
    }
}
