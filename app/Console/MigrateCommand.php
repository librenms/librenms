<?php
/**
 * MigrateCommand.php
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

namespace App\Console;

use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;

class MigrateCommand extends BaseMigrateCommand
{
    protected $signature = 'migrate {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--no-snapshot : Skip snapshot and run full migration}
                {--path=* : The path(s) to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--step : Force the migrations to be run so they can be rolled back individually}';

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        $database = $this->option('database');
        $this->migrator->setConnection($database);

        // import snapshot if this is a new DB and it is available
        if (!$this->option('no-snapshot')
            && !$this->migrator->repositoryExists()
            && is_file(\config('snipe.snapshot-location'))
        ) {
            $this->output->write('Importing DB snapshot... ');
            try {
                $res = $this->laravel['db']->connection($database)
                    ->getPdo()->exec(file_get_contents(\config('snipe.snapshot-location')));

                if ($res !== 0) { // is 0 the right value?
                    $this->output->write('failed');
                }
            } catch (\PDOException $e) {
                $this->output->write('failed ' . $e->getMessage());
            }

            $this->laravel['db']->reconnect($database); // reconnect to clear any settings set in the .sql file
            $this->output->writeln('');
        }

        parent::prepareDatabase();
    }
}
