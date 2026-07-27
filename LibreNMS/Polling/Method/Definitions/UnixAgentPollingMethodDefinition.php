<?php

/**
 * UnixAgentPollingMethodDefinition.php
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
use LibreNMS\Polling\Method\UnixAgentPollingMethod;

/**
 * @implements PollingMethodDefinitionInterface<UnixAgentPollingMethod>
 */
class UnixAgentPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [
            'port' => [
                'type' => 'number',
                'default' => 6556,
                'min' => 1,
                'max' => 65535,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'port' => 6556,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-terminal';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return UnixAgentPollingMethod::class;
    }

    /**
     * @inheritDoc
     */
    public function secretClass(): null
    {
        return null;
    }
}
