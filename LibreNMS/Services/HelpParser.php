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

use Illuminate\Support\Str;
use LibreNMS\Config;
use Symfony\Component\Process\Process;

class HelpParser
{
    /** @var \LibreNMS\Services\CheckParameter[] */
    private $params = [];

    /**
     * @param  string  $check
     * @return \LibreNMS\Services\CheckParameter[]
     */
    public function parse(string $check): array
    {
        $command = [Config::get('nagios_plugins') . '/' . $check, '--help'];
        $process = new Process($command);
        $process->run();

        foreach (explode("\n", $process->getOutput()) as $line) {
            // parse usage section, includes optional/required information
            if (preg_match("/^(Usage:)?\s*$check [-[{]/", $line)) {
                if (isset($usage)) {
                    $this->parseUsage( $usage);
                }
                $usage = trim($line);
            } elseif(isset($usage)) {
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

    /**
     * @param  string  $usage
     * @return void
     */
    private function parseUsage(string $usage)
    {
        preg_match_all('/[{[]?(-{1,2}\w+)( [^-[\]{}|]+)?[}\]|]?/', $usage, $matches);

        $group = [];
        $required = false;
        foreach ($matches[0] as $index => $match) {
            $short = $matches[1][$index];
            $value = trim($matches[2][$index]);

            $this->setParameter(new CheckParameter('', $short, $value));

            // check the starting character if we aren't in a group
            if (empty($group) && Str::startsWith($match, ['-', '{'])) {
                $required = true;
            }

            // if $required is set, set it on the parameter
            $this->params[$short]->setRequired($required);

            if (! empty($group)) {
                // keep adding to the group until we find the end
                $group[] = $short;

                // if found the last one, save the group to all members
                if (Str::endsWith($match, ['}', ']'])) {
                    foreach ($group as $member) {
                        $this->params[$member]->setExclusiveGroup($group);
                    }
                    $group = []; // clear group
                    $required = false; // clear required status
                }
            } elseif (Str::endsWith($match, '|')) {
                // the start of a group
                $group[] = $short;
            } else {
                $required = false; // not a group, reset $required
            }
        }
    }

    private function setParameter(CheckParameter $param)
    {
        $key = $param->short ?: $param->param;
        if (isset($this->params[$key])) {
            foreach (['short', 'param', 'value', 'description', 'required', 'group'] as $field) {
                $this->params[$key]->$field = $param->$field ?: $this->params[$key]->$field;
            }
        } else {
            $this->params[$key] = $param;
        }
    }
}
