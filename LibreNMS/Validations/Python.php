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

    public static function pythonVersion()
    {
        $python_binary = exec('which python3');
        if (empty($python_binary)) {
            return null;
        }
        $output = exec($python_binary . ' --version');
        return explode(' ', $output)[1];
    }

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        $this->checkInstalled($validator);
        $this->checkVersion($validator);
        $this->checkExtensions($validator);
    }

    private function checkInstalled(Validator $validator)
    {
        if (empty(self::pythonVersion())) {
            $validator->fail('Python3 not found');
        }
    }

    private function checkVersion(Validator $validator)
    {
        // if update is not set to false and version is min or newer
        if (Config::get('update') && version_compare(self::pythonVersion(), self::PYTHON_MIN_VERSION, '<')) {
            $validator->warn("Python version " . self::PYTHON_MIN_VERSION . " is the minimum supported version. We recommend you update Python to a supported version (" . self::PYTHON_RECOMMENDED_VERSION . " suggested) to continue to receive updates. If you do not update Python, LibreNMS will continue to function but stop receiving bug fixes and updates.");
        }
    }

    private function checkExtensions(Validator $validator)
    {
        $pythonExtensions = 'scripts/check_requirements.py';
        exec(Config::get('install_dir') . '/' . $pythonExtensions . ' -v', $output, $returnval);

        if (in_array(intval($returnval), [1, 2])) {
                $validator->fail("Python3 Modul issue found: '" . $output. "'");
        }
    }
}
