<?php

/**
 * IcmpPollingMethodDefinition.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Method\Definitions;

use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\IcmpPollingMethod;

/**
 * @implements PollingMethodDefinitionInterface<IcmpPollingMethod>
 */
class IcmpPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'affects_availability' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-exchange';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return IcmpPollingMethod::class;
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): null
    {
        return null;
    }
}
