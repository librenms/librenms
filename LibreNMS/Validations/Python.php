<?php
/**
 * Python.php
 *
 * Check that various Python modules and functions exist.
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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class Python extends BaseValidation
{
    const PYTHON_MIN_VERSION = '3.4.0';
    const PYTHON_RECOMMENDED_VERSION = '3.5.2';

    const PYTHON_REQUIREMENTS_FILE = 'requirements.txt';

    public static function pythonVersion() {
        return explode(' ', exec('python3 --version'))[1];
    }

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        $this->checkVersion($validator);
        $this->checkExtensions($validator);
    }

    private function checkVersion(Validator $validator)
    {
        // if update is not set to false and version is min or newer
        if (Config::get('update') && version_compare(self::pythonVersion(), self::PYTHON_MIN_VERSION, '<')) {
            $validator->warn("Python version " . self::PYTHON_MIN_VERSION . " is the minimum supported version. We recommend you update Python to a supported version (" . self::PYTHON_RECOMMENDED_VERSION . " suggested) to continue to receive updates. If you do not update Python, LibreNMS will continue to function but stop receiving bug fixes and updates.");
        }
    }

    /**
     * split string list to associative array of package with condition version
     *
     * @param array
     */
    private function dependency_split($list)
    {
        $splitted_list = [];
        foreach ($list as $p) {
            if (strpos($p, '==') !== false) {
                $cond = '==';
                list($package, $version, $condition) = array_merge(explode($cond, $p), [$cond]);
            } elseif (strpos($p, '<=') !== false) {
                $cond = '<=';
                list($package, $version, $condition) = array_merge(explode($cond, $p), [$cond]);
            } elseif (strpos($p, '>=') !== false) {
                $cond = '>=';
                list($package, $version, $condition) = array_merge(explode($cond, $p), [$cond]);
            } else {
                # no version specified
                list($package, $version, $condition) = [$p, null, null];
            }

            if (empty($package)) {
                continue;
            }

            $splitted_list[$package] = ['version' => $version, 'condition' => $condition];
        }
        return $splitted_list;
    }

    /**
     * reads python requirements file and returns a linewise array of file
     *
     */
    private function requirements()
    {
        $file_content = explode("\n", file_get_contents(Config::get('install_dir') . '/' . self::PYTHON_REQUIREMENTS_FILE));

        return $this->dependency_split($file_content);
    }

    private function checkExtensions(Validator $validator)
    {
        exec('pip3 freeze', $found_packages);

        $installed_packages = $this->dependency_split($found_packages);

        $needed_packages = $this->requirements();

        $package_list = array_keys($installed_packages);

        foreach ($needed_packages as $package => $args) {
            $version = $args['version'];
            $condition = $args['condition'];

            if (!in_array($package, $package_list)) {
                $validator->fail("Missing Python Package ".$package." ".$condition." ".$version);
                continue;
            } elseif ($condition == null) {
                continue;
            } elseif ($condition == '<=') {
                if ($installed_packages[$package]['version'] <= $needed_packages[$package]['version']) {
                    continue;
                }
            } elseif ($condition == '==') {
                if ($installed_packages[$package]['version'] == $needed_packages[$package]['version']) {
                    continue;
                }
            } elseif ($condition == '>=') {
                if ($installed_packages[$package]['version'] >= $needed_packages[$package]['version']) {
                    continue;
                }
            }

            $validator->warn("Python Package Version conflict found: ".$package." needs to be ".$condition." ".$version);
        }
    }
}
