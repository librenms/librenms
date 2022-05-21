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
    private $params;

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
        foreach (explode("\n", $this->fetchHelp($check)) as $line) {
            // parse usage section, includes optional/required information
            if (preg_match("/^(Usage:)?\s*$check [-[{]/", $line)) {
                if (isset($usage)) {
                    $this->parseUsage($usage);
                }
                $usage = trim($line);
            } elseif (isset($usage)) {
                if (empty($line)) {
                    $this->parseUsage($usage);
                    unset($usage);
                } else {
                    $usage .= ' ' . trim($line);
                }
            }

            // parse option lines
            if (preg_match('/^\s*((?<short>-\w)[, ]*|\(?(?<param>--[\w-]+)\)?){1,2}(\s*=\s*(?<value>.+))?$/', $line, $param_matches)) {
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
        $usage .= ' ';
        $optional_args = [];
        $required_args = [];
        $filtered = preg_replace_callback('/\[(-\w.*?)\] /', function ($match) use (&$optional_args) {
            $optional_args[] = $match[1];
            return '';
        }, $usage);

        preg_match('/(?<= )-\w \S+/', $filtered, $required_args);

        foreach ($required_args as $entry) {
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

        $args = preg_split('/(\||]\[)/', $group);
        if (count($args) === 1) {
            $exclusive = false;
            preg_match_all('/((?<= )-\w \S+|^-\w \S+)/', $group, $inclusive_args);
            $args = $inclusive_args[0];
        }
        foreach ($args as $arg) {
            $parts = explode(' ', $arg, 2);
            $group_params->push((new CheckParameter('', $parts[0], $parts[1] ?? ''))->setRequired($required));
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
            foreach (['short', 'param', 'value', 'description', 'required', 'inclusive_group', 'exclusive_group', 'default'] as $field) {
                $current_param->$field = $param->$field ?? $current_param->$field;
            }

            return;
        }

        $this->params->put($key, $param);
    }
}
