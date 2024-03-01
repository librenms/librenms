<?php
/**
 * Version.php
 *
 * Get version info about LibreNMS and various components/dependencies
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use Symfony\Component\Process\Process;

class Version
{
    /** @var string Update this on release */
    public const VERSION = '24.2.0';

    /** @var Git convenience instance */
    public $git;

    public function __construct()
    {
        $this->git = Git::make();
    }

    public static function get(): Version
    {
        return new static;
    }

    public function release(): string
    {
        return Config::get('update_channel') == 'master' ? 'master' : self::VERSION;
    }

    public function date(string $format = 'c'): string
    {
        return date($format, $this->git->commitDate() ?: filemtime(__FILE__));  // approximate date for non-git installs
    }

    public function name(): string
    {
        return $this->git->tag() ?: self::VERSION;
    }

    public function databaseServer(): string
    {
        if (! Eloquent::isConnected()) {
            return 'Not Connected';
        }

        switch (Eloquent::getDriver()) {
            case 'mysql':
                $ret = Arr::first(DB::selectOne('select version()'));

                return (str_contains($ret, 'MariaDB') ? 'MariaDB ' : 'MySQL ') . $ret;
            case 'sqlite':
                return 'SQLite ' . Arr::first(DB::selectOne('select sqlite_version()'));
            default:
                return 'Unsupported: ' . Eloquent::getDriver();
        }
    }

    /**
     * Get the database last migration and count as a string
     */
    public function database(): string
    {
        return sprintf('%s (%s)', $this->lastDatabaseMigration(), $this->databaseMigrationCount());
    }

    /**
     * Get the total number of migrations applied to the database
     */
    public function databaseMigrationCount(): int
    {
        try {
            if (Eloquent::isConnected()) {
                return Eloquent::DB()->table('migrations')->count();
            }
        } catch (\Exception $e) {
        }

        return 0;
    }

    /**
     * Get the name of the last migration that was applied to the database
     */
    public function lastDatabaseMigration(): string
    {
        if (! Eloquent::isConnected()) {
            return 'Not Connected';
        }

        try {
            return Eloquent::DB()->table('migrations')->orderBy('id', 'desc')->value('migration');
        } catch (\Exception $e) {
            return 'No Schema';
        }
    }

    public function python(): string
    {
        $proc = new Process(['python3', '--version']);
        $proc->run();

        if ($proc->getExitCode() !== 0) {
            return '';
        }

        return explode(' ', rtrim($proc->getOutput()), 2)[1] ?? '';
    }

    public function rrdtool(): string
    {
        $process = new Process([Config::get('rrdtool', 'rrdtool'), '--version']);
        $process->run();
        preg_match('/^RRDtool ([\w.]+) /', $process->getOutput(), $matches);

        return str_replace('1.7.01.7.0', '1.7.0', $matches[1] ?? '');
    }

    public function netSnmp(): string
    {
        $process = new Process([Config::get('snmpget', 'snmpget'), '-V']);

        $process->run();
        preg_match('/[\w.]+$/', $process->getErrorOutput(), $matches);

        return $matches[0] ?? '';
    }

    /**
     * The OS/distribution and version
     */
    public function os(): string
    {
        $info = [];

        // find release file
        if (file_exists('/etc/os-release')) {
            $info = @parse_ini_file('/etc/os-release');
        } else {
            foreach (glob('/etc/*-release') as $file) {
                $content = file_get_contents($file);
                // normal os release style
                $info = @parse_ini_string($content);
                if (! empty($info)) {
                    break;
                }

                // just a string of text
                if (substr_count($content, PHP_EOL) <= 1) {
                    $info = ['NAME' => trim(str_replace('release ', '', $content))];
                    break;
                }
            }
        }

        $only = array_intersect_key($info, ['NAME' => true, 'VERSION_ID' => true]);

        return implode(' ', $only);
    }

    /**
     * Get a formatted header to print out to the user.
     */
    public function header(): string
    {
        return sprintf(<<<'EOH'
===========================================
Component | Version
--------- | -------
LibreNMS  | %s (%s)
DB Schema | %s (%s)
PHP       | %s
Python    | %s
Database  | %s
RRDTool   | %s
SNMP      | %s
===========================================

EOH,
            $this->name(),
            $this->date(),
            $this->lastDatabaseMigration(),
            $this->databaseMigrationCount(),
            phpversion(),
            $this->python(),
            $this->databaseServer(),
            $this->rrdtool(),
            $this->netSnmp()
        );
    }
}
