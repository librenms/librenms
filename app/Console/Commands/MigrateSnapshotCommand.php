<?php
/**
 * MigrateSnapshotCommand.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Symfony\Component\Console\Input\InputOption;

class MigrateSnapshotCommand extends LnmsCommand
{
    protected $developer = true;
    protected $name = 'migrate:snapshot';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.migrate:snapshot.description'));
        $this->addOption('database', null, InputOption::VALUE_REQUIRED);
    }

    public function handle() {
        $database = $this->option('database') ?: \config('database.default');
        $this->dropAllTables($database);
        $this->call('migrate', [
            '--env' => $this->option('env'),
            '--database' => $database,
            '--no-snapshot' => true,
        ]);

        $storageLocation = config('database.snapshot_location');
        $db_config = \config("database.connections.$database");

        // Store a snapshot of the db after migrations run.
        exec("mysqldump -h {$db_config['host']} -u {$db_config['username']} --password={$db_config['password']} {$db_config['database']} > {$storageLocation} 2>/dev/null");
    }

    protected function dropAllTables($database) {
        $this->laravel['db']->connection($database)
            ->getSchemaBuilder()
            ->dropAllTables();
    }
}
