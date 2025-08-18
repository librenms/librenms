<?php

/**
 * ReleaseTag.php
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
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use LibreNMS\Util\GitHub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class ReleaseTag extends LnmsCommand
{
    protected $name = 'release:tag';
    protected $developer = true;

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('tag', InputArgument::REQUIRED, 'The new tag / version');
        $this->addArgument('from', InputArgument::REQUIRED, 'The previous tag / version');

        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'The filename to update');
        $this->addOption('pr', null, InputOption::VALUE_REQUIRED, 'The last PR to include in this release if not master branch');
    }

    public function handle(): int
    {
        $tag = (string) $this->argument('tag');
        $this->info("Creating release $tag.....");

        $file = (string) ($this->option('file') ?: 'doc/General/Changelog.md');

        $gh = new GitHub(
            $tag,
            (string) $this->argument('from'),
            $file,
            getenv('GH_TOKEN') ?: (string) $this->secret('Enter a GitHub Token?'),
            $this->option('pr')
        );

        try {
            $gh->createChangelog();
            $this->info("Changelog generated for $tag");

            if ($this->confirm('Do you want to view the generated Changelog?')) {
                $this->output->writeln($gh->getMarkdown());
            }

            if ($this->confirm("Do you want to create the release $tag on GitHub?")) {
                if ($gh->createRelease()) {
                    $this->info('Release created.');
                } else {
                    $this->error('Failed to create release, check github to see what was completed.');
                }
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }

        // remove changelog modifications
        (new Process(['git', 'checkout', base_path($file)]))->run();

        return 0;
    }
}
