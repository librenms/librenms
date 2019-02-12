<?php
/**
 * ApplyPullRequest.php
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

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ApplyPullRequest extends Command
{
    protected $name = 'test:pull-request';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.test:pull-request.description'));

        $this->addArgument(
            'pull-request',
            InputArgument::REQUIRED,
            __('commands.test:pull-request.arguments.pull-request', ['url' => 'https://github.com/librenms/librenms/pull/'])
        );

        $this->addOption(
            'remove',
            'r',
            InputOption::VALUE_NONE,
            __('commands.test:pull-request.options.remove')
        );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $number = $this->argument('pull-request');
        $remove = $this->option('remove');
        $process = \LibreNMS\Util\Git::applyPullRequest($number, $remove);

        $key = $remove ? 'remove' : 'apply';

        if ($process->getExitCode() == 0) {
            $this->info(__("commands.test:pull-request.success.$key", ['number' => $number]));
        } elseif (str_contains($process->getErrorOutput(), 'error: unrecognized input')) {
            $this->error(__('commands.test:pull-request.download_failed'));
        } else {
            $this->line($process->getErrorOutput());
            $this->error(__("commands.test:pull-request.failed.$key", ['number' => $number]));
        }

        return $process->getExitCode();
    }
}
