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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\Util\Version;
use LibreNMS\Validator;
use Symfony\Component\Process\Process;

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
            $validator->fail('python3 not found', 'Install Python 3 for your system.');

            return; // no need to check anything else
        }

        $this->checkVersion($validator, $version);
        $this->checkPipVersion($validator, $version);
        $this->checkExtensions($validator);
    }

    private function checkVersion(Validator $validator, $version)
    {
        if (version_compare($version, self::PYTHON_MIN_VERSION, '<')) {
            $validator->warn("Python version $version too old.", 'Python version ' . self::PYTHON_MIN_VERSION . ' is the minimum supported version. We recommend you update Python to a supported version.');
        }
    }

    private function checkPipVersion(Validator $validator, $version)
    {
        preg_match('/\(python ([0-9.]+)\)/', `pip3 --version 2>/dev/null`, $matches);
        $pip = $matches[1];
        $python = implode('.', array_slice(explode('.', $version), 0, 2));
        if ($pip && version_compare($python, $pip, '!=')) {
            $validator->fail("python3 ($python) and pip3 ($pip) versions do not match.  This likely will cause dependencies to be installed for the wrong python version.");
        }
    }

    private function checkExtensions(Validator $validator)
    {
        $pythonExtensions = '/scripts/check_requirements.py';
        $process = new Process([Config::get('install_dir') . $pythonExtensions, '-v']);
        $process->run();

        if ($process->getExitCode() !== 0) {
            $user = \config('librenms.user');
            $user_mismatch = function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? null) !== $user : false;

            if ($user_mismatch) {
                $validator->warn("Could not check Python dependencies because this script is not running as $user");
            } else {
                $validator->fail("Python3 module issue found: '" . $process->getOutput() . "'", 'pip3 install -r ' . Config::get('install_dir') . '/requirements.txt');
            }
        }
    }
}
