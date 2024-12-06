<?php
/**
 * CheckDistributedPollerEnabled.php
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

namespace LibreNMS\Validations\DistributedPoller;

use LibreNMS\Config;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;

class CheckDistributedPollerEnabled implements Validation, ValidationFixer
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (! Config::get('distributed_poller')) {
            return ValidationResult::fail(trans('validation.validations.distributedpoller.CheckDistributedPollerEnabled.not_enabled'))
                ->setFix('lnms config:set distributed_poller true')
                ->setFixer(__CLASS__);
        }

        $db_config = \App\Models\Config::firstWhere('config_name', 'distributed_poller');
        if ($db_config === null || ! $db_config->config_value) {
            return ValidationResult::fail(trans('validation.validations.distributedpoller.CheckDistributedPollerEnabled.not_enabled_globally'))
                ->setFix('lnms config:set distributed_poller true')
                ->setFixer(__CLASS__);
        }

        return ValidationResult::ok(trans('validation.validations.distributedpoller.CheckDistributedPollerEnabled.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    public function fix(): bool
    {
        Config::persist('distributed_poller', true);

        return true;
    }
}
