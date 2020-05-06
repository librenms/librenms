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
use LibreNMS\Util\Version;
use LibreNMS\Validator;

class Python extends BaseValidation
{
    const PYTHON_MIN_VERSION = '3.4.0';

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        $version = Version::python();

        if (empty($version)) {
            $validator->fail('python3 not found');
            return; // no need to check anything else
        }

        $this->checkVersion($validator, $version);
        $this->checkExtensions($validator);
    }

    private function checkVersion(Validator $validator, $version)
    {
        if (version_compare($version, self::PYTHON_MIN_VERSION, '<')) {
            $validator->warn('Python version ' . self::PYTHON_MIN_VERSION . ' is the minimum supported version. We recommend you update Python to a supported version.');
        }
    }

    private function checkExtensions(Validator $validator)
    {
        $pythonExtensions = 'scripts/check_requirements.py';
        exec(Config::get('install_dir') . '/' . $pythonExtensions . ' -v', $output, $returnval);

        if ($returnval !== 0) {
            $validator->fail("Python3 module issue found: '" . ($output[0] ?? '') . "'");
        }
    }
}
