<?php
/**
 * FileCategorizer.php
 *
 * Categorizes files in LibreNMS
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Str;

class FileCategorizer extends Categorizer
{
    private const TESTS_REGEX = '#^tests/(snmpsim|data)/(([0-9a-z\-]+)(_[0-9a-z\-]+)?)(_[0-9a-z\-]+)?\.(json|snmprec)$#';

    public function __construct($items = [])
    {
        parent::__construct($items);

        if (getenv('CIHELPER_DEBUG')) {
            $this->setSkippable(function ($item) {
                return in_array($item, [
                    '.github/workflows/test.yml',
                    'LibreNMS/Util/CiHelper.php',
                    'LibreNMS/Util/FileCategorizer.php',
                    'app/Console/Commands/DevCheckCommand.php',
                    'tests/Unit/CiHelperTest.php',
                ]);
            });
        }

        $this->addCategory('php', function ($item) {
            return Str::endsWith($item, '.php') ? $item : false;
        });
        $this->addCategory('docs', function ($item) {
            return (Str::startsWith($item, 'doc/') || $item == 'mkdocs.yml') ? $item : false;
        });
        $this->addCategory('python', function ($item) {
            return Str::endsWith($item, '.py') ? $item : false;
        });
        $this->addCategory('bash', function ($item) {
            return Str::endsWith($item, '.sh') ? $item : false;
        });
        $this->addCategory('svg', function ($item) {
            return Str::endsWith($item, '.svg') ? $item : false;
        });
        $this->addCategory('resources', function ($item) {
            return Str::startsWith($item, 'resources/') ? $item : false;
        });
        $this->addCategory('full-checks', function ($item) {
            return in_array($item, ['composer.lock', '.github/workflows/test.yml']) ? $item : false;
        });
        $this->addCategory('os-files', function ($item) {
            if (($os_name = $this->osFromFile($item)) !== null) {
                return ['os' => $os_name, 'file' => $item];
            }

            return false;
        });
    }

    public function categorize()
    {
        parent::categorize();

        // split out os
        $this->categorized['os'] = array_unique(array_column($this->categorized['os-files'], 'os'));
        $this->categorized['os-files'] = array_column($this->categorized['os-files'], 'file');

        // If we have more than 4 (arbitrary number) of OS' then blank them out
        // Unit tests may take longer to run in a loop so fall back to all.
        if (count($this->categorized['os']) > 4) {
            $this->categorized['full-checks'] = [true];
        }

        return $this->categorized;
    }

    private function validateOs($os)
    {
        return file_exists("includes/definitions/$os.yaml") ? $os : null;
    }

    private function osFromFile($file)
    {
        if (Str::startsWith($file, 'includes/definitions/')) {
            return basename($file, '.yaml');
        } elseif (Str::startsWith($file, ['includes/polling', 'includes/discovery'])) {
            return $this->validateOs(basename($file, '.inc.php'));
        } elseif (preg_match('#LibreNMS/OS/[^/]+.php#', $file)) {
            return $this->osFromClass(basename($file, '.php'));
        } elseif (preg_match(self::TESTS_REGEX, $file, $matches)) {
            if ($this->validateOs($matches[3])) {
                return $matches[3];
            }
            if ($this->validateOs($matches[2])) {
                return $matches[2];
            }
        }

        return null;
    }

    /**
     * convert class name to os name
     *
     * @param string $class
     * @return string|null
     */
    private function osFromClass($class)
    {
        preg_match_all('/[A-Z][a-z0-9]*/', $class, $segments);
        $osname = implode('-', array_map('strtolower', $segments[0]));
        $osname = preg_replace(
            ['/^zero-/', '/^one-/', '/^two-/', '/^three-/', '/^four-/', '/^five-/', '/^six-/', '/^seven-/', '/^eight-/', '/^nine-/'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $osname
        );

        if ($os = $this->validateOs($osname)) {
            return $os;
        }

        return $this->validateOs(str_replace('-', '_', $osname));
    }
}
