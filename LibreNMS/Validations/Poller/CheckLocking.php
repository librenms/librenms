<?php
/**
 * CheckDistributedLocking.php
 *
 * Check that distributed locking is set correctly and enabled.
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

use LibreNMS\ValidationResult;

class CheckLocking implements \LibreNMS\Interfaces\Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        try {
            $lock = \Cache::lock('dist_test_validation', 5);
            $lock->get();
            $lock->release();

            return ValidationResult::ok(trans('validation.validations.poller.CheckLocking.ok'));
        } catch (\Exception $e) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckLocking.fail', ['message' => $e->getMessage()]));
        }
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }
}
