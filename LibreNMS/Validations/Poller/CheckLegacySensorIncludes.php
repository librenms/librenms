<?php

/**
 * CheckLegacySensorIncludes.php
 *
 * Find sensor polling includes, support for them will be removed
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Poller;

use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

/**
 * LibreNMS no longer ships these includes, any that exist were added locally (see LegacySensorPolling)
 */
class CheckLegacySensorIncludes implements Validation
{
    private readonly string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? base_path('includes/polling/sensors');
    }

    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        return ValidationResult::warn(trans('validation.validations.poller.CheckLegacySensorIncludes.warn'), trans('validation.validations.poller.CheckLegacySensorIncludes.fix'))
            ->setList('Files', $this->files());
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return ! empty($this->files());
    }

    /**
     * @return string[]
     */
    private function files(): array
    {
        return glob("$this->directory/*/*.inc.php") ?: [];
    }
}
