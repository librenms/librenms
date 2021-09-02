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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use Exception;
use LibreNMS\Exceptions\FileWriteFailedException;
use LibreNMS\Interfaces\InstallerStep;
use LibreNMS\Util\EnvHelper;

class FinalizeController extends InstallationController implements InstallerStep
{
    protected $step = 'finish';

    public function index()
    {
        if (! $this->initInstallStep()) {
            return $this->redirectToIncomplete();
        }

        $env = '';
        $config = '';
        $config_file = base_path('config.php');
        $messages = [];
        $success = false;
        $config_message = file_exists($config_file) ? trans('install.finish.config_exists') : trans('install.finish.config_written');
        $env_message = trans('install.finish.env_written');

        try {
            $this->writeConfigFile();
        } catch (Exception $e) {
            $config = $this->getConfigFileContents();
            $config_message = trans('install.finish.config_not_written');
        }

        try {
            $this->writeEnvFile();
            $success = true;
            session()->flush();
        } catch (Exception $e) {
            $env = $this->getEnvFileContents();
            $messages[] = $e->getMessage();
            $env_message = trans('install.finish.env_not_written');
        }

        return view('install.finish', $this->formatData([
            'success' => $success,
            'env' => $env,
            'config' => $config,
            'messages' => $messages,
            'env_message' => $env_message,
            'config_message' => $config_message,
        ]));
    }

    private function writeEnvFile()
    {
        return EnvHelper::writeEnv(
            $this->envVars(),
            ['INSTALL'],
            base_path('.env')
        );
    }

    private function envVars()
    {
        $this->configureDatabase();
        $connection = config('database.default', $this->connection);
        $port = config("database.connections.$connection.port");

        return [
            'NODE_ID' => uniqid(),
            'DB_HOST' => config("database.connections.$connection.host"),
            'DB_PORT' => $port == 3306 ? null : $port, // don't set default port
            'DB_USERNAME' => config("database.connections.$connection.username"),
            'DB_PASSWORD' => config("database.connections.$connection.password"),
            'DB_DATABASE' => config("database.connections.$connection.database"),
            'DB_SOCKET' => config("database.connections.$connection.unix_socket"),
        ];
    }

    /**
     * @throws \LibreNMS\Exceptions\FileWriteFailedException
     */
    private function writeConfigFile()
    {
        $config_file = base_path('config.php');
        if (file_exists($config_file)) {
            return;
        }

        if (! copy(base_path('config.php.default'), $config_file)) {
            throw new FileWriteFailedException($config_file);
        }
    }

    private function getConfigFileContents()
    {
        return file_get_contents(base_path('config.php.default'));
    }

    private function getEnvFileContents()
    {
        return EnvHelper::setEnv(
            file_get_contents(base_path('.env')),
            $this->envVars(),
            ['INSTALL']
        );
    }

    public function enabled(): bool
    {
        foreach ($this->hydrateControllers() as $step => $controller) {
            /** @var InstallerStep $controller */
            if ($step !== 'finish' && ! $controller->complete()) {
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
