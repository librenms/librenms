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
use LibreNMS\Util\EnvHelper;

class FinalizeController extends \App\Http\Controllers\Controller
{
    public function __invoke()
    {
        $env = '';
        $config_file = base_path('config.php');
        $config = $this->getConfigFileContents();
        $messages = [];
        $success = true;
        $config_message = file_exists($config_file) ? trans('install.finish.config_exists') : trans('install.finish.config_written');

        try {
            $this->writeConfigFile($config,  $config_file);
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
            // TODO clear session
//            session()->forget('install');
//            session()->forget('db');
        }

        return view('install.finish', [
            'env' => $env,
            'config' => $config,
            'messages' => $messages,
            'success' => $success,
            'config_message' => $config_message,
        ]);
    }

    private function writeEnvFile()
    {
        return EnvHelper::setEnv([
            'NODE_ID' => uniqid(),
            'DB_HOST' => session('db.host'),
            'DB_PORT' => session('db.port'),
            'DB_USERNAME' => session('db.username'),
            'DB_PASSWORD' => session('db.password'),
            'DB_DATABASE' => session('db.database'),
            'DB_SOCKET' => session('db.socket'),
        ], [], base_path('.env')); // TODO unset INSTALL
    }

    private function writeConfigFile($config_contents, $config_file)
    {
        if (!file_exists($config_file)) {
            $conf = fopen($config_file, 'w');
            if ($conf !== false) {
                if (fwrite($conf, "<?php\n") === false) {
                    throw new Exception("We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)");
                }

                $config_contents = stripslashes($config_contents);
                fwrite($conf, $config_contents);
                fclose($conf);
                return;
            }

            throw new Exception("We couldn't create the config.php file, please create this manually before continuing by copying the below into a config.php in the root directory of your install (typically /opt/librenms/)");
        }
    }

    public static function enabled($steps): bool
    {
        foreach ($steps as $step => $controller) {
            if ($step !== 'finish' && !session("install.$step")) {
                return false;
            }
        }

        return true;
    }

    public static function icon(): string
    {
        return 'fa-check';
    }

    private function getConfigFileContents()
    {
        $db = session('db');
        $install_dir = base_path();

        return <<<"EOD"
## Have a look in defaults.inc.php for examples of settings you can set here. DO NOT EDIT defaults.inc.php!

### Database config
\$config['db_host'] = '{$db['host']}';
\$config['db_port'] = '{$db['port']}';
\$config['db_user'] = '{$db['username']}';
\$config['db_pass'] = '{$db['password']}';
\$config['db_name'] = '{$db['database']}';
\$config['db_socket'] = '{$db['unix_socket']}';

// This is the user LibreNMS will run as
//Please ensure this user is created and has the correct permissions to your install
\$config['user'] = 'librenms';

### Locations - it is recommended to keep the default
#\$config['install_dir']  = "$install_dir";

### This should *only* be set if you want to *force* a particular hostname/port
### It will prevent the web interface being usable form any other hostname
#\$config['base_url']        = "http://librenms.company.com";

### Enable this to use rrdcached. Be sure rrd_dir is within the rrdcached dir
### and that your web server has permission to talk to rrdcached.
#\$config['rrdcached']    = "unix:/var/run/rrdcached.sock";

### Default community
\$config['snmp']['community'] = ['public'];

### Authentication Model
\$config['auth_mechanism'] = "mysql"; # default, other options: ldap, http-auth
#\$config['http_auth_guest'] = "guest"; # remember to configure this user if you use http-auth

### List of RFC1918 networks to allow scanning-based discovery
#\$config['nets'][] = "10.0.0.0/8";
#\$config['nets'][] = "172.16.0.0/12";
#\$config['nets'][] = "192.168.0.0/16";

# Update configuration
#\$config['update_channel'] = 'release';  # uncomment to follow the monthly release channel
#\$config['update'] = 0;  # uncomment to completely disable updates
EOD;

    }
}
