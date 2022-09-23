<?php
/**
 * CheckPythonWrapper.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Poller;

use App\Models\Poller;
use App\Models\PollerCluster;
use LibreNMS\Config;
use LibreNMS\ValidationResult;

class CheckPythonWrapper implements \LibreNMS\Interfaces\Validation
{
    /** @var bool */
    private $could_not_check_cron = false;

    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (Poller::exists()) {
            return $this->checkPythonWrapper();
        }

        // check if cron is installed, then try to check if the cron entries are enabled.
        $cron = Config::locateBinary('cron');
        if ($cron !== 'cron') { // cron is installed
            if ($this->wrapperCronEnabled()) {
                return $this->checkPythonWrapper();
            }

            if ($this->could_not_check_cron) {
                return ValidationResult::info(trans('validation.validations.poller.CheckPythonWrapper.cron_unread'));
            }

            $status = PollerCluster::isActive()->exists() ? ValidationResult::SUCCESS : ValidationResult::FAILURE;

            return new ValidationResult(trans('validation.validations.poller.CheckPythonWrapper.not_detected'), $status);
        }

        $status = PollerCluster::isActive()->exists() ? ValidationResult::SUCCESS : ValidationResult::FAILURE;

        return new ValidationResult(trans('validation.validations.poller.CheckPythonWrapper.no_pollers'), $status);
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    private function checkPythonWrapper(): ValidationResult
    {
        if (! Poller::isActive()->exists()) {
            $status = PollerCluster::isActive()->exists() ? ValidationResult::SUCCESS : ValidationResult::FAILURE;

            return new ValidationResult(trans('validation.validations.poller.CheckPythonWrapper.fail'), $status);
        }

        $inactive_pollers = Poller::isInactive()->get();
        if ($inactive_pollers->isNotEmpty()) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckPythonWrapper.nodes_down'))
                ->setList('Inactive Nodes', $inactive_pollers->pluck('poller_name')->toArray());
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckPythonWrapper.ok'));
    }

    private function wrapperCronEnabled(): bool
    {
        $files = glob('/etc/cron.d/*');
        $files[] = '/etc/crontab';
        $files[] = '/var/spool/cron/crontabs/librenms';
        $this->could_not_check_cron = true; // set this in case we can't read any cron files

        $cron_entries = array_reduce($files, function ($entries, $file) {
            if (is_readable($file)) {
                $entries .= file_get_contents($file) . PHP_EOL;
                $this->could_not_check_cron = false;
            }

            return $entries;
        }, '');

        return (bool) preg_match('/^\s*[^#].*poller-wrapper\.py/', $cron_entries);
    }
}
