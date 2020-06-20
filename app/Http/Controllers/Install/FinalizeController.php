<?php
/**
 * FinalizeController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use Exception;
use LibreNMS\Interfaces\InstallerStep;
use LibreNMS\Util\EnvHelper;

class FinalizeController extends InstallationController implements InstallerStep
{
    public function index()
    {
        if (!$this->enabled()) {
            return redirect()->route('install');
        }

        $env = '';
        $config_file = base_path('config.php');
        $config = $this->getConfigFileContents();
        $messages = [];
        $success = true;
        $config_message = file_exists($config_file) ? trans('install.finish.config_exists') : trans('install.finish.config_written');

        try {
            $this->writeConfigFile();
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $config_message = trans('install.finish.config_not_written');
            $success = true;
        }

        // write env last only if everything else succeeded
        if ($success) {
            try {
                $env = $this->writeEnvFile();
            } catch (Exception $e) {
                $messages[] = $e->getMessage();
                $success = false;
            }
        }

        if ($success) {
            session()->flush();
        }

        return view('install.finish', $this->formatData([
            'env' => $env,
            'config' => $config,
            'messages' => $messages,
            'success' => $success,
            'config_message' => $config_message,
        ]));
    }

    private function writeEnvFile()
    {
        $this->configureDatabase();
        $connection = config('database.default', $this->connection);
        return EnvHelper::writeEnv([
            'NODE_ID' => uniqid(),
            'DB_HOST' => config("database.connections.$connection.host"),
            'DB_PORT' => config("database.connections.$connection.port"),
            'DB_USERNAME' => config("database.connections.$connection.username"),
            'DB_PASSWORD' => config("database.connections.$connection.password"),
            'DB_DATABASE' => config("database.connections.$connection.database"),
            'DB_SOCKET' => config("database.connections.$connection.unix_socket"),
        ], ['INSTALL'], base_path('.env'));
    }

    private function writeConfigFile()
    {
        $config_file = base_path('config.php');
        if (file_exists($config_file)) {
            return;
        }

        if (!copy(base_path('config.php.default'), $config_file)) {
            throw new Exception("We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)");
        }
    }

    private function getConfigFileContents()
    {
        return file_get_contents(base_path('config.php.default'));
    }

    public function enabled(): bool
    {
        foreach ($this->steps as $step => $controller) {
            if ($step !== 'finish' && !session("install.$step")) {
                return false;
            }
        }

        return true;
    }

    public function complete(): bool
    {
        return false;
    }

    public function icon(): string
    {
        return 'fa-check';
    }
}
