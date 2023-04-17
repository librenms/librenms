<?php
/**
 * Updates.php
 *
 * Checks the status of git and updates.
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use DateTime;
use DateTimeZone;
use Exception;
use LibreNMS\ComposerHelper;
use LibreNMS\Config;
use LibreNMS\Util\EnvHelper;
use LibreNMS\Util\Git;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;
use Symfony\Component\Process\Process;

class Updates extends BaseValidation
{
    public function validate(Validator $validator): void
    {
        if (EnvHelper::librenmsDocker()) {
            $validator->warn('Updates are managed through the official Docker image');

            return;
        }

        if (! Git::make()->repoPresent()) {
            $validator->warn('Non-git install, updates are manual or from package');

            return;
        }

        // if git is not available, we cannot do the other tests
        if (! Git::make()->binaryExists()) {
            $validator->warn('Unable to locate git. This should probably be installed.');

            return;
        }

        // check if users on master update channel are up to date
        if (Config::get('update_channel') == 'master') {
            $git = Git::make();
            if ($git->commitHash() != $git->remoteHash()) {
                if (! $git->commitDate()) {
                    $process = new Process(['git', 'show', '--quiet', '--pretty=%H|%ct'], base_path());
                    $process->run();
                    $error = rtrim($process->getErrorOutput());

                    $validator->fail('Failed to fetch version from local git: ' . $error);
                } else {
                    try {
                        $commit_date = new DateTime('@' . $git->commitDate(), new DateTimeZone(date_default_timezone_get()));
                        if ($commit_date->diff(new DateTime())->days > 0) {
                            $validator->warn(
                                'Your install is over 24 hours out of date, last update: ' . $commit_date->format('r'),
                                'Make sure your daily.sh cron is running and run ./daily.sh by hand to see if there are any errors.'
                            );
                        }
                    } catch (Exception $e) {
                        $validator->fail($e->getMessage());
                    }
                }
            }

            $branch = $git->branch();
            if ($branch != 'master') {
                if ($branch == 'php53') {
                    $validator->warn(
                        'You are on the PHP 5.3 support branch, this will prevent automatic updates.',
                        'Update to PHP 5.6.4 or newer (PHP ' . Php::PHP_RECOMMENDED_VERSION . ' recommended) to continue to receive updates.'
                    );
                } elseif ($branch == 'php56') {
                    $validator->warn(
                        'You are on the PHP 5.6/7.0 support branch, this will prevent automatic updates.',
                        'Update to PHP ' . Php::PHP_MIN_VERSION . ' or newer (PHP ' . Php::PHP_RECOMMENDED_VERSION . ' recommended) to continue to receive updates.'
                    );
                } else {
                    $validator->warn(
                        'Your local git branch is not master, this will prevent automatic updates.',
                        'You can switch back to master with git checkout master'
                    );
                }
            }
        }

        // TODO check update channel stable version

        // check for modified files
        $modifiedcmd = 'git diff --name-only --exit-code';
        $validator->execAsUser($modifiedcmd, $cmdoutput, $code);
        if ($code !== 0 && ! empty($cmdoutput)) {
            // Check so it's not only plugins that "pests" the diff
            if (! ($cmdoutput === ['composer.json', 'composer.lock'] && ComposerHelper::getPlugins())) {
                $result = ValidationResult::warn(
                    'Your local git contains modified files, this could prevent automatic updates.',
                    'You can fix this with ./scripts/github-remove'
                );

                $result->setList('Modified Files', $cmdoutput);
                $validator->result($result);
            }
        }
    }
}
